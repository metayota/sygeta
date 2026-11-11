<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
$table = $data['resource_name'];
$saveData = $data['data'];

if (hasAccess('database',$table)) {
    // User has access
} else if (hasAccess('db.table.edit.owned',$table)) {
    $saveData['user_id'] = $user_id;
} else {
    http_response_code(403);
    echo json_encode(['message'=>'You have no access to update data in the database table '.$table,'highlight'=>'db','resource'=>$table]);
    die();
}

$db->insert($table, $saveData);
$insertID = $db->lastInsertId();
echo json_encode(['message'=>translate('create_row_success_'.$table), 'success'=>true, 'insert_id'=>$insertID]);
?>