<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  

    if ($data['action'] == 'save') {
        if (!hasAccess('database',$table)) {
            if (hasAccess('db.table.edit.owned',$table)) {
                $db->update($table,$data['data'],['user_id'=>$user_id, 'id'=>$data['rowid']]);
            } else {
                http_response_code(403);
                echo json_encode(['message'=>'You have no access to update data in the database table '.$table,'highlight'=>'db','resource'=>$table]);
                die();
            }
        } else {
            $db->update($table,$data['data'],array('id'=>$data['rowid']));
        }
        msg('msg_data_updated',[],'success');
    }
?>