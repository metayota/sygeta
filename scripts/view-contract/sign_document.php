<?php 
    namespace Metayota;
    include_once('../metayota/library.php');   
    include_once('../contacts-lib/contacts-lib.php');
    include_once('../translation-service/translation-service.php');
    include_once('../mail-contact-sender/mail-contact-sender.php');

    $contract = $db->getById('contract', $data['contract_id']);
    $contractTemplate = $db->getById('contract_template', $contract['contract_template_id']);
    $contact = getContactById( $contract['contact_id'] );

    if (!empty($contact)) {
        $db->update('contract', ['signed'=>true,'signature_file'=>$data['filename'],'sign_date'=>date('Y-m-d H:i:s'),'sign_location'=>$contact['city'],'sign_ip'=>$_SERVER['REMOTE_ADDR']],['id'=>$contract['id']]);
    }
    success('msg_document_sign_success');


    while (ob_get_level()) {
        ob_end_flush();
    }
    flush();
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }

    callAction('view-contract','generate-pdf', ['contract_id'=>$contract['id']]);

    $contract = $db->getById('contract', $contract['id']); // Contract contains the filename now
    $allVars = [];

    $allVars['sign_date'] = formatDatetimeForContact($contract['sign_date'], $contact);
    $title = translateLanguage($contact['language'], $contractTemplate['title']);
    $allVars['title'] = $title;
    $allVars['signer_full_name'] = $contact['full_name'];

    $sender = new MailContactSender();
    $sender->addContact($contact['id']);
    $sender->setTemplate('contract_signed',$allVars);
    $sender->addAttachment('../../uploads/contracts/'.$contract['contract_file'], $title.'.pdf');
    $sender->send();    

    if (!empty($contact['partner_id'])) {
        $sender = new MailContactSender();
        $partnerContact = getPartnerContact($contact);
        $sender->addContact($partnerContact['id']);
        $sender->setTemplate('contract_signed_partner',$allVars);
        $sender->addAttachment('../../uploads/contracts/'.$contract['contract_file'], $title.'.pdf');
        $sender->send();    
    }
    
?>