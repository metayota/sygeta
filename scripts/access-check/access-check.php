<?php 
    namespace Metayota;
    include_once('../metayota/library.php');  
    
    function checkReadPermissions($table, $rowID) {
        global $db;
        global $user_id;
        if (hasAccess('database',$table) || hasAccess('db.table.get_all',$table)) {
            // User has access
        } else if (hasAccess('db.table.list.owned',$table)) {
            $dbRowUserID = $db->getColumnById($table, 'user_id', $rowID);
            if ($dbRowUserID !== false && $dbRowUserID != $user_id) {
                http_response_code(403);
                echo json_encode(['message'=>'You have no access to update data in the database table '.$table,'highlight'=>'db','resource'=>$table]);
                die();            
            }
        } else {
            http_response_code(403);
            echo json_encode(['message'=>'You have no access to update data in the database table '.$table,'highlight'=>'db','resource'=>$table]);
            die();
        }
    }
    function checkWritePermissions($table, $rowID) {
        global $db;
        global $user_id;
        if (hasAccess('database',$table)) {
            // User has access
        } else if (hasAccess('db.table.edit.owned',$table)) {
            $dbRowUserID = $db->getColumnById($table, 'user_id', $rowID);
            if ($dbRowUserID !== false && $dbRowUserID != $user_id) {
                http_response_code(403);
                echo json_encode(['message'=>'You have no access to update data in the database table '.$table,'highlight'=>'db','resource'=>$table]);
                die();            
            }
        } else {
            http_response_code(403);
            echo json_encode(['message'=>'You have no access to update data in the database table '.$table,'highlight'=>'db','resource'=>$table]);
            die();
        }
    }
?>