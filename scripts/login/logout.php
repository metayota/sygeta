<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }
    unset($_SESSION['loggedInUser']);
    if (!empty($_COOKIE['token'])) {
        $db->delete('remember_me',array('token' => $_COOKIE['token']));
    }
    unset($_COOKIE['token']);
    $domain = $_SERVER['HTTP_HOST'];
    if(strpos($domain, 'www.') === 0) {
        $domain = substr($domain, 4);
    }
    $domain = "." . $domain; 
    setcookie('token', '', time() - 3600, '/', $domain, false, true);
    session_destroy();
    if (!empty($_GET['redirect'])) {
        header('Location: /');
    }
    success('logout_successful');
?>