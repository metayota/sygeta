<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../send-mail/send-mail.php');   include_once('../translation-service/translation-service.php');  
    $c = $_GET['c'];
    $result = $db->fetchQuery("SELECT * FROM user WHERE verification_code=:c", array('c' => $c));
    if ($result) {
        $db->query("UPDATE user SET verification_code=NULL,status='active' WHERE verification_code=:c",array('c' => $c));
		header('Location: /login/confirmed-mail');
    } else {
        header('Location: /login/confirmed-mail');
    }
?>