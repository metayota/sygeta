<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../php-mailer/php-mailer.php');   include_once('../translation-service/translation-service.php');  
$user = $db->get('user',array('email'=>$data['email']));
if (!empty($user)) {
    $token = substr(bin2hex(random_bytes(64)),0,64);
    $db->update('user',array('forgot_token'=>$token),array('id'=>$user['id']));
    $subject = translate('mail_pw_forgot_subject');
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $language = $GLOBALS['language'];
    $link = "$protocol://$host/$language/reset-password/$token";
    $contact = $db->getById('contact',$user['contact_id']);
    $text = translate('mail_pw_forgot_text', ['firstname'=>$contact['firstname'], 'lastname'=>$contact['lastname'],'link'=>$link]);
    sendMail($user['email'],$subject, $text);
    msg('check_mail_pw_forgot_link',[],'success');
} else {
    errorMsg('user_not_found');
}
?>