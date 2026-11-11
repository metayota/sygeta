<?php
// New Version 2
namespace Metayota;
require_once('../../conf/db-config.php');
require_once('../../conf/cache-config.php');
require_once('../../conf/main-config.php');
require_once('../metayota/lib.php');
require_once('../metayota/lib_db.php');
require_once('../metayota/lib_access.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkReferer = true;
    $requestURI = $_SERVER['REQUEST_URI'];
    foreach ($GLOBALS['no_referer_check'] as $noRefererCheck) {
        if (str_starts_with($requestURI, $noRefererCheck)) {
            $checkReferer = false;
        }
    }
    if ($checkReferer) {
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
            $host = $_SERVER['HTTP_HOST'];
            $isAllowed = false;
            foreach ($GLOBALS['allowed_referers'] as $allowedReferer) {
                if (str_starts_with($referer, $allowedReferer)) {
                    $isAllowed = true;
                }
            }
            if (!$isAllowed) {
                error_log("Metayota Security Check Failed");
                echo json_encode(['message'=>'The domain is not eligible to send post requests. Please contact your administrator.']);
                die();
            }
        } else {
            die('METAYOTA SECURITY CHECK FAILED / HEADER MISSING');
        }
    }
}

function customErrorHandler($severity, $message, $file, $line, $trace = null) {
    global $db;
    $ignoreList = [
        'lib_db.php',
        'library.php',
        'lib_db',
        'library',
        '/scripts/metayota/lib_db.php',
        '/scripts/metayota/library.php'
    ];
    
    $backtrace = $trace ?? debug_backtrace();
    $stackTrace = [];
    $ignoredEntry = null; // Store the ignored entry for dual logging
    
    foreach ($backtrace as $traceEntry) {
        if (isset($traceEntry['file']) && isset($traceEntry['line'])) {
            $isIgnored = false;
            foreach ($ignoreList as $ignoredFile) {
                $currentBasename = basename($traceEntry['file']);
                if (strpos($traceEntry['file'], $ignoredFile) !== false ||
                $currentBasename === $ignoredFile) {
                    $isIgnored = true;
                    $ignoredEntry = $traceEntry; // Store the ignored entry
                    break;
                }
            }
            if (!$isIgnored) {
                $stackTrace[] = [
                    'file' => $traceEntry['file'],
                    'line' => $traceEntry['line'],
                    'function' => $traceEntry['function'] ?? '',
                ];
                break;
            }
        }
    }
    
    // Non-fatal error handling
    $nonFatalErrors = [E_WARNING, E_NOTICE, E_USER_WARNING, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED];
    if (in_array($severity, $nonFatalErrors, true)) {
        try {
            $resource = $file ? basename(dirname($file)) : '';
            $tab = $file ? pathinfo($file, PATHINFO_FILENAME) : '';
            $db->insertIgnore('error', [
                'message' => $message,
                'resource' => $resource,
                'char_start' => 0,
                'line' => $line,
                'tab' => $tab,
                'time' => date('Y-m-d H:i:s'),
                'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            ]);
            
            // If we have both an ignored entry and a calling location, log both
            if ($ignoredEntry && !empty($stackTrace)) {
                $callingResource = basename(dirname($stackTrace[0]['file']));
                $callingTab = pathinfo($stackTrace[0]['file'], PATHINFO_FILENAME);
                $db->insertIgnore('error', [
                    'message' => $message . ' (called from)',
                    'resource' => $callingResource,
                    'char_start' => 0,
                    'line' => $stackTrace[0]['line'],
                    'tab' => $callingTab,
                    'time' => date('Y-m-d H:i:s'),
                    'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            error_log("Failed to log warning/notice to database: " . $e->getMessage());
        }
        return; // Do not stop the script for non-fatal errors
    } else {
        
        // Fatal error handling
        if (!empty($stackTrace)) {

            $filePath = !empty($stackTrace) && isset($stackTrace[0]['file']) ? $stackTrace[0]['file'] : '';
            $tab = $filePath ? pathinfo($filePath, PATHINFO_FILENAME) : '';
            $resource = $filePath ? basename(dirname($filePath)) : '';
            $errorLine = !empty($stackTrace) && isset($stackTrace[0]['line']) ? $stackTrace[0]['line'] : 0;
            
            $errorData = [
                'resource' => $resource,
                'line' => $errorLine,
                'char_start' => 0,
                'message' => $message,
                'tab' => $tab,
                'time' => date('Y-m-d H:i:s'),
                'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
                'count' => 1
            ];
            
            try {
                $db->insertIgnore('error', $errorData);
                
                // If we have an ignored entry, also log the actual error location
                if ($ignoredEntry) {
                    $actualResource = basename(dirname($ignoredEntry['file']));
                    $actualTab = pathinfo($ignoredEntry['file'], PATHINFO_FILENAME);
                    $actualErrorData = [
                        'resource' => $actualResource,
                        'line' => $ignoredEntry['line'],
                        'char_start' => 0,
                        'message' => $message . ' (actual location)',
                        'tab' => $actualTab,
                        'time' => date('Y-m-d H:i:s'),
                        'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
                        'count' => 1
                    ];
                    $db->insertIgnore('error', $actualErrorData);
                }
                
                header("HTTP/1.1 500 Internal Server Error");
                $errorData['type'] = 'error';
                echo json_encode($errorData);
                exit; // Stop script for fatal errors
            } catch (\Exception $e) {
                error_log("Failed to log fatal error to database: " . $e->getMessage());
                error_log("Original error: " . print_r($errorData, true));
            }
        } else {
            try {
                $errorRow = [
                    'message' => $message,
                    'file' => $file,
                    'line' => $line,
                    'severity' => $severity,
                    'time' => date('Y-m-d H:i:s'),
                    'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
                ];
                $tab = '';
                if (!empty($file)) {
                    $tab = preg_match('/([^\/\\\\]+)\.[^\.]+$/', $file, $matches) ? $matches[1] : '';
                }

                $resource = $file ? basename(dirname($file)) : '';
                $fileName = $file ? basename($file) : '';
                
                $errorData = [
                    'resource' => $resource,
                    'line' => $line,
                    'char_start' => 0,
                    'message' => $message,
                    'tab' => $tab,
                    'time' => date('Y-m-d H:i:s'),
                    'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
                    'count' => 1
                ];
                $db->insertIgnore('error', $errorData);
                $errorData['link'] =  "https://static.metayota.com/editor/resource/$resource/vscode#tab=$fileName,line=".$line;
                $errorData['button_name'] = 'Editor';
                header("HTTP/1.1 500 Internal Server Error");
                echo json_encode($errorData);
            } catch (\Exception $e) {
                echo 'Could not log error:'.$e->getMessage();
                error_log("Failed to log warning/notice to database: " . $e->getMessage());
            }
        }
    }
}



set_exception_handler(function($exception) {
    customErrorHandler(
    E_ERROR,
    $exception->getMessage(),
    $exception->getFile(),
    $exception->getLine(),
    $exception->getTrace()
    );
});

register_shutdown_function(function() {
    $error = error_get_last();
    
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        \Metayota\customErrorHandler(
        $error['type'],
        $error['message'],
        $error['file'],
        $error['line'],
        [ // Simulated backtrace
        [
            'file' => $error['file'] ?? 'unknown',
            'line' => $error['line'] ?? 0,
            'function' => 'shutdown_function'
        ]
    ]
    );
}
});


set_error_handler(function($severity, $message, $file, $line) {
    \Metayota\customErrorHandler($severity, $message, $file, $line);
});

header('Last-Modified: ' . gmdate("D, d M Y H:i:s", time()));

$db = new PDORepository();
global $loggedInUser;
$user_id = isset($loggedInUser['id']) ? $loggedInUser['id'] : NULL;
global $user_id;
$usergroup = isset($_SESSION['usergroup']) ? $_SESSION['usergroup'] : NULL;

if (getConfig('wordpress')) {
    $serverDB = $db;
}
if (getConfig('main_server')) {
    $serverID = isset($_SESSION['server']) ? $_SESSION['server'] : NULL;
    if (empty($serverID)) {
        if (isset($_COOKIE['server_id'])) {
            $server = $db->get('server',['user_id'=>$user_id,'id'=>$_COOKIE['server_id']]);
            if (!empty($server)) {
                $serverID = $_COOKIE['server_id'];
                if (session_status() != PHP_SESSION_ACTIVE) {
                    session_start();
                }
                $_SESSION['server'] = $serverID;
                session_write_close();
            }
        }
    }
    
    if (!empty($serverID)) {
        $server = $db->fetchQuery('SELECT * FROM server WHERE id=:serverid', array('serverid' => $serverID));
        if (!empty($server)) {
            try {
                $serverDB = new PDORepository($server);
            } catch(\Exception $e) {
              //  var_dump($server);
             //   var_dump($server);
          //      echo $e->getMessage();
                if (session_status() != PHP_SESSION_ACTIVE) {
                    session_start();
                }
                unset($_SESSION['server']);
                session_write_close();
            }
        } else {
            $hasTask = $db->get('task', array('server_id' => $serverID, 'assigned_to_user' => $user_id, 'task_state' => 6));
            if (!empty($hasTask)) {
                $server = $db->fetchQuery('SELECT * FROM server WHERE id=:serverid', array('serverid' => $serverID));
                $serverDB = new PDORepository($server);
            } else {
                $_SESSION['server'] = NULL;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = [];
} else {
    if (empty($_POST)) {
        $REQUEST_BODY = file_get_contents('php://input');
        $data = json_decode($REQUEST_BODY, true);
    } else {
        $data = $_POST;
    }
}

preg_match('!^.*/(.*?)/[^/]+$!', $_SERVER['SCRIPT_NAME'], $matches);
if (isset($matches[1])) {
    $resource = $matches[1];
    requireAccess('call', $resource);
}

$get = $_GET;
?>