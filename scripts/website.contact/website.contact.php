<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../send-mail/send-mail.php');   include_once('../translation-service/translation-service.php');  
    sendMail(getConfig('admin_mail'),$data['title'],"This is the original message received on the contact form from ".$data['name'].", ".$data['email']."\n\n---\n\n".$data['description']);
    $db->insert('contact_form',['form_data'=>json_encode($data)]);
    success();
?>