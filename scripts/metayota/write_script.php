<?php
namespace Metayota;
include_once('../metayota/library.php');

checkApiKey();

if (getConfig('write_script_disabled', false)) {
    die('The "Write Script"-Call has been disabled for security reasons!');
}

$inputData = $data;
$resourcename = $inputData['resourcename'] ?? $_GET['resourcename'] ?? null;
$specificFile = $inputData['file'] ?? null;
$fileContent = $inputData['content'] ?? null;
$resourceType = $inputData['resource_type'] ?? null;
$dependenciesJson = $inputData['dependencies'] ?? null;

// If specific file is provided, write it directly without database lookup
if ($specificFile !== null && $fileContent !== null && $resourcename !== null) {
    if (getConfig('wordpress')) {
        $folder = '../../scripts/'.$resourcename.'/';
    } else {
        $folder = '../'.$resourcename.'/';
    }
    
    if (!is_dir($folder)) {
        mkdir($folder);
    }
    
    // Determine file name using same logic as before
    if (str_ends_with($specificFile,'php') && $specificFile != 'php' && $specificFile != 'html.php') {
        $fileName = $folder.$specificFile;
    } else {
        if ($specificFile == 'html.php' || $specificFile == 'html' || $specificFile == 'css' || $specificFile == 'javascript' || $specificFile == 'php') {
            $fileName = $folder.$resourcename.'.'.$specificFile;
            unlink($folder.$specificFile);
        } else {
            $fileName = $folder.$specificFile;
        }
    }
    
    $implementation = $fileContent;
    
    
    $implementationCleaned = str_replace(["\r\n", "\r"], "\n", $implementation);
    if (file_put_contents($fileName, $implementationCleaned) === false) {
        echo json_encode(['success'=>false, 'error'=>'File could not be written: '.$fileName]);
        exit;
    }
    
    echo json_encode(['success'=>true]);
    exit;
}

// Original logic for full resource write (fallback)
$resource = $db->getByName('resource',$resourcename);

if (empty($resource)) {
    $parentFolder = realpath('..');
    $resourceFolder = realpath($parentFolder . '/' . $resourcename);
    
    if ($resourceFolder && dirname($resourceFolder) === $parentFolder) {
        deleteDirectory($resourceFolder);
    }
}

if (isset($resource['implementation']) && $resource['implementation'] != '{}') {
    $implementations = json_decode($resource['implementation'], true);
} else {
    $implementations = null;
}

if (!empty($implementations)) {
    if (getConfig('wordpress')) {
        $folder = '../../scripts/'.$resource['name'].'/';
    } else {
        $folder = '../'.$resource['name'].'/';
    }
    if (!is_dir($folder)) {
        mkdir($folder);
    }
    if (!empty($resource['dependencies'])) {
        $dependencies = json_decode($resource['dependencies']);
    }
    
    $includeStr = "namespace Metayota; include_once('../metayota/library.php'); ";
    if (!empty($dependencies) && $resource['name'] != 'metayota') {
        foreach($dependencies as $dependency) {
            if ($dependency->type == 'php-library') {
                $dependencyFile = '../'.$dependency->name.'/'.$dependency->name.'.php';
                $includeStr.= "  include_once('$dependencyFile'); ";
            }
        }
    }
    
    foreach($implementations as $technology => $implementation) {
        if (str_ends_with($technology,'php') && $technology != 'php' && $technology != 'html.php') {
            $fileName = $folder.$technology;
        } else {
            if ($technology == 'html.php' || $technology == 'html' || $technology == 'css' || $technology == 'javascript' || $technology == 'php') {
                $fileName = $folder.$resource['name'].'.'.$technology;
            } else {
                $fileName = $folder.$technology;
            }
        }
        
        if ($resource['type'] == 'php-library' && $resource['name'] != 'metayota') {
            if (str_ends_with($technology,'php')) {
                if (!str_starts_with($implementation, '<?php')) {
                    $implementation = "<?php\n".$implementation."\n?>";
                }
            }
        }
        
        if (!empty($includeStr)  && $resource['name'] != 'metayota') {
            if (str_ends_with($technology,'php')) {
                if (str_starts_with($implementation,'<?php')) {
                    $implementation = substr($implementation, 5);
                    $implementation = "<?php ".$includeStr." ".$implementation;
                }  else {
                    $implementation = "<?php ".$includeStr." ?>".$implementation;
                }
            }
        }
        
        $implementationCleaned = str_replace(["\r\n", "\r"], "\n", $implementation);
        if (file_put_contents($fileName, $implementationCleaned) === false) {
            echo json_encode(['success'=>false, 'error'=>'File could not be written: '.$fileName]);
            exit;
        }
    }
}

function deleteDirectory($dirPath) {
    if (!is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = scandir($dirPath);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        $filePath = $dirPath . $file;
        if (is_dir($filePath)) {
            deleteDirectory($filePath);
        } else {
            unlink($filePath);
        }
    }
    rmdir($dirPath);
}

echo json_encode(['success'=>true]);
?>
