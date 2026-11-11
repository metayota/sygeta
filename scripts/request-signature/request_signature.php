<?php 
    namespace Metayota;
    include_once('../metayota/library.php');   
    include_once('../access-check/access-check.php');
    include_once('../mail-contact-sender/mail-contact-sender.php');

    $contact = $db->getById('contact', $data['contact_id']);

    checkWritePermissions('contact', $contact['id']);

    $contractTemplate = $db->getById('contract_template', $data['contract_template_id']);
    $appURL = $db->getColumn('app_settings', 'app_url');
    $allVars = [];
    $allVars['title'] = translateLanguage($contact['language'], $contractTemplate['title']);
    $allVars['description'] = translateLanguage($contact['language'], $contractTemplate['description']);
    $db->insert('contract', ['contact_id'=>$contact['id'], 'contract_template_id'=>$contractTemplate['id']]);

    $contractID = $db->lastInsertId();
    $loginAndRedirect = $appURL.'call/login/login?access_key='.$contact['access_key'].'&redirect='.urlencode('/view-contract/'.$contractID);
    $allVars['contract_link'] = $loginAndRedirect;

    $sender = new MailContactSender();
    $sender->addContact($contact['id']);
    //$allVars = 
    $sender->setTemplate('cover_mail_contract', $allVars);
    $sender->send();

    success('msg_signature_requested');
?>