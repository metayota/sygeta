<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../sync-functions/sync-functions.php');   include_once('../translation-service/translation-service.php');  
    requireAccess('database','resource');//Maybe check task
    //$task = $db->getById('task',$_GET['task']);
//if ($task['worker_id'] == $user_id) {
    $resourcename = $data['resource'];
    $dependencies = getResourceDependencies($resourcename);
    echo json_encode($dependencies);
//}
?>