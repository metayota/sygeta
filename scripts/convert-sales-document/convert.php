<?php namespace Metayota; include_once('../metayota/library.php');  

include_once('../access-check/access-check.php');
include('../create-sales-document/lib.php');

checkWritePermissions('sales_document', $data['document_id']);
$document = $db->getById('sales_document', $data['document_id']);

$newDocumentID = createSalesDocument($data['document_type'], $document['contact_id'], $data['title'] ?? '');
$newDocument = $db->getById('sales_document', $newDocumentID);
$items = $db->getAll('sales_document_item', ['document_id'=>$document['id']]);
$i = 0;
foreach($items as $item) {
    unset($item['id']);
    $translationKeyPrefix = 'document_item_'.slugifyWithUnderscore($newDocument['document_number']).'_'.($i++).'_';
    $item['description'] = copyTranslations($item['description'], $translationKeyPrefix.'description', []);
    $item['detail'] = copyTranslations($item['detail'], $translationKeyPrefix.'detail', []);
    $item['document_id'] = $newDocument['id'];
    $db->insert('sales_document_item', $item);
}
$db->update('sales_document', ['currency'=>$document['currency']], $newDocumentID);
echo json_encode(['success'=>true,'message'=>translate('msg_document_convert_success'), 'id'=>$newDocumentID]);

?>