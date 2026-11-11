<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../format-text/format-text.php');   include_once('../slugify/slugify.php');   include_once('../translation-service/translation-service.php');  
// *** NEW: ZUGFeRD data preparation function ***

function formatTaxRate($taxRate, $decimalSeparator) {
    return rtrim(
        rtrim(number_format($taxRate, 10, $decimalSeparator, ''), '0'),
        $decimalSeparator
    ).' %';
}

function prepareZugferdData($document, $contact, $db) {
    global $db;
    // Get company settings
    $company = $db->get('company_settings');
    
    // Get all items
    $items = $db->getAllByQuery('SELECT * FROM sales_document_item WHERE document_id=:document_id ORDER BY position', ['document_id' => $document['id']]);
    
    // Calculate totals (reuse logic from load_document.php)
    $totalNet = 0;
    $totalVat = 0;
    $totalGross = 0;
    
    $processedItems = [];
    foreach ($items as $item) {
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
        
        $totalNet += $itemNet;
        $totalVat += $itemVat;
        $totalGross += $itemGross;
        
        $processedItems[] = [
            'description' => translateLanguage($contact['language'], $item['description']),
            'quantity' => $item['quantity'],
            'unit_price' => $unitPrice,
            'tax_rate' => $item['tax_rate'],
            'net_amount' => $itemNet,
            'vat_amount' => $itemVat,
            'gross_amount' => $itemGross
        ];
    }
    
    return [
        'document' => [
            'number' => $document['document_number'],
            'date' => $document['date'],
            'due_date' => $document['due_date'],
            'delivery_date' => $document['delivery_date'], // ADD THIS LINE
            'payment_terms' => translateLanguage($contact['language'],$document['payment_terms']), // ADD THIS LINE
            'currency' => $document['currency'],
            'type' => $document['document_type']
        ],
        'seller' => [
            'name' => $company['name'],
            'street' => $company['street'],
            'postal_code' => $company['postal_code'],
            'city' => $company['city'],
            'country' => $company['country'] ?? 'US', // Changed to US
            'vat_number' => $company['vat_number'],
            'tax_number' => $company['tax_number'] ?? $company['vat_number'],
            'hr_number' => $company['hr_number'] // ADD THIS LINE
        ],
        'buyer' => [
            'name' => $contact['company'] ?? ($contact['firstname'] . ' ' . $contact['lastname']),
            'street' => $contact['street'],
            'postal_code' => $contact['zip_code'],
            'city' => $contact['city'],
            'country' => $contact['country'] ?? 'DE',
            'language' => $contact['language']
        ],
        'items' => $processedItems,
        'totals' => [
            'net' => $totalNet,
            'vat' => $totalVat,
            'gross' => $totalGross
        ]
    ];
}

?>