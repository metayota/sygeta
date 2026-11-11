<?php
namespace Metayota;
include_once('../metayota/library.php');

$inputData = json_decode(file_get_contents('php://input'), true);
$searchStr = $inputData['search'] ?? $_POST['search'] ?? null;
$searchStr = trim($searchStr);

if (empty($searchStr)) {
    echo json_encode(['success' => true, 'results' => []]);
    exit;
}

$searchLower = mb_strtolower($searchStr);

// Durchsuche alle Ordner im scripts/ Verzeichnis
if (getConfig('wordpress')) {
    $baseFolder = '../../scripts/';
} else {
    $baseFolder = '../';
}

$results = array();

if (is_dir($baseFolder)) {
    $folders = scandir($baseFolder);
    
    foreach ($folders as $folder) {
        if ($folder == '.' || $folder == '..' || !is_dir($baseFolder . $folder)) {
            continue;
        }
        
        // $folder ist der Resource-Name
        $resourceName = $folder;
        $resourcePath = $baseFolder . $folder . '/';
        
        // Durchsuche alle Dateien in diesem Resource-Ordner
        $files = scandir($resourcePath);
        
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            
            $filePath = $resourcePath . $file;
            
            if (is_file($filePath)) {
                $content = file_get_contents($filePath);
                $lines = explode("\n", $content);
                $matchingLines = array();
                
                foreach ($lines as $lineNum => $line) {
                    $lineLower = mb_strtolower($line);
                    
                    if (mb_strpos($lineLower, $searchLower) !== false) {
                        $matchingLines[] = array(
                            'code' => $line, 
                            'line' => $lineNum + 1
                        );
                    }
                }
                
                if (!empty($matchingLines)) {
                    $results[] = array(
                        'name' => $resourceName, 
                        'type' => 'Code', 
                        'language' => $file,
                        'lines' => $matchingLines
                    );
                }
            }
        }
    }
}

echo json_encode(['success' => true, 'results' => $results], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>