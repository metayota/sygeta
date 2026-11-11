<?php
    namespace Metayota;
    include('../metayota/library.php');
    
    $id = $db->getColumnByName('contract_template', 'id', $data['name']);
    echo json_encode(['id'=>$id]);
?>