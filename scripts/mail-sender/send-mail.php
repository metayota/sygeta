<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
//ob_start(); // Start output buffering to prevent header issues

require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function parseEmailAddress($email) {
    if (preg_match('/^(.*?)\s*<(.+?)>$/', $email, $matches)) {
        return [
            'name' => trim($matches[1]),
            'email' => trim($matches[2])
        ];
    }
    return [
        'name' => '',
        'email' => trim($email)
    ];
}

function processEmojiImages($message, $mailer) {
    // Pattern to match emoji img tags with relative paths
    $pattern = '/src="\.\.\/images\/emojis-webp\/([^"]+)"/';
    
    preg_match_all($pattern, $message, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $filename = $match[1];
        $filepath = '../../images/emojis-webp/' . $filename;
        
        if (file_exists($filepath)) {
            // Generate unique CID for this image
            $cid = 'emoji_' . pathinfo($filename, PATHINFO_FILENAME) . '_' . uniqid();
            
            // Add as inline attachment
            $mailer->addEmbeddedImage($filepath, $cid, $filename);
            
            // Replace the src attribute with CID reference
            $message = str_replace($match[0], 'src="cid:' . $cid . '"', $message);
        }
    }
    
    return $message;
}

    // Validate required inputs
    
    if (empty($data['subject']) || empty($data['recipients']) || empty($data['message'])) {
        throw new Exception('Missing required fields');
    }

    // Fetch account details
    $account = $db->get('email_account', ['user_id' => $user_id]);
    if (!$account) {
        throw new Exception('Email account not found.');
    }

    // Initialize PHPMailer for SMTP
    $mailer = new PHPMailer(true);

    $mailer->isSMTP();
    $mailer->Host = $account['smtp_host']; // Use smtp_host for SMTP
    $mailer->SMTPAuth = true;
    $mailer->Username = $account['imap_username']; // Using same username for SMTP
    $mailer->Password = $account['imap_password']; // Using same password for SMTP
    $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // SSL encryption for SMTP
    $mailer->Port = $account['smtp_port']; // Use smtp_port for SMTP, default to 465
    $mailer->CharSet = 'UTF-8';
    $mailer->Encoding = 'base64'; // Ensures special characters are properly encoded

    // Set sender details
    $mailer->setFrom($account['email'], $account['from_name']);

    // Set recipient details
    foreach($data['recipients'] as $recipient) {
        $recipientAddress = parseEmailAddress($recipient);
        $mailer->addAddress($recipient);
    }
    $mailer->addReplyTo($account['email'],$account['from_name']);

    // Fix Subject Encoding (Base64 Encoding)
    $mailer->Subject = '=?UTF-8?B?' . base64_encode($data['subject']) . '?=';

    // Process emoji images and embed them as inline attachments
    $processedMessage = processEmojiImages($data['message'], $mailer);

    // Email Body (Ensuring UTF-8 Encoding)
    $mailer->isHTML(true);
    $mailer->Body = $processedMessage;
    $mailer->AltBody = strip_tags($processedMessage); // Plain-text fallback

    // Add regular attachments, if any
    if (!empty($data['attachments'])) {
        foreach ($data['attachments'] as $attachment) {
            $filepath = str_starts_with($attachment, '/') ? '../../' . $attachment : '../../uploads/mail_attachments/' . $attachment;
            if (file_exists($filepath)) {
                $mailer->addAttachment($filepath, basename($attachment));
            }
        }
    }

    // Send the email
    if ($mailer->send()) {
        // IMAP Configuration
        $imapHost = sprintf('{%s:%d/imap/ssl}', $account['imap_host'], 993); // Use imap_host for IMAP
        $sentFolder = getConfig('sent_folder', 'Sent');
        $imapMailbox = $imapHost . $sentFolder;

        // Use the processed message for IMAP as well
        $emailHeaders = 
            "From: {$account['email']}\r\n" .
            "To: {$recipientAddress['email']}\r\n" .
            "Subject: " . '=?UTF-8?B?' . base64_encode($data['subject']) . "?=\r\n" .
            "Date: " . date('r') . "\r\n" .
            "MIME-Version: 1.0\r\n" .
            "Content-Type: text/html; charset=UTF-8\r\n" . // Ensure UTF-8 encoding
            "Content-Transfer-Encoding: base64\r\n\r\n" .
            base64_encode($processedMessage); // Use processed message with CIDs

        // Connect to IMAP server
        $imap = @imap_open($imapHost, $account['imap_username'], $account['imap_password']);
        if (!$imap) {
            throw new Exception('Failed to connect to IMAP: ' . imap_last_error());
        }

        // Check if the folder exists
        $mailboxes = imap_getmailboxes($imap, $imapHost, '*');
        $folderExists = false;
        if ($mailboxes) {
            foreach ($mailboxes as $mailbox) {
                if (strpos(imap_utf7_decode($mailbox->name), $sentFolder) !== false) {
                    $folderExists = true;
                    break;
                }
            }
        }

        // Create the folder if it doesn't exist
        if (!$folderExists) {
            if (!imap_createmailbox($imap, imap_utf7_encode($imapHost . $sentFolder))) {
                throw new Exception('Failed to create "Sent" folder: ' . imap_last_error());
            }
        }

        // Append the email to the "Sent" folder
        if (!imap_append($imap, $imapMailbox, $emailHeaders)) {
            throw new Exception('Failed to save email to Sent folder: ' . imap_last_error());
        }

        imap_close($imap);
    }

//ob_end_clean();
success('msg_email_sent_success');
?>