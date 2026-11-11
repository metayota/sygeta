<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../php-mailer/php-mailer.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-contact-sender/mail-contact-sender.php');  

    $appInvitation = $db->get('app_invitation', ['invitation_key'=>$data['invitation_key']]);
    $contact = $db->getByQuery('SELECT c.email as mail, c.phone, c.company, c.salutation, c.firstname, c.lastname, c.language FROM contact c WHERE id=:contact_id', ['contact_id'=>$appInvitation['contact_id']]);
    
    if (!empty($appInvitation['company_mail'])) {
        $contact['mail'] = $appInvitation['company_mail'];
    }
    echo json_encode(['mailReadonly'=>!empty($appInvitation['company_mail']), 'contact'=>$contact]);
?>