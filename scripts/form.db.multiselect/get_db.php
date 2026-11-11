<?php namespace Metayota; include_once('../metayota/library.php');   

    if (hasAccess('database',$data['reference_table']) || hasAccess('db.table.list.owned',$data['reference_table'])) {
        $ownCol = $data['own_id_column'];
        $otherCol = $data['other_id_column'];
        $results = [];

        $columns = $db->getAll($data['reference_table'],[$ownCol => $data['own_id']]);
        foreach($columns as $col) {
            $results[] = $col[$otherCol];
        }
        echo json_encode($results);
    }
    
?>