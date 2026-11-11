<?php 
    namespace Metayota;
    include_once('../metayota/library.php');
    include_once('../mail-contact-sender/mail-contact-sender.php');
    include_once('../access-check/access-check.php');
    
    checkWritePermissions('contact', $data['contact_id']);

    $contact = $db->getById('contact',$data['contact_id']);

    $template = isset($data['template']) ? $data['template'] : $data['mail_template_name'];

    $sender = new MailContactSender();
    $sender->addContact($contact['id']);
    $sender->setTemplate($template,$data);
    $sender->send();

    success('mail_sent_to_customer');
?>