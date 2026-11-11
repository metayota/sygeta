<?php namespace Metayota; include_once('../metayota/library.php');  

echo "=== Searching for 'rental_agreement' ===\n\n";

// Get all text columns (excluding resource_archive)
$textColumns = $db->getAllByQuery("
    SELECT TABLE_NAME, COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
      AND DATA_TYPE IN ('varchar', 'text', 'longtext', 'mediumtext', 'tinytext', 'char')
      AND TABLE_NAME != 'resource_archive'
    ORDER BY TABLE_NAME, COLUMN_NAME
", []);

$foundCount = 0;

foreach ($textColumns as $column) {
    $tableName = $column['TABLE_NAME'];
    $columnName = $column['COLUMN_NAME'];
    
    // Search for rental_agreement in this column
    $rows = $db->getAllByQuery("
        SELECT * 
        FROM `{$tableName}` 
        WHERE `{$columnName}` LIKE '%rental_agreement%'
    ", []);
    
    if (!empty($rows)) {
        $foundCount += count($rows);
        echo "FOUND in {$tableName}.{$columnName} ({count($rows)} matches):\n";
        
        foreach ($rows as $row) {
            echo "  - Content: " . $row[$columnName] . "\n";
            if (isset($row['id'])) {
                echo "    Row ID: " . $row['id'] . "\n";
            }
            echo "\n";
        }
    }
}

if ($foundCount == 0) {
    echo "rental_agreement NOT FOUND in any table!\n";
    echo "This translation key appears to be unused.\n";
} else {
    echo "Total matches found: {$foundCount}\n";
}

?>