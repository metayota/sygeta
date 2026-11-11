<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../send-mail/send-mail.php');   include_once('../translation-service/translation-service.php');  
    $mail = $data['mail'];
    $user = $db->get('user',array('email'=>$mail));
    $code = $user['verification_code'];
    $link = 'https://'.$_SERVER['SERVER_NAME'].'/'.$GLOBALS['language'].'/call/verify-mail/'.$code;
    $firstname = $user['firstname'];
    $lastname = $user['lastname'];
    if (!empty($code)) {
        $subject = translate('mail_verification_subject');
        $text = translate('mail_verification_text', ['firstname'=>$firstname,'lastname'=>$lastname,'link'=>$link]);
        sendMail($user['email'], $subject, $text);  
    } 
    msg('verification_mail_sent_again',[],'success');

?>