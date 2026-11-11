<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../format-text/format-text.php');   include_once('../slugify/slugify.php');   include_once('../translation-service/translation-service.php');  
include_once('../access-check/access-check.php');
include_once('../sales-document-detail-view/lib.php');

checkReadPermissions('sales_document', $data['id']);

$document = $db->getById('sales_document', $data['id']);
$lang = $GLOBALS['language'];
$documentID = $document['id'];
$randomString = bin2hex(random_bytes(8));
$folder = '/uploads/sales-documents/';
if (!is_dir('../..'.$folder)) {
    mkdir($folder, 0750);
}

if (empty($data['bulk_mailing'])) {
    if (!empty($document['file'])) {
    unlink('../../'.$document['file']);
    } 
}
$savePath = $folder.'sales-document-' . $document['id'] . '_' . $lang . '_' . $randomString . '.pdf';

$params = ['document_id' => $document['id']];

if (!empty($data['contact_id'])) {
    $params['contact_id'] = $data['contact_id'];
    $contact = $db->getById('contact', $data['contact_id']);
    $lang = $contact['language'];
} else if (!empty($document['contact_id'])) {
    $contact = $db->getById('contact', $document['contact_id']);
    if (!empty($contact)) {
        $lang = $contact['language'];
    }
}

// *** NEW: Check if ZUGFeRD is enabled for this document type ***
$zugferdData = null;
if (!empty($document['document_type'])) {
    $documentType = $db->getByName('sales_document_type', $document['document_type']);
    if (!empty($documentType['zugferd']) && $documentType['zugferd'] == 1) {
        $zugferdData = prepareZugferdData($document, $contact, $db);
    }
}

$encodedParams = http_build_query(['params' => json_encode($params), 'language' => $lang]);

$callPath = '/call/print-index';
if (getConfig('wordpress')) {
    $callPath = '/wp-content/plugins/metayota/scripts/print-index/print-index.php';
}

if (!empty($document['print_layout_id'])) {
    $printLayout = $db->getById('print_layout',$document['print_layout_id']);
    $additionalParams = 'layout_name='.$printLayout['name'].'&';
} else {
    $additionalParams = '';
}

$appSecret = getConfig('app_secret');
$page = htmlspecialchars($callPath . '?is_export=true&system_access_key='.$appSecret.'&tag=sales-document-detail-view&'.$additionalParams . $encodedParams, ENT_QUOTES, 'UTF-8');

$templateParams = ['title' => $document['title']];

$itemCountRow = $db->getByQuery("SELECT count(*) as count FROM sales_document_item WHERE document_id=:id",['id'=>$data['id']]);
$itemCount = $itemCountRow['count'];

if ($itemCount == 0) {
    $templateParams['no_items'] = true;
}

if ($document['document_type'] == 'letter') {
    $templateParams['account_information'] = 'hide';
} else {
    $templateParams['account_information'] = 'show';
}

if (!empty($printLayout)) {
    $templateParams['layout_name'] = $printLayout['name'];
}

$templateParams['language'] = $lang;

// *** NEW: Add ZUGFeRD data to parameters if enabled ***
if ($zugferdData) {
    $templateParams['zugferd_data'] = $zugferdData;
}

$result = callResource('pdf-generator', [
    'print_template' => 'print-template-bill', 
    'template_params' => $templateParams, 
    'save_path' => $savePath, 
    'page' => $page,
    'language' => $lang
]);

$jsonResult = json_decode($result,true);
$jsonResult['pdf_path'] = $savePath;

$document['file'] = $savePath;
$db->update('sales_document',$document);
echo json_encode($jsonResult);

?>