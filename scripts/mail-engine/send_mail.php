<?php 
    namespace Metayota;
    include_once('../metayota/library.php');  
    
    $contact = $db->getById('contact',$data['contact_id']);

    $sender = new MailSender();
    $sender->addContact($contact['id']);
    $sender->setTemplate('account_created',$data);
    $sender->send();

    success(translate('account_created_and_mail_sent'));
?>