<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
$useDB = $db;
if (getConfig('main_server')) {
    $useDB = $serverDB;
    $allAccess = true;
} else {
    $allAccess = false;
}

$table = $data['table'];
$where = array('id'=>$data['rowid']);
if (!$allAccess && !hasAccess('database',$table)) {
    if (hasAccess('db.table.edit.owned')) {
        $where['user_id'] = $user_id;
    } else {
        http_response_code(403);
        echo json_encode(['message'=>'You have no access to update data in the database table '.$table,'highlight'=>'db','resource'=>$table]);
        die();
    }
}

$data['data'] = array_filter( $data['data'], fn($key) => !str_ends_with($key, '_translated'), ARRAY_FILTER_USE_KEY );
$useDB->update($data['table'],$data['data'],$where);
?>