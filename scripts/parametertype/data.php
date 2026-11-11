<?php namespace Metayota; include_once('../metayota/library.php');   
    $parameterTypes = $db->getAllByQuery('SELECT * FROM parametertype ORDER BY title');
    echo json_encode($parameterTypes);
?>