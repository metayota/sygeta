<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../format-text/format-text.php');   include_once('../invite-functions/invite-functions.php');   include_once('../translation-service/translation-service.php');   include_once('../alterworld-functions/alterworld-functions.php');   
die('unused');
    if (empty($data['personal_message'])) {
        msg('please_enter_personal_message');
    }
    $sql = "SELECT COUNT(*) as invite_count FROM invite_contact WHERE last_invite >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND user_id=:user_id";
    $inviteCountRow = $db->getByQuery($sql, ['user_id'=>$user_id]);
    $inviteCount = $inviteCountRow['invite_count'];
    $user = $db->getById('user',$user_id);
    if (empty($user['firstname']) || empty($user['lastname'])) {
        echo json_encode(['message' => translate('msg_name_required',$recommenededBuilding['name']), 'link' => '/settings', 'link_text'=>translate('profile_settings')]);
        die();
    }
    $personal_message = $data['personal_message'];
    // Remove HTML and PHP tags
    $personal_message = strip_tags($personal_message);

    // Regular expression pattern for URL
    $pattern = '/((https?:\/\/)?[\w-]+(\.[\w-]+)+\.?(:\d+)?(\/\S*)?)/i';

    // Check if input matches the pattern
    if (preg_match($pattern, $personal_message)) {
        msg('pm_input_not_allowed');
    } 

    $nowSent = 0;
    $contacts = $data['contacts'];




    
    $mail_version = ($data['mail_version'] * 1).'';
    foreach($contacts as $contact) {
        
        $contactRow = $db->get('contact',['user_id'=>$user_id,'id'=>$contact]);
        
        $oneMonthAgo = strtotime("-1 month");
        

        if (empty($contactRow['last_contact']) || strtotime($contactRow['last_invite']) < $oneMonthAgo) {
            $mailAddress = filter_var($contactRow['mail_address'], FILTER_VALIDATE_EMAIL);
            if ($mailAddress) {
                $subject = getInviteSubject($data['mail_version'],$user_id,$contactRow);
                $text = getInviteText($data['mail_version'],$user_id,$contactRow);
                $text = str_replace('__PERSONAL_MESSAGE__',$personal_message,$text);
                $now = date('Y-m-d H:i:s', time());
                $db->update('contact',['last_invite'=>$now],['id'=>$contactRow['id']]);
                
                $nowSent++;

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

                $fromName = $data['mail_version'] == 3 ? $user['firstname'].' '.$user['lastname'] : $user['firstname'];
                $parameters = array('From: '.$fromName.' <invitation@alterworld.de>', 'Reply-to: '.$mailConfig['reply_to_name'].' <'.$mailConfig['reply_to_email'].'>', 'Content-Type: text/plain; charset=utf-8');
                mail($mailAddress,$subject,$text,implode("\r\n", $parameters));
            }
          
        }
        if ($inviteCount++ > 300) {
            msg('msg_invitation_limit_reached',$nowSent.'');
        }
    }

    msg('msg_sent_invitation_mails',[],'success');
?>