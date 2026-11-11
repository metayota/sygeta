<?php

namespace Metayota;
function getConfig($name,$default=NULL) {
    $config = $GLOBALS['metayota_config'];
    if (isset($config[$name])) {
        return $config[$name];
    } else {
        return $default;
    }
}


function jsonResponse($data, int $status = 200) {
    // Set HTTP status code
    http_response_code($status);

    // Always JSON header
    header('Content-Type: application/json; charset=utf-8');

    // If it's a string, output raw
    if (is_string($data)) {
        echo $data;
    } else {
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}


function getResourceFiles($resourcename) {
    if (empty($resourcename)) {
        return [];
    }
    
    if (getConfig('wordpress')) {
        $folder = '../../scripts/' . $resourcename . '/';
    } else {
        $folder = '../' . $resourcename . '/';
    }
    
    if (!is_dir($folder)) {
        return [];
    }
    
    $files = [];
    $resourceFiles = scandir($folder);
    
    foreach ($resourceFiles as $file) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        
        $filePath = $folder . $file;
        
        if (is_file($filePath)) {
            $content = file_get_contents($filePath);
            
            // Ensure UTF-8 encoding
            if (!mb_check_encoding($content, 'UTF-8')) {
                $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
            }
            
            $files[$file] = $content;
        }
    }
    
    return $files;
}

function sendCacheHeaders($seconds = NULL)
{
    $GLOBALS['cacheEnabled'];
    if (!$GLOBALS['cacheEnabled'] || $seconds === 0) {
        return;
    }
    if ($seconds === NULL) {
        $seconds = getConfig('default_cache_time', 3600*12);
    }
    $seconds_to_cache = $seconds;
    $ts = date("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
    header("Expires: $ts");
    header("Pragma: cache");
    header("Cache-Control: public, max-age=$seconds_to_cache");
    $now = date("D, d M Y H:i:s", time()) . " GMT";
    header("Last-Modified: " . $now);
}

$GLOBALS['executed_dependencies'] = [];
function getResourceByName($resourceName, $db) {
    $resource = $db->getByName('resource', $resourceName);
    return $resource;
}

function getResource($resourceName, $db) {
    $result = getJSON($resourceName, $db);
    if (!empty($result['extends_resource']) && $result['extends_resource'] != null) {
        $result = mergeJSON($result['extends_resource'], $result, $db);
    }
    return $result;
}

function getJSON($resourceName, $db) {
    $resource = getResourceByName($resourceName, $db);
    
    if ($resource === false) {
        return false;
    }
    
    $result = array();
    
    $result['id'] = $resource['id'];
    if (empty($resource['parameters'])) {
        $result['parameters'] = array();
    } else {
        $result['parameters'] = json_decode($resource['parameters']);
    }
    
    //$result['implementation'] = (array) json_decode($resource['implementation']);
    $result['name'] = $resource['name'];
    $result['title'] = $resource['title'];
    if (!empty($resource['configuration'])) {
        $result['configuration'] = json_decode($resource['configuration']);
    }
    if (!empty($resource['data'])) {
        $result['data'] = json_decode($resource['data']);
    }
    if (!empty($resource['allowed_subelements'])) {
        $result['allowed_subelements'] = json_decode($resource['allowed_subelements']);
    }
    $result['type'] = $resource['type'];
    if (!empty($resource['dependencies'])) {
        $result['dependencies'] = json_decode($resource['dependencies']);
    }
    $result['extends_resource'] = $resource['extends_resource'];
    return $result;
}

function mergeJSONArray($a,$b,$parameter) {
    if (empty($a[$parameter])) {
        return isset($b[$parameter]) ? $b[$parameter] : null;
    }
    if (empty($b[$parameter])) {
        return $a[$parameter];
    }
    return array_merge((array)$a[$parameter],(array)$b[$parameter]);
}

function mergeJSON($baseName, $resource, $db) {
    $baseResource = getJSON($baseName, $db);
    
    if (!empty($baseResource['extends_resource'])) {
        $baseResource = mergeJSON($baseResource['extends_resource'], $baseResource, $db);
    }
    $result = array();
    $result['id'] = $resource['id'];
    $result['parameters'] = mergeJSONArray($baseResource, $resource, 'parameters');
    //$result['implementation'] = mergeJSONArray($baseResource, $resource, 'implementation');
    $result['name'] = $resource['name'];
    $result['title'] = $resource['title'];
    $result['configuration'] = mergeJSONArray($baseResource, $resource, 'configuration');
    $result['data'] = $resource['data'];
    $result['allowed_subelements'] = mergeJSONArray($baseResource, $resource, 'allowed_subelements');
    $result['type'] = $resource['type'];
    $result['dependencies'] = mergeJSONArray($baseResource, $resource, 'dependencies');
    $result['extends_resource'] = $resource['extends_resource'];
    return $result;
}



function JsonResult($result) {
    echo json_encode($result);
}

function success($message = null, $params = []) {
    if ($message !== null) {
        if (function_exists('Metayota\\softTranslate')) {
            // Übersetzung der Nachricht
            $message = softTranslate($message, $params);
        }
        echo json_encode(array('message' => $message, 'success' => true,'type'=>'success'));
    } else {
        echo '{"success":true}';
    }
}
function failure($message = null) {
    if ($message !== null) {
        echo json_encode(array('message'=>$message,'success'=>false));
    } else {
        echo '{"success":false}';
    }
}
function errorMsg($msg, $params=[], $responseCode=null) { // Please use showErrorMessage
    if ($msg !== null) {
        if (function_exists('Metayota\\softTranslate')) {
            // Übersetzung der Nachricht
            $msg = softTranslate($msg, $params);
        }
    }
    if ($responseCode != null) {
        http_response_code($responseCode);
    }
    echo json_encode(array("error"=>$msg));
    die();
}
function showErrorMessage($msg, $params=[], $responseCode=null) {
    errorMsg($msg, $params, $responseCode);
}

function callAction($resource,$action,$data=null) {
    return callFn($resource,$data,$action);
}

function callResource($resource,$data=null) {
    return callFn($resource,$data);
}

function callFn($resource,$data,$action=null,$get=[]) {
    global $db;
    global $loggedInUser;
    global $serverDB;
    global $user_id;
    $resourceRow = null;
    if (isset($resource)) {
        $name = is_array($resource) ? $resource['name'] : $resource;
        $resourceRow = getResource($name,$db);
    }
    //executeDependencies($resourceRow);
    //$implementation =  $resourceRow['implementation'];
    
    $tabName = "";
    $phpFile = '';
    if (!empty($action)) {
        $propertyName = "$action.php";
        $phpFile = '../'.$resourceRow['name'].'/'.$propertyName;
        //$phpcode = $implementation[$propertyName];
        $tabName = $propertyName;
    } else {
        $phpFile = '../'.$resourceRow['name'].'/'.$resourceRow['name'].'.php';
        //$phpcode = $implementation['php'];
        $tabName = "php";
    }
    if (!empty($resourceRow['configuration'])) {
        $defaults = (array) $resourceRow['configuration'];
    } else {
        $defaults = array();
    }
    
    try {
        $GLOBALS['execution_context'] = ['resource'=>$resourceRow['name'],'tab'=>$tabName];
        ob_start();
        include($phpFile);
        $result = ob_get_contents();
        ob_end_clean();
        //$result = eval( $phpcode );
        return $result;
    } catch(\Throwable $e) {
        if (!headers_sent()) {
            http_response_code(500);
        }
        ob_end_clean();
        
        
        $executionContext = [];
        $executionContext['message'] = $e->getMessage();
        $executionContext['line'] = $e->getLine();
        $executionContext['tab'] = 'unknown';
        $executionContext['resource'] = 'unknown';
        $executionContext['request_uri'] = $_SERVER['REQUEST_URI'];
        
        $pattern = '#.*/(?P<resource>[^/]+)/(?P<tab>[^/]+)\.(?P<technology>[^/]+)$#';
        $file = $e->getFile();
        if (preg_match($pattern, $file, $matches)) {
            
            $executionContext['resource'] = $matches['resource'];
            $executionContext['tab'] = $matches['tab'];
            
            
            $executionContext['request_uri'] = substr($_SERVER['REQUEST_URI'],0,500);
            $technology = $matches['technology'];
            
            if (in_array($technology, ['html', 'css', 'javascript', 'php']) && $executionContext['resource'] == $executionContext['tab']) {
                $executionContext['tab'] = $technology;
            } else {
                $executionContext['tab'] = $matches['tab'];
            }
            
            // print_r($executionContext); // For debugging purposes
        }
        //var_dump($file);
        // debug_print_backtrace();
        //  die('error appeared');
        
        global $db;
        var_dump($executionContext);
        $db->query("INSERT INTO error SET message=:message, line=:line, resource=:resource,tab=:tab,request_uri=:request_uri ON DUPLICATE KEY UPDATE count=count+1, time=CURRENT_TIME()",$executionContext);
        
        echo "<h1>Error in resource $resourceRow[name]</h1>";
        debug_print_backtrace();
        $lastCall = $e->getTrace()[0];
        echo '<h2>'.$e->getMessage().'</h2>';
        $message = $e->getMessage();
        echo $e->getFile()."\n";
        $line = $e->getLine();
        $url = "https://www.metayota.com/editor/resource/$resourceRow[name]/vscode#tab=$tabName,line=".$line;
        echo json_encode(['link'=>$url, 'message'=>$message]);
        
        echo '<p><a target="_top" href="'.$url.'"'.">Show Error (Resource $resourceRow[name] in Tab $tabName on Line $line)</a></p>";
        echo '<pre>'.$e->getTraceAsString().'</pre>';
    }
}

/*function executeDependencies($resourceRow,$executeResource=false) {
    global $db;
    if (!empty($resourceRow['dependencies']) && $resourceRow['dependencies'] != 'null') {
        $dependencies = $resourceRow['dependencies'];
        foreach($dependencies as $dependency) {
            if ($dependency->type == 'php-library') {
                $dependentResourceRow = getResourceByName($dependency->name,$db);
                executeDependencies($dependentResourceRow,true);
            }
        }
    }
    if ($executeResource) {
        $implementationStr = $resourceRow['implementation'];
        $implementation = json_decode($implementationStr);
        
        try {
            if (!in_array($resourceRow['name'], $GLOBALS['executed_dependencies'])) {
                $GLOBALS['executed_dependencies'][] = $resourceRow['name'];
                include_once('../'.$resourceRow['name'].'/'.$resourceRow['name'].'.php');
                //eval($implementation->php);
            }
        } catch(Throwable $e) {
            if (!headers_sent()) {
                http_response_code(500);
            }
            $resource = $resourceRow['name'];
            echo "<h1>Error in resource $resource</h1>";
            $lastCall = $e->getTrace()[count($e->getTrace())-1];
            $message = $e->getMessage();
            $line = $e->getLine();
            //$line = $lastCall['line'];
            $tabName = 'php';
            $url = "https://www.metayota.com/editor/resource/$resource/vscode#tab=$tabName,line=".$line;
            $db->query('INSERT INTO error SET message=:message, line=:line, resource=:resource,tab=:tab,request_uri=:request_uri ON DUPLICATE KEY UPDATE count=count+1, time=CURRENT_TIME()',array('resource'=>$resource,'line'=>$line,'tab'=>$tabName,'message'=>$message,'request_uri'=>$_SERVER['REQUEST_URI']));
            echo '<p><a target="_top" href="'.$url.'"'.">Show Error (Resource $resource in Tab $tabName on Line $line)</a></p>";
            echo '<pre>'.$e->getTraceAsString().'</pre>';
            die();
        }
    }
}*/
?>
