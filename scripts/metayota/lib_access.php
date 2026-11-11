<?php
namespace Metayota;
if (isset($_COOKIE['PHPSESSID'])) {
    if(session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}
require_once('lib_db.php');
$db = new PDORepository();
$loggedInUser = !empty($_SESSION['loggedInUser']) ? $_SESSION['loggedInUser'] : null;
if ($loggedInUser == null) {
    if (!empty($_COOKIE['token'])) {
        $rememberMe = $db->get('remember_me', array('token' => $_COOKIE['token']));
        if (!empty($rememberMe)) {
            $loggedInUser = $db->get('user', array('id' => $rememberMe['user_id']));
            $usergroupRow = $db->getById('usergroup', $loggedInUser['usergroup_id']);
            if (!empty($loggedInUser)) {
                if (!isset($_COOKIE['PHPSESSID'])) {
                    session_start();
                }
                $_SESSION['loggedInUser'] = $loggedInUser;
                $_SESSION['usergroup'] = $usergroupRow['name'];
            }
        }
    }
    
    // API-Key aus verschiedenen Quellen holen
    $apiKey = null;
    
    // 1. Aus Query-Parameter
    if (isset($_GET['system_access_key'])) {
        $apiKey = $_GET['system_access_key'];
    }

    if (isset($_GET['api_key'])) {
        $apiKey = $_GET['api_key'];
    }
    
    // 2. Aus Authorization Header (Bearer Token)
    if (!$apiKey && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        if (preg_match('/Bearer\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            $apiKey = $matches[1];
        }
    }
    
    // API-Key validieren
    if ($apiKey) {
        if (getConfig('app_secret') == $apiKey) {
            $loggedInUser = ['id'=>1, 'usergroup_id'=>3, 'is_admin'=>true];
            if (!isset($_COOKIE['PHPSESSID'])) {
                session_start();
            }
            $_SESSION['loggedInUser'] = $loggedInUser;
        } else {
		if (getConfig('metayota_sync_api_key') != $apiKey) {
			// at the moment the sync api is handled differently
              $apiKeyRow = $db->get('api_key', ['api_key'=>$apiKey]);
              if ($apiKeyRow) {
                  $loggedInUser = $db->getById('user', $apiKeyRow['user_id']);
                  $_SESSION['loggedInUser'] = $loggedInUser;
              }	    
	    } 
        }
    }
    
}

if (getConfig('wordpress')) {
    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
        $loggedInUser = [];
        $loggedInUser['is_admin'] = in_array( 'administrator', (array) $current_user->roles );
        $loggedInUser['username'] = $current_user->user_login;
        $loggedInUser['firstname'] = $current_user->user_firstname;
        $loggedInUser['usergroup_id'] = [];
        foreach ( $current_user->roles as $role_slug ) {
            $usergroup = $db->getByName('usergroup', $role_slug);
            $loggedInUser['usergroup_id'][] = $usergroup['id'];
        }
        $_SESSION['loggedInUser'] = $loggedInUser;
    }
} else {
    if (!empty($_SESSION['loggedInUser'])) {
        $_SESSION['loggedInUser']['is_admin'] = $_SESSION['loggedInUser']['usergroup_id'] == 3;
        $loggedInUser = $_SESSION['loggedInUser'];
    }
}

session_write_close();

$GLOBALS['loggedInUser'] = $loggedInUser;

function denyAccess($message) {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(array("error" => $message));
    die();
}

function checkApiKey() {
    $keyCorrect = false;
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        if (preg_match('/Bearer\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            $apiKey = $matches[1];
            $keyCorrect = (getConfig('metayota_sync_api_key') == $apiKey);
        }
    }
    if (!$keyCorrect) {
        denyAccess("Please make sure that the metayota_sync_api_key in your configuration is the same like in your server settings in the editor");
    }
}

function hasAccess($right, $resource = null, $field = null)
{
    global $db;
    if (isset($GLOBALS['loggedInUser'])) {
        $usergroups = $GLOBALS['loggedInUser']['usergroup_id'];
        if (!is_array($usergroups)) {
            $usergroups = [$usergroups];
        }
        if (isset($GLOBALS['loggedInUser']['is_admin']) && $GLOBALS['loggedInUser']['is_admin'] == true) {
            return true;
        }
    } else {
        $usergroups = [1];
    }
    
    foreach($usergroups as $usergroup_id) {
        $where = array('access_right' => $right, 'usergroup_id' => $usergroup_id);
        if ($resource != null) {
            $where['resource_name']  = $resource;
        }
        if ($field != null) {
            $where['resource_field'] = $field;
        }
        
        $result = $db->get('access', $where);
        if (!empty($result)) {
            return true;
        }
    }
    return false;
}

function requireAccess($right, $resource = null, $field = null)
{
    if (!hasAccess($right, $resource, $field)) {
        header('HTTP/1.0 403 Forbidden');       
        $msg = "You don't have permission to access this resource.";
        echo json_encode(array("error" => $msg, "resource" => $resource, "field"=>$field));
        die();
    }
}

function isAdmin() {
    return isset($_SESSION['loggedInUser']) && $_SESSION['loggedInUser']['is_admin'];
}
?>
