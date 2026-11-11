<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../slugify/slugify.php');  
include('../create-sales-document/lib.php');

$documentID = createSalesDocument($data['document_type'], $data['contact_id'], $data['title'] ?? '');

echo json_encode([
        'type' => 'success',
        'success' => true,
        'id' => $documentID, 
        'message' => translate('sales_document_created')
    ]);
?>