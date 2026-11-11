<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    $resource = $db->getByName('resource', $data['resourcetype']);
    $title = translate($resource['title']);
    $description = translate($resource['description']);
    echo json_encode(['title'=>$title, 'description'=>$description]);
?>