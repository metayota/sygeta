<?php namespace Metayota; include_once('../metayota/library.php');  
require_once '../../vendor/autoload.php';

use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdDocumentPdfBuilder;
use horstoeko\zugferd\ZugferdProfiles;

function createZugferdPdf($pdfContent, $zugferdData) {
    
    try {
        $document = ZugferdDocumentBuilder::CreateNew(ZugferdProfiles::PROFILE_EN16931);
        
        $document->setDocumentInformation(
            $zugferdData['document']['number'],
            "380",
            \DateTime::createFromFormat('Y-m-d', $zugferdData['document']['date']),
            $zugferdData['document']['currency']
        );
        
        // Set delivery date if available (use delivery_date, not due_date)
        if (!empty($zugferdData['document']['delivery_date'])) {
            $document->setDocumentSupplyChainEvent(\DateTime::createFromFormat('Y-m-d', $zugferdData['document']['delivery_date']));
        }
        
        $document->setDocumentSeller(
            $zugferdData['seller']['name'],
            $zugferdData['seller']['tax_number'] ?? ''
        )->setDocumentSellerAddress(
            $zugferdData['seller']['street'],
            '', '', 
            $zugferdData['seller']['postal_code'],
            $zugferdData['seller']['city'],
            $zugferdData['seller']['country']
        );
        
        
        // ADD THIS NEW SECTION:
        // Add Wyoming State Registration Number as Seller Identifier (BT-29)
        if (!empty($zugferdData['seller']['hr_number'])) {
            $document->addDocumentSellerGlobalId($zugferdData['seller']['hr_number'], "0088");
        }

        // Add it as tax registration identifier (BT-32) 
        if (!empty($zugferdData['seller']['hr_number'])) {
            $document->addDocumentSellerTaxRegistration("FC", $zugferdData['seller']['hr_number']);
        }
        // ADD VAT REGISTRATION
        if (!empty($zugferdData['seller']['vat_number'])) {
            $document->addDocumentSellerTaxRegistration("VA", $zugferdData['seller']['vat_number']);
        }
        
        $document->setDocumentBuyer($zugferdData['buyer']['name'])
                 ->setDocumentBuyerAddress(
                     $zugferdData['buyer']['street'],
                     '', '', 
                     $zugferdData['buyer']['postal_code'],
                     $zugferdData['buyer']['city'],
                     $zugferdData['buyer']['country']
                 );
        
        // ADD PAYMENT TERMS
        // Combine payment terms and due date in a single payment term
        $paymentTermsText = '';
        if (!empty($zugferdData['document']['payment_terms'])) {
            $paymentTermsText = $zugferdData['document']['payment_terms'];
        }
        if (!empty($zugferdData['document']['due_date'])) {
            if (!empty($paymentTermsText)) {
                $paymentTermsText .= ' / ';
            }
            $phpDateFormat = translateLanguage($zugferdData['buyer']['language'],  'php_date_format');
            $dueDateFormattted = date_format(date_create($zugferdData['document']['due_date']), $phpDateFormat);
            $paymentTermsText .= translateLanguage($zugferdData['buyer']['language'],  'due_date').': ' . $dueDateFormattted;
        }
        if (!empty($paymentTermsText)) {
            $document->addDocumentPaymentTerm($paymentTermsText);
        }
        
        // Add items with CORRECT calculations
        $position = 1;
        foreach ($zugferdData['items'] as $item) {
            if ($item['net_amount'] > 0) {
                $document->addNewPosition((string)$position)
                         ->setDocumentPositionNote($item['description'])
                         ->setDocumentPositionProductDetails($item['description'])
                         ->setDocumentPositionNetPrice($item['unit_price'])
                         ->setDocumentPositionQuantity($item['quantity'], "C62")
                         ->addDocumentPositionTax('S', 'VAT', $item['tax_rate'])
                         ->setDocumentPositionLineSummation($item['net_amount']); // NET AMOUNT!
                $position++;
            }
        }
        
        // Group taxes by rate for accurate calculation
        $taxGroups = [];
        foreach ($zugferdData['items'] as $item) {
            $rate = $item['tax_rate'];
            if (!isset($taxGroups[$rate])) {
                $taxGroups[$rate] = ['net' => 0, 'vat' => 0];
            }
            $taxGroups[$rate]['net'] += $item['net_amount'];
            $taxGroups[$rate]['vat'] += $item['vat_amount'];
        }
        
        foreach ($taxGroups as $rate => $amounts) {
            $document->addDocumentTax("S", "VAT", $amounts['net'], $amounts['vat'], $rate);
        }
        
        $document->setDocumentSummation(
            $zugferdData['totals']['gross'], // grand total
            $zugferdData['totals']['gross'], // due payable amount  
            $zugferdData['totals']['net'],   // line total amount (sum of net amounts)
            0.00, // charge total amount
            0.00, // allowance total amount
            $zugferdData['totals']['net'],   // tax basis total amount
            $zugferdData['totals']['vat'],   // tax total amount
            0.00, 0.00
        );
        
        $pdfBuilder = new ZugferdDocumentPdfBuilder($document, $pdfContent);
        $pdfBuilder->setAttachmentRelationshipTypeToAlternative();
        $pdfBuilder->generateDocument();
        
        return $pdfBuilder->downloadString('zugferd-invoice.pdf');
        
    } catch (Exception $e) {
        error_log("ZUGFeRD Error: " . $e->getMessage());
        return $pdfContent;
    }
}
?>