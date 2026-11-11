<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    if (hasAccess('database',$data['reference_table']) && hasAccess('database',$data['table'])) {
        $ownCol = $data['own_id_column'];
        $otherCol = $data['other_id_column'];
        $results = [];
        $columns = $db->getAll($data['reference_table'],[$ownCol => $data['own_id']]);
        
        foreach($columns as $col) {
            $colRow = $db->getById($data['table'],$col[$otherCol]);
            if ($data['translate_options']) {
                $results[] = translate($colRow[$data['titlefield']]);
            } else {
                $results[] = $colRow[$data['titlefield']];
            }
        }
        echo json_encode($results);
    }
?>