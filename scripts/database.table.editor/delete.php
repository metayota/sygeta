<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
$useDB = $db;
if (getConfig('main_server')) {
    $useDB = $serverDB;
    $allAccess = true;
} else {
    $allAccess = false;
}

$table = $data['table'];
$where = ['id'=>$data['row_id']];
    if (!$allAccess && !hasAccess('database',$table)) {
        if (hasAccess('db.table.edit.owned')) {
            $where['user_id'] = $user_id;
        } else {
            http_response_code(403);
            echo json_encode(['message'=>'You have no access to delete data in the database table '.$table,'highlight'=>'db','resource'=>$table]);
            die();
        }
    }
    

$useDB->delete($data['table'],$where);
?>