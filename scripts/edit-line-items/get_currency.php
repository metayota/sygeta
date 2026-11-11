<?php namespace Metayota; include_once('../metayota/library.php');  
    include_once('../access-check/access-check.php');
    checkReadPermissions('sales_document', $data['document_id']);

    $sql = 'SELECT c.* FROM sales_document sd LEFT JOIN currency c ON c.currency_code = sd.currency WHERE sd.id=:document_id';
    $currency = $db->getByQuery($sql, ['document_id'=>$data['document_id']]);
    echo json_encode($currency);
?>