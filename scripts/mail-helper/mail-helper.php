<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-contact-sender/mail-contact-sender.php');   include_once('../translation-service/translation-service.php');   
    function sendMailHelper($contact_id, $mailTemplateName, $variables=[]) {
        global $user_id, $db;
        $contact = $db->getById('contact',$contact_id);
        
        $senderUser = $db->getById('user',$user_id);
        $allVariables = $variables;
        if (!empty($senderUser)) {
            $senderContact = $db->getById('contact',$senderUser['contact_id']);
            
            $allVariables['sender_firstname'] = $senderContact['firstname'];
            $allVariables['sender_lastname'] = $senderContact['lastname'];
        }

        $allVariables['firstname'] = $contact['firstname'];
        $allVariables['lastname'] = $contact['lastname'];

        $language = empty($contact['language']) ? $contact['language'] : 'de';
        $mailTemplate = $db->getByName('mail_template',$mailTemplateName);

        $mailMessage = [];
        $mailMessage['receiver_contact_id'] = $contact_id;
        $mailMessage['receiver_mail'] = $contact['email'];
        $mailMessage['subject'] = translateLanguage($language, $mailTemplate['subject'], $allVariables);
        $mailMessage['message'] = translateLanguage($language, $mailTemplate['message'], $allVariables);
        $db->insert('mail_message',$mailMessage);

        $sender = new MailContactSender();
        $sender->addContact($contact['id']);
        $sender->setTemplate($mailTemplateName,$allVariables);
        $sender->send();
        
    }

?>