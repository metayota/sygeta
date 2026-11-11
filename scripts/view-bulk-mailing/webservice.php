<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    $bulkMailingReceivers = $db->getAllByQuery("SELECT * FROM bulk_mailing_receiver WHERE file IS NULL LIMIT 3");

    foreach($bulkMailingReceivers as $bulkMailingReceiver) {
        $bulkMailing = $db->getById('bulk_mailing', $bulkMailingReceiver['bulk_mailing_id']);
        $params = ['id'=>$bulkMailing['sales_document_id'],'contact_id'=>$bulkMailingReceiver['contact_id'],'bulk_mailing'=>true];
        $result = callAction('sales-document-detail-view','export',$params);
        
        $resultJson = json_decode($result, true);
        $bulkMailingReceiver['file'] = $resultJson['pdf_path'];
        $db->update('bulk_mailing_receiver',$bulkMailingReceiver);
    }
?>