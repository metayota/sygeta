<?php 
namespace Metayota; 
include_once('../metayota/library.php');
include_once('../format-text/format-text.php');
include_once('../slugify/slugify.php');
include_once('../translation-service/translation-service.php');  
include_once('../access-check/access-check.php');
include_once('../sales-document-detail-view/lib.php');


$contract = $db->getById('contract', $data['contract_id']);
$contractTemplate = $db->getById('contract_template', $contract['contract_template_id']);

$lang = $GLOBALS['language'];

$randomString = bin2hex(random_bytes(8));
$folder = '/uploads/contracts/';
if (!is_dir('../..'.$folder)) {
    mkdir($folder, 0750);
}

$filename = 'contract-' . $contract['id'] . '_' . $lang . '_' . $randomString . '.pdf';
$savePath = $folder.$filename;

$params = ['contract_id' => $contract['id']];

if (!empty($contract['contact_id'])) {
    $params['contact_id'] = $contract['contact_id'];
    
    $contact = $db->getById('contact', $contract['contact_id']);
    $lang = $contact['language'];
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
$page = htmlspecialchars($callPath . '?is_export=true&system_access_key='.$appSecret.'&tag=view-contract&'.$additionalParams . $encodedParams, ENT_QUOTES, 'UTF-8');

$templateParams = [];

$title = translateLanguage($contact['language'], $contractTemplate['title']);
$templateParams['title'] = $title;

$templateParams['language'] = $lang;

$result = callResource('pdf-generator', [
    'print_template' => 'print-template-default', 
    'template_params' => $templateParams, 
    'save_path' => $savePath, 
    'page' => $page,
    'language' => $lang
]);

$jsonResult = json_decode($result,true);
$jsonResult['pdf_path'] = $savePath;

$document['file'] = $savePath;
$db->update('contract',['contract_file'=>$filename],['id'=>$contract['id']]);
echo json_encode($jsonResult);

?>