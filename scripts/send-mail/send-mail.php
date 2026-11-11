<?php namespace Metayota; include_once('../metayota/library.php');  
function sendMail($receiver,$subject,$text,$type='all_mail') {
    global $db;
    if (getConfig('mailer_unsubscribe')) {
        $unsubscribed = $db->get('unsubscribed_mail',['mail'=>$receiver,'mail_type'=>$type]);
        if (!empty($unsubscribed)) {
            return;
        }
    }


    global $user_id;
    if (empty($user_id)) {
        $sql = "SELECT * FROM `email_account` WHERE `user_id` IS NULL";
        $mailConfig = $db->getByQuery($sql);
    } else {
        $sql = "SELECT * FROM `email_account` WHERE `user_id` = :user_id OR `user_id` IS NULL ORDER BY `user_id` DESC LIMIT 1;";
        $mailConfig = $db->getByQuery($sql, ['user_id'=>$user_id]);
    }
    ini_set('auth_user', $mailConfig['imap_username']);
    ini_set('auth_password', $mailConfig['imap_password']);
    $hash = md5($receiver.'_unsubscribe_secret_key_2ECMENQ9340');
    $http_host = $_SERVER['HTTP_HOST'];
    $emailUnsubscribeLink = 'https://'.$http_host.'/call/unsubscribe?email=' . urlencode($receiver) . '&hash='.$hash.'&type='.$type;
    $parameters = array(
        'From: '.$mailConfig['from_name'].' <'.$mailConfig['email'].'>', 
        'Reply-to: '.$mailConfig['reply_to_name'].' <'.$mailConfig['reply_to_email'].'>', 
        'Content-Type: text/plain; charset=utf-8',
        'List-Unsubscribe: <'.$emailUnsubscribeLink.'>'
    );
    mail($receiver,$subject,$text,implode("\r\n", $parameters));
}
?>