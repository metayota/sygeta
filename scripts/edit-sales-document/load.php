<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../access-check/access-check.php');  
    include_once('../access-check/access-check.php');

    $id = $data['id'];
    checkReadPermissions('sales_document', $id);
    $document = $db->getById('sales_document', $id);
    $documentType = $db->getByName('sales_document_type', $document['document_type']);
    echo json_encode(['document'=>$document,'documentType'=>$documentType]);
?>