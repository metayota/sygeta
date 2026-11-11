<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    $loginRedirect = getConfig('login_redirect_url', '/login');
    echo json_encode(['login_redirect_url' => $loginRedirect]);

?>