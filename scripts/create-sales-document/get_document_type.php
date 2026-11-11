<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../slugify/slugify.php');   
    $documentType = $db->getByName('sales_document_type', $data['document_type']);
    echo json_encode($documentType);
?>