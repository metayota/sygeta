<?php namespace Metayota;
include_once('../metayota/library.php');
include_once('../gemini-api/gemini-api.php');
include_once('../php-mailer/php-mailer.php');
include_once('../translation-service/translation-service.php');
include('../email-client/lib.php');

try {
    // Validate input parameters
    if (!isset($data['account_id']) || !isset($data['message_id']) || !isset($data['folder'])) {
        throw new \Exception('Missing required parameters');
    }
    
    // Get email account details
    $account = $db->getById('email_account', $data['account_id']);
    if (!$account || $account['user_id'] != $user_id) {
        throw new \Exception('Invalid email account access');
    }
    
    // Helper function for decoding email body parts
    function decodeEmailPart($message, $encoding) {
        switch($encoding) {
            case 0: return $message; // 7BIT
            case 1: return $message; // 8BIT
            case 2: return $message; // BINARY
            case 3: return base64_decode($message); // BASE64
            case 4: return quoted_printable_decode($message); // QUOTED-PRINTABLE
            default: return $message;
        }
    }

    // NEW: Recursive function to process all parts
    function processParts($imap, $message_number, $part, $partNumber = '', &$body, &$attachments) {
        // Build the part number string (e.g., "1", "1.1", "1.2")
        $partNum = $partNumber;
        
        // If this part has subparts, recursively process them
        if (isset($part->parts) && count($part->parts)) {
            foreach ($part->parts as $index => $subpart) {
                $subPartNum = $partNum ? $partNum . '.' . ($index + 1) : ($index + 1);
                processParts($imap, $message_number, $subpart, $subPartNum, $body, $attachments);
            }
            return;
        }
        
        // Fetch the message body for this part
        $message = imap_fetchbody($imap, $message_number, $partNum);
        $message = decodeEmailPart($message, $part->encoding);
        
        // Handle character encoding
        if (isset($part->parameters)) {
            foreach ($part->parameters as $param) {
                if (strtolower($param->attribute) == 'charset') {
                    $message = mb_convert_encoding($message, 'UTF-8', $param->value);
                    break;
                }
            }
        }
        
        // Process text content (type 0 = text)
        if ($part->type == 0) {
            if (strtolower($part->subtype) == 'plain') {
                $body['plain'] .= $message;
            } elseif (strtolower($part->subtype) == 'html') {
                $body['html'] .= $message;
            }
        }
        
        // Process attachments
        if (($part->ifdisposition && 
             (strtolower($part->disposition) == 'attachment' || strtolower($part->disposition) == 'inline')) ||
            $part->type == 5 || $part->type == 3) {
            
            $filename = '';
            
            if (isset($part->dparameters)) {
                foreach ($part->dparameters as $dparam) {
                    if (strtolower($dparam->attribute) == 'filename') {
                        $filename = decodeEmailSubject($dparam->value);
                        break;
                    }
                }
            }
            
            if (!$filename && isset($part->parameters)) {
                foreach ($part->parameters as $param) {
                    if (strtolower($param->attribute) == 'name') {
                        $filename = decodeEmailSubject($param->value);
                        break;
                    }
                }
            }
            
            if (!$filename && $part->type == 3) {
                $ext = strtolower($part->subtype);
                $filename = 'inline_image_' . $partNum . '.' . $ext;
            }
            
            $content_id = '';
            if (isset($part->id)) {
                $content_id = trim($part->id, '<>');
            }
            
            if ($filename || $content_id) {
                $attachments[] = [
                    'filename' => $filename,
                    'part_number' => $partNum,
                    'type' => $part->type,
                    'subtype' => $part->subtype,
                    'disposition' => $part->ifdisposition ? strtolower($part->disposition) : null,
                    'content_id' => $content_id
                ];
            }
        }
    }

    // Set up IMAP connection
    $opts = [
        'DISABLE_AUTHENTICATOR' => 'GSSAPI',
        'CHARSET' => 'UTF-8'
    ];
    
    $mailbox = sprintf(
        '{%s:%d/imap/ssl/novalidate-cert}%s',
        $account['imap_host'],
        $account['imap_port'],
        $data['folder']
    );

    // Connect to IMAP server
    $imap = @imap_open($mailbox, $account['imap_username'], $account['imap_password'], 0, 1, $opts);
    if (!$imap) {
        throw new \Exception('Failed to connect to IMAP server: ' . imap_last_error());
    }

    $message_number = $data['message_num'];
    $email_structure = imap_fetchstructure($imap, $message_number);
    $headers = imap_headerinfo($imap, $message_number);
    
    $body = [
        'html' => '',
        'plain' => ''
    ];
    $attachments = [];
    
    // Process email structure recursively
    if (isset($email_structure->parts) && count($email_structure->parts)) {
        foreach($email_structure->parts as $index => $part) {
            processParts($imap, $message_number, $part, ($index + 1), $body, $attachments);
        }
    } else {
        // Handle simple emails without parts
        $message = imap_body($imap, $message_number);
        $message = decodeEmailPart($message, $email_structure->encoding);
        
        if (isset($email_structure->subtype) && strtolower($email_structure->subtype) == 'html') {
            $body['html'] = $message;
        } else {
            $body['plain'] = $message;
        }
    }

    if (empty($body['html']) && empty($body['plain'])) {
        $body['plain'] = imap_body($imap, $message_number);
    }

    // Format the sender's name/email
    $fromString = isset($headers->from[0]->personal)
        ? decodeEmailSubject($headers->from[0]->personal)
        : $headers->from[0]->mailbox . '@' . $headers->from[0]->host;

    // Helper functions for quoted text
    function createQuotedPlainText($text, $from, $date) {
        return sprintf("\nOn %s, %s wrote:\n%s", 
            $date, 
            $from, 
            preg_replace('/^/m', '> ', $text)
        );
    }

    function createQuotedHTML($html, $from, $date) {
        return sprintf(
            '<br>On %s, %s wrote:<br><blockquote style="margin:0 0 0 .8ex;border-left:1px #ccc solid;padding-left:1ex;">%s</blockquote>',
            $date,
            $from,
            $html
        );
    }

    // Mark email as read
    imap_setflag_full($imap, $message_number, "\\Seen");

    // Prepare response
    $response = [
        'subject' => isset($headers->subject) ? decodeEmailSubject($headers->subject) : '(No Subject)',
        'from' => [
            'email' => $headers->from[0]->mailbox . '@' . $headers->from[0]->host,
            'name' => isset($headers->from[0]->personal) 
                ? decodeEmailSubject($headers->from[0]->personal) 
                : null
        ],
        'to' => $headers->to[0]->mailbox . '@' . $headers->to[0]->host,
        'date' => date('Y-m-d H:i:s', strtotime($headers->date)),
        'body' => $body,
        'content' => [
            'html' => !empty($body['html']) ? $body['html'] : nl2br($body['plain']),
            'plain' => $body['plain'],
            'quoted_html' => createQuotedHTML(
                !empty($body['html']) ? $body['html'] : nl2br($body['plain']),
                $fromString,
                date('D, M j, Y \a\t g:i A', strtotime($headers->date))
            ),
            'quoted_plain' => createQuotedPlainText(
                $body['plain'],
                $fromString,
                date('D, M j, Y \a\t g:i A', strtotime($headers->date))
            )
        ],
        'attachments' => $attachments,
        'message_id' => $data['message_id']
    ];

    imap_close($imap);

    echo json_encode([
        'status' => 'success',
        'data' => $response
    ]);

} catch (\Exception $e) {
    if (isset($imap) && is_resource($imap)) {
        imap_close($imap);
    }
    
    error_log('Email processing error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>