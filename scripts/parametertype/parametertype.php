<?php namespace Metayota; include_once('../metayota/library.php');  
    if (isset($data['action']) && $data['action'] == 'save') {
        $value = (array) json_decode($data['value']);
       // echo 'value was'.var_dump($value);
        $db->insert('parametertype',$value);
        echo json_encode(array('successful'=>true));
    }
    if (isset($data['action']) && $data['action'] == 'load') {
        echo json_encode($db->fetchAll('parametertype'));
    }
?>