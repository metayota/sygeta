<?php namespace Metayota; include_once('../metayota/library.php');  

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendMail($receiver,$subject,$text,$type='all_mail') {
    require '../../vendor/autoload.php';  // Update this path if needed
    global $db;

    if (getConfig('mailer_unsubscribe')) {
        $unsubscribed = $db->get('unsubscribed_mail',['mail'=>$receiver,'mail_type'=>$type]);
        if (!empty($unsubscribed)) {
            return;
        }
    }

    $mail = new PHPMailer(true);

    global $user_id;
    if (empty($user_id)) {
        $sql = "SELECT * FROM `email_account` WHERE `user_id` IS NULL";
        $mailConfig = $db->getByQuery($sql);
    } else {
        $sql = "SELECT * FROM `email_account` WHERE `user_id` = :user_id OR `user_id` IS NULL ORDER BY `user_id` DESC LIMIT 1;";
        $mailConfig = $db->getByQuery($sql, ['user_id'=>$user_id]);
    }

    try {
        //Server settings
     //   $mail->SMTPDebug = 2;                      // Enable verbose debug output .
        $mail->isSMTP();                           // Send using SMTP
        $mail->Host       = $mailConfig['smtp_host'];       // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                  // Enable SMTP authentication
        $mail->Username   = $mailConfig['imap_username'];      // SMTP username from config
        $mail->Password   = $mailConfig['imap_password'];  // SMTP password from config
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Enable SSL encryption
        $mail->Port       = $mailConfig['smtp_port'];                   // TCP port to connect to
        $mail->CharSet    = 'UTF-8';  

        //Recipients
        $mail->setFrom($mailConfig['email'], $mailConfig['from_name']);
        $mail->addAddress($receiver); // Add a recipient
        $mail->addReplyTo($mailConfig['reply_to_email'], $mailConfig['reply_to_name']);

        if (getConfig('mailer_unsubscribe')) {
            $hash = md5($receiver.'_unsubscribe_secret_key_2ECMENQ9340');
            $http_host = $_SERVER['HTTP_HOST'];
            if (getConfig('wordpress')) {
                $emailUnsubscribeLink = 'https://'.$http_host.'/wp-content/plugins/metayota/scripts/unsubscribe/unsubscribe.php?email=' . urlencode($receiver) . '&hash='.$hash.'&type='.$type;
            } else {
                $emailUnsubscribeLink = 'https://'.$http_host.'/call/unsubscribe?email=' . urlencode($receiver) . '&hash='.$hash.'&type='.$type;
            }
            $mail->addCustomHeader('List-Unsubscribe', '<' . $emailUnsubscribeLink . '>');
        }

        // Content
        $mail->isHTML(false); // Set email format to TEXT
        $mail->Subject = $subject;
        $mail->Body    = $text;

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

?>