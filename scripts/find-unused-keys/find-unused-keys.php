<?php namespace Metayota; include_once('../metayota/library.php');  

// Even more optimized version with better regex patterns
function findUnusedTranslationKeysAdvancedRegex() {
    global $db;
    
    // Default excluded prefixes if none provided

    $excludePrefixes = ['sales_no_', 'salutation_', 'event_status_', 'create_row_', 'event_time_until_'];

    echo "Step 1: Getting all translation keys...\n";
    echo "Excluding prefixes: " . implode(', ', $excludePrefixes) . "\n\n";
    
    $translationKeys = $db->getAllByQuery("SELECT translation_key FROM translation WHERE translation_key NOT LIKE 'resource_archive%'", []);
    
    // Create hash set of translation keys with prefix filtering
    $validTranslationKeys = [];
    $excludedCount = 0;
    
    foreach ($translationKeys as $keyRow) {
        $key = trim($keyRow['translation_key']);
        
        // Check if key starts with any excluded prefix
        $shouldExclude = false;
        foreach ($excludePrefixes as $prefix) {
            if (strpos($key, $prefix) === 0) {
                $shouldExclude = true;
                $excludedCount++;
                break;
            }
        }
        
        if (!$shouldExclude && preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $key) && strlen($key) >= 3) {
            $validTranslationKeys[$key] = true;
        }
    }
    
    echo "Found " . count($validTranslationKeys) . " valid translation keys\n";
    echo "Excluded {$excludedCount} keys based on prefixes\n\n";
    
    echo "Step 2: Extracting potential keys with advanced regex...\n";
    $textColumns = $db->getAllByQuery("
        SELECT TABLE_NAME, COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
          AND DATA_TYPE IN ('varchar', 'text', 'longtext', 'mediumtext', 'tinytext', 'char')
          AND NOT (TABLE_NAME = 'translation' AND COLUMN_NAME = 'translation_key')
          AND TABLE_NAME != 'resource_archive'
        ORDER BY TABLE_NAME, COLUMN_NAME
    ", []);
    
    $foundKeys = [];
    $processedRows = 0;
    
    foreach ($textColumns as $column) {
        $tableName = $column['TABLE_NAME'];
        $columnName = $column['COLUMN_NAME'];
        
        echo "Processing {$tableName}.{$columnName}...\n";
        
        $offset = 0;
        $limit = 500;
        
        do {
            $rows = $db->getAllByQuery("
                SELECT `{$columnName}` 
                FROM `{$tableName}` 
                WHERE `{$columnName}` IS NOT NULL 
                  AND `{$columnName}` != ''
                  AND LENGTH(`{$columnName}`) > 0
                LIMIT {$limit} OFFSET {$offset}
            ", []);
            
            foreach ($rows as $row) {
                $text = $row[$columnName];
                if (!empty($text)) {
                    // Multiple regex patterns to catch different formats:
                    
                    // 1. Standard translation key format: word_word_word
                    preg_match_all('/\b[a-zA-Z][a-zA-Z0-9_]{2,}\b/', $text, $matches1);
                    
                    // 2. Keys that might be in configuration format: key=value
                    preg_match_all('/([a-zA-Z][a-zA-Z0-9_]{2,})\s*=/', $text, $matches2);
                    
                    // 3. Keys that might be in template/code format: {{key}} or ${key}
                    preg_match_all('/[\{\$\{]\s*([a-zA-Z][a-zA-Z0-9_]{2,})\s*[\}\}]/', $text, $matches3);
                    
                    // Combine all matches
                    $allMatches = array_merge(
                        $matches1[0] ?? [],
                        $matches2[1] ?? [],
                        $matches3[1] ?? []
                    );
                    
                    foreach ($allMatches as $match) {
                        $cleanMatch = trim($match);
                        if (isset($validTranslationKeys[$cleanMatch])) {
                            $foundKeys[$cleanMatch] = true;
                        }
                    }
                }
                $processedRows++;
            }
            
            // Show progress every 10000 rows
            if ($processedRows % 10000 == 0 && $processedRows > 0) {
                echo "  Processed {$processedRows} rows, found " . count($foundKeys) . " keys so far\n";
            }
            
            $offset += $limit;
        } while (count($rows) === $limit);
    }
    
    echo "Total processed rows: {$processedRows}\n";
    echo "Found " . count($foundKeys) . " translation keys in use\n\n";
    
    // Calculate results
    $usedKeys = array_keys($foundKeys);
    $allKeys = array_keys($validTranslationKeys);
    $unusedKeys = array_diff($allKeys, $usedKeys);
    
    // Sort for better output
    sort($usedKeys);
    sort($unusedKeys);
    
    echo "=== RESULTS ===\n";
    echo "Total valid translation keys: " . count($allKeys) . "\n";
    echo "Used translation keys: " . count($usedKeys) . "\n";
    echo "Unused translation keys: " . count($unusedKeys) . "\n";
    echo "Usage rate: " . round((count($usedKeys) / count($allKeys)) * 100, 2) . "%\n\n";
    
    if (!empty($unusedKeys)) {
        echo "Unused translation keys:\n";
        foreach ($unusedKeys as $key) {
            echo "- {$key}\n";
        }
        echo "\n";
    }
    
    // Show some used keys as verification
    if (!empty($usedKeys)) {
        echo "Sample of used keys (first 10):\n";
        foreach (array_slice($usedKeys, 0, 10) as $key) {
            echo "✓ {$key}\n";
        }
    }
    
    return [
        'used' => $usedKeys,
        'unused' => $unusedKeys,
        'total_translation_keys' => count($allKeys),
        'usage_rate' => (count($usedKeys) / count($allKeys)) * 100,
        'excluded_count' => $excludedCount
    ];
}

// Usage
echo "=== Advanced Translation Key Analysis ===\n\n";
$result = findUnusedTranslationKeysAdvancedRegex();
foreach ($result['unused'] as $key) $db->delete('translation', ['translation_key' => $key]);

// Optional: Save results to files
if (!empty($result['unused'])) {
    file_put_contents('unused_translation_keys.txt', implode("\n", $result['unused']));
    echo "Unused keys saved to: unused_translation_keys.txt\n";
}

if (!empty($result['used'])) {
    file_put_contents('used_translation_keys.txt', implode("\n", $result['used']));
    echo "Used keys saved to: used_translation_keys.txt\n";
}

?>