<?php
namespace Metayota;
require_once('../../conf/db-config.php');
require_once('../../conf/cache-config.php');
require_once('../../conf/main-config.php');
require_once('lib.php');
require_once('lib_access.php');
require_once('lib_db.php');
require_once('resource_lib.php');

if (file_exists('../translation-service/translation-service.php')) {
    require_once('../translation-service/translation-service.php');
} else {
    function softTranslate($key) {
        return $key;
    }
}



global $db;
$db = new PDORepository();
$accessCheck = true;
$resourceName = isset($_GET['name']) ? $_GET['name'] : $data['name'];
$serverPrefix = false;
if (isset($resourceName)) {
    if (substr($resourceName, 0, 7) == 'server:') {
        if (getConfig('wordpress')) {
            $resourceName = substr($resourceName, 7);
        } else {
            if (empty($_SESSION['server'])) {
                die('not connected');
            }
            $serverPrefix = true;
            $resourceName = substr($resourceName, 7);
            $server = $db->fetchQuery('SELECT * FROM server WHERE id=:serverid AND user_id=:user_id', array('serverid' => $_SESSION['server'], 'user_id'=>$user_id)); // user check?
            $db = new PDORepository($server);
            $accessCheck = false;
        }
    }
}

if (isset($_GET['name']) && isset($_GET['item'])) {
    if ($accessCheck) {
        requireAccess('resource-json', $resourceName);
    }
    if (str_ends_with($_GET['item'],'php')) {
        die('NO ACCESS');
    }
    $resource = getResourceByName($resourceName, $db);
    //$impl = $resource['implementation'];
    //$decoded = json_decode($impl);
    $item = $_GET['item'];
    if ($item == 'javascript' || $item == 'html' || $item == 'css'  || $item == 'php') {
        $fileName = $resource['name'].'.'.$item;
    }
    $arr = explode('.', $item);
    $extension = array_pop($arr);
    if ($extension == 'svg') {
        header('Content-type: image/svg+xml');
        $fileName = $item;
    }
    if ($extension == 'css') {
        header('Content-type: text/css');
        $fileName = $item;
    }
    if ($extension == 'js') {
        header('Content-type: text/javascript');
        $fileName = $item;
    }
    if (!$serverPrefix) {
        sendCacheHeaders();
    }
    $filePath = '../'.$resource['name'].'/'.$fileName;
    
    if (file_exists($filePath)) {
        echo file_get_contents($filePath);
    } else {
        
        header('HTTP/1.0 404 Page not found');
        echo "404 PAGE NOT FOUND: $resourceName - fn:".$fileName;
    }
    die();
}

$output = isset($_GET['output']) ? $_GET['output'] : $data['output'];

if ($output == 'implementation') {
    $resource = getResourceByName($resourceName, $db);
    $file = isset($_GET['file']) ? basename($_GET['file']) : null;

    if ($file) {
        $fileName = $file;
        if ($file == 'javascript' || $file == 'html' || $file == 'css'  || $file == 'php') {
            $fileName = $resource['name'].'.'.$file;
        }
        if ($accessCheck) {
            if ($file == 'javascript' || $file == 'html' || $file == 'css') {
                requireAccess('resource-json', $resourceName);
            } else {
                requireAccess('resource', $resourceName, $file);
            }
        }
        if (!$serverPrefix) {
            sendCacheHeaders();
        }
        if ($file == 'javascript') {
            header('Content-type: text/javascript');
        }
        if (str_ends_with($file, 'css')) {
            header('Content-type: text/css');
        }
        echo file_get_contents('../'.$resource['name'].'/'.$fileName);
    } 
}
if ($output == 'json') {
    header('Content-type: application/json');
    if ($accessCheck) {
        requireAccess('resource-json', $resourceName);
    }
    if (!$serverPrefix) {
        $cacheTime = $db->getByQuery("SELECT cachetime FROM resource WHERE name=:name",['name'=>$resourceName]); // fix
        sendCacheHeaders($cacheTime['cachetime']);
    }
    $result = getBrowserJSON($resourceName, $db);
    if (!$result) {
        http_response_code(404);
        msg('Resource not found '.$resourceName);
        die();
    }
    if (!empty($result['extends_resource']) && $result['extends_resource'] != null) {
        $result = mergeBrowserJSON($result['extends_resource'], $result, $db);
    }
    echo json_encode($result);
}

?>