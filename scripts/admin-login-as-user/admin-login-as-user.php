<?php namespace Metayota; include_once('../metayota/library.php');   
    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }
    $user = $db->get('user',['id'=>$data['user_id']]);
    if (!empty($user)) {
        $_SESSION['loggedInUser'] = $user;
    } 
    success();
?>