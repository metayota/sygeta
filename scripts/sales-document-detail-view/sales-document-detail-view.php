<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../format-text/format-text.php');   include_once('../slugify/slugify.php');   include_once('../translation-service/translation-service.php');  
include_once('../resource-parameter-formatter/resource-parameter-formatter.php');
include_once('../access-check/access-check.php');
include('../contacts-lib/contacts-lib.php');
include('../sales-document-detail-view/lib.php');

checkReadPermissions('sales_document', $data['id']);
$document = $db->getById('sales_document', $data['id']);
$documentType = $db->getByName('sales_document_type', $document['document_type']);
$items = $db->getAllByQuery('SELECT * FROM sales_document_item WHERE document_id=:document_id ORDER BY position', ['document_id' => $data['id']]);
if (!empty($data['contact_id'])) {
    $contact = $db->getById('contact',$data['contact_id']);
} else {
    $contact = $db->getById('contact', $document['contact_id']);
}
$company = $db->get('company_settings');

$allVars = [];


if (!empty($documentType['custom_form']) && !empty($document['custom_form_data'])) {
    $allVars = formatResourceValues($documentType['custom_form'], json_decode($document['custom_form_data'],true), $contact['language']);

}


foreach($company as $key => $value) {
    $allVars['company_'.$key] = $value;
}
foreach($contact as $key => $value) {
    $allVars[$key] = $value;
}
$allVars['salutation'] = getSalutation($contact);

$decimalSeparator = translateLanguage($contact['language'], 'number_format_decimal_separator');
$groupSeparator = translateLanguage($contact['language'], 'number_format_group_separator');

// Initialisierung der Summen
$totalNet = 0;
$totalVat = 0;
$totalGross = 0;
$vatByRate = []; // NEW: VAT totals per tax rate

// Berechnung pro Position und Gesamtsummen
foreach ($items as &$item) {
    $unitPrice = $item['unit_price'];
    $quantity = $item['quantity'];
    $taxRate = $item['tax_rate'] / 100;

    $hasUnitPrice = !empty($item['unit_price']);
    $hasTotal = !empty($item['total']);

    if ($hasUnitPrice && $hasTotal) {
        $itemGross = $item['total'];
        $itemNet = $itemGross / (1 + $taxRate);
        $itemVat = $itemGross - $itemNet;
    } elseif ($hasTotal && !$hasUnitPrice) {
        $itemGross = $item['total'];
        $itemNet = $itemGross / (1 + $taxRate);
        $itemVat = $itemGross - $itemNet;
    } elseif ($hasUnitPrice && !$hasTotal) {
        $itemNet = $quantity * $unitPrice;
        $itemVat = $itemNet * $taxRate;
        $itemGross = $itemNet + $itemVat;
    } else {
        $itemNet = 0;
        $itemVat = 0;
        $itemGross = 0;
    }

    // Summen aktualisieren
    $totalNet += $itemNet;
    $totalVat += $itemVat;
    $totalGross += $itemGross;

    // NEW: VAT grouped by tax rate
    $rateKey = $item['tax_rate']; // e.g. "7" or "19"
    if (!isset($vatByRate[$rateKey])) {
        $vatByRate[$rateKey] = 0;
    }
    $vatByRate[$rateKey] += $itemVat;

    $itemTotal = ($itemNet) * (1 + $taxRate);

    // Werte zur Position hinzufügen
    $item['unit_price_formatted'] = formatCurrencyForContact($contact, $item['unit_price'], $document['currency']); 
    $item['net_amount'] = number_format($itemNet, 2, '.', '');
    $item['vat_amount'] = number_format($itemVat, 2, '.', '');


    $item['tax_rate_formatted'] = formatTaxRate($item['tax_rate'], $decimalSeparator); //thinspace
    $item['item_total_formatted'] =  formatCurrencyForContact($contact, $itemTotal, $document['currency']);
    $item['gross_amount'] = number_format($itemGross, 2, '.', '');
    $item['description'] = translateLanguage($contact['language'],  $item['description']);
    $item['detail'] = translateLanguage($contact['language'],  $item['detail']);
}

// Formatierung der Gesamtsummen
$totals = [
    'net' => formatCurrencyForContact($contact, $totalNet, $document['currency']),
    'vat' => formatCurrencyForContact($contact, $totalVat, $document['currency']),
    'vat_value' => $totalVat,
    'gross' => formatCurrencyForContact($contact, $totalGross, $document['currency']),
    'vat_by_rate' => [] // NEW
];

$db->update('sales_document', ['total_amount' => $totalGross], $document['id']);

// Format VAT per rate into same structure
foreach ($vatByRate as $rate => $vatAmount) {
    $totals['vat_by_rate'][] = [
        'rate' => formatTaxRate($rate, $decimalSeparator),
        'vat' => formatCurrencyForContact($contact, $vatAmount, $document['currency']),
        'vat_value' => $vatAmount
    ];
}


$document['status_translated'] = translateLanguage($contact['language'],  $document['status']);
$contact['salutation'] = translateLanguage($contact['language'],  $contact['salutation']);
$phpDateFormat = translateLanguage($contact['language'],  'php_date_format');
$document['date'] = date_format(date_create($document['date']), $phpDateFormat);
$document['due_date'] = date_format(date_create($document['due_date']), $phpDateFormat);
$document['delivery_date'] = date_format(date_create($document['delivery_date']), $phpDateFormat);
$document['title_translated'] = translateLanguage($contact['language'], $document['title'], $allVars);
$document['intro_text_formatted'] = formatText(translateLanguage($contact['language'], $document['intro_text'], $allVars));
$currency = $db->get('currency', ['currency_code'=>$document['currency']]);
$document['currency_symbol'] = $currency['currency_symbol'];

if (!empty($document['file']) && !file_exists('../../'.$document['file'])) {
    $document['file'] = '';
}

if (!empty($document['body_text'])) {
    $document['body_text_formatted'] = formatText(translateLanguage($contact['language'], $document['body_text'], $allVars));
}

$document['bottom_text_formatted'] = formatText(translateLanguage($contact['language'], $document['bottom_text'], $allVars));
$document['payment_terms_formatted'] = formatText(translateLanguage($contact['language'], $document['payment_terms'], $allVars));
if (!empty($document['print_layout_id'])) {
    $layout = $db->getById('print_layout',$document['print_layout_id']);
    $document['layout'] = $layout['name'];
}
$document['number_name'] = translateLanguage($contact['language'],  'sales_no_'.$document['document_type']); 
echo json_encode([
    'documentType' => $documentType,
    'document' => $document,
    'items' => $items,
    'contact' => $contact,
    'company' => $company,
    'totals' => $totals
]);

?>