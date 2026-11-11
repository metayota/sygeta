<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../format-text/format-text.php');   include_once('../slugify/slugify.php');   include_once('../translation-service/translation-service.php');  
include_once('../resource-parameter-formatter/resource-parameter-formatter.php');
include_once('../contacts-lib/contacts-lib.php');
include_once('../sales-document-detail-view/lib.php');
include_once('../mail-contact-sender/mail-contact-sender.php');

include_once('../access-check/access-check.php');
checkWritePermissions('sales_document', $data['sales_document_id']);

$document = $db->getById('sales_document', $data['sales_document_id']);
$documentType = $db->getByName('sales_document_type', $document['document_type']);

if (!empty($documentType['cover_mail'])) {
    $mailTemplate = $db->getByName('mail_template', $documentType['cover_mail']);

    if (!empty($mailTemplate)) {
        $db->update('sales_document', ['status'=>'sent'], $document['id']);

        // Alle Daten laden (wie in Ihrem anderen Code)
        $items = $db->getAllByQuery('SELECT * FROM sales_document_item WHERE document_id=:document_id ORDER BY position', ['document_id' => $document['id']]);
        $contact = $db->getById('contact', $document['contact_id']);
        $company = $db->get('company_settings');
        
        // Template-Variablen vorbereiten (inspiriert von Ihrem Code)
        $allVars = [];


        if (!empty($documentType['custom_form']) && !empty($document['custom_form_data'])) {
            $allVars = formatResourceValues($documentType['custom_form'], json_decode($document['custom_form_data'],true), $contact['language']);
        }

        // Beträge berechnen (vereinfacht aus Ihrem Code)
        $totalNet = 0;
        $totalVat = 0;
        $totalGross = 0;
        
        foreach ($items as $item) {
            $taxRate = $item['tax_rate'] / 100;
            if (!empty($item['unit_price'])) {
                $itemNet = $item['quantity'] * $item['unit_price'];
                $itemVat = $itemNet * $taxRate;
                $itemGross = $itemNet + $itemVat;
            } elseif (!empty($item['total'])) {
                $itemGross = $item['total'];
                $itemNet = $itemGross / (1 + $taxRate);
                $itemVat = $itemGross - $itemNet;
            }
            
            $totalNet += $itemNet;
            $totalVat += $itemVat;
            $totalGross += $itemGross;
        }
        
        // Dokumentdaten formatieren und übersetzen
        $phpDateFormat = translateLanguage($contact['language'], 'php_date_format');
        $currency = $db->get('currency', ['currency_code'=>$document['currency']]);
        
        $allVars['document_number'] = $document['document_number'];
        $allVars['document_title'] = translateLanguage($contact['language'], $document['title'], $allVars);
        $allVars['document_date'] = date_format(date_create($document['date']), $phpDateFormat);
        $allVars['document_due_date'] = date_format(date_create($document['due_date']), $phpDateFormat);
        $allVars['document_delivery_date'] = date_format(date_create($document['delivery_date']), $phpDateFormat);
        $allVars['document_type_name'] = translateLanguage($contact['language'], 'sales_document_type_'.$document['document_type']);
        
        // Beträge formatiert
        $allVars['total_net'] = formatCurrencyForContact($contact, $totalNet, $document['currency']);
        $allVars['total_vat'] = formatCurrencyForContact($contact, $totalVat, $document['currency']);
        $allVars['total_gross'] = formatCurrencyForContact($contact, $totalGross, $document['currency']);
        $allVars['currency_symbol'] = $currency['currency_symbol'];

        $sender = new MailContactSender();
        $sender->addContact($contact['id']);
        $sender->addAttachment('../..'.$document['file'], $document['document_number'].'.pdf');
        $sender->setTemplate($mailTemplate['name'],$allVars);
        $sender->send();

        success('msg_cover_mail_sent_success');
    }

}
?>