<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
$useDB = $db;
if (getConfig('main_server')) {
    $useDB = $serverDB;
    $allAccess = true;
} else {
    $allAccess = false;
}
$table = $data['table'];
if (!$allAccess && !hasAccess('database',$table)) {
    if (hasAccess('db.table.edit.owned')) {
        $data['resourceData']['user_id'] = $user_id;
    } else {
        http_response_code(403);
        echo json_encode(['message'=>'You have no access to insert data into the database table '.$table,'highlight'=>'db','resource'=>$table]);
        die();
    }
}
$data['resourceData'] = array_filter($data['resourceData'], fn($key) => !str_ends_with($key, '_translated'),ARRAY_FILTER_USE_KEY);
if (!empty($data['where'])) {
    $data['resourceData'] = array_merge($data['resourceData'],$data['where']);
}
$useDB->insert($data['table'],$data['resourceData']);
?>