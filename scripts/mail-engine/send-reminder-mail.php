<?php 
    namespace Metayota;
    include_once('../metayota/library.php');
    include_once('../mail-contact-sender/mail-contact-sender.php');
    include_once('../access-check/access-check.php');
    
    checkWritePermissions('contact', $data['contact_id']);

    $contact = $db->getById('contact',$data['contact_id']);

    $template = isset($data['template']) ? $data['template'] : $data['mail_template_name'];
    $sql = "SELECT * FROM mail_message WHERE receiver_contact_id=:contact_id AND mail_template_name=:mail_template_name ORDER BY id DESC";
    $originalMessage = $db->getByQuery($sql, ['contact_id'=>$contact['id'], 'mail_template_name'=>$data['original_mail_template']]);
    if (!empty($originalMessage)) {
        
        $sender = new MailContactSender();
        $sender->addContact($contact['id']);
        if (!empty($originalMessage['template_variables'])) {
            $originalVars = json_decode($originalMessage['template_variables'], true);
            $data = array_merge($data, $originalVars);
        }
        $sender->setTemplate($template,$data);
        $sender->send();

        success('mail_sent_to_customer');
    }
?>