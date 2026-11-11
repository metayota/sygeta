<?php
    namespace Metayota;
    include_once('../metayota/library.php');   
    include_once('../contacts-lib/contacts-lib.php');
    include_once('../translation-service/translation-service.php');

    $contract = $db->getById('contract', $data['contract_id']);
    $contact = getContactById( $contract['contact_id'] );

    if (!empty($contact)) {
        $companyData = $db->get('company_settings');
        $contractTemplate = $db->getById('contract_template', $contract['contract_template_id']);

        $allVars = [];

        $contractDate = empty($contract['sign_date']) ? 'now' : $contract['sign_date'];
        $contactTimezone = getTimezoneForContact($contact['id']);
        $signDate = new \DateTime($contractDate, new \DateTimeZone($contactTimezone));
        $phpDateFormat = translateLanguage($contact['language'], 'php_date_format');
        $formattedDate = $signDate->format($phpDateFormat);
        $allVars['contract_date'] = $formattedDate;

        $allVars['contract_location'] = $contact['city'];

        foreach($companyData as $key => $value ){
            $allVars['company_'.$key] = $value;
        }
        foreach($contact as $key => $value) {
            $allVars['contact_'.$key] = $value;
        }
        
        if (!empty($contract['signature_file'])) {
            $allVars['contact_signature'] = '<img class="signature" src="/uploads/signatures/'.$contract['signature_file'].'"/>';
        } else {
            $allVars['contact_signature'] =  translateLanguage($contact['language'], 'your_signature');
        }

        if (!empty($companyData['company_signature'])) {
            $allVars['company_signature'] = '<img class="signature" src="/uploads/signatures/'.$companyData['company_signature'].'"/>';
        }

        if (!empty($contract['content'])) {
            $contentTranslated = $contract['content'];
        } else {
            $contentTranslated = translateLanguage($contact['language'], $contractTemplate['content'], $allVars);
        }

        $contract['content_translated'] = $contentTranslated;
        
        echo json_encode($contract);
    } else {
        errorMsg('You have no access to this contact');
    }
?>