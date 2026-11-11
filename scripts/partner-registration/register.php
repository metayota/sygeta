<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../php-mailer/php-mailer.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-contact-sender/mail-contact-sender.php');  
    include('../create-contact/lib.php');
    include('../mail-manager/lib.php');


    $appInvitation = $db->get('app_invitation', ['invitation_key'=>$data['invitation_key']]);

    if (empty($appInvitation)) {
        echo json_encode(['message'=>translate('invitation_key_not_valid_or_used')]);
        die();
    }
    
    // TODO; set from invitation usergroup id and check the verification code


    $pattern = '/^[a-zA-Z_\.\-öäüÖÜÄ0-9]+$/u'; // Regular expression pattern
    if (!preg_match($pattern, $data['username'])) {
        msg('msg_invalid_username'); 
    }


/*    $user = $db->get('user',['email'=>$data['mail']]);
    if (!empty($user)) {
        msg('mail_already_exists');
    }
    allow dup mail
    */

    $user = $db->get('user',['username'=>$data['username']]);
    if (!empty($user)) {
        msg('username_already_exists');
    }

    $hash = password_hash($data['password'],PASSWORD_DEFAULT); 
    $firstname = $data['firstname'];
    $lastname = $data['lastname'];
    $verification = rand ( 1000 , 9999 ).'-'.rand ( 1000 , 9999 ).'-'.rand ( 1000 , 9999 ).'-'.rand ( 1000 , 9999 ).'-'.rand ( 1000 , 9999 );
    $appSettings = $db->get('app_settings');
    $appURL = $appSettings['app_url'];
    $link = $appURL.'call/verify-mail?c='.$verification;
    $fullName = $data['firstname'].' '.$data['lastname'];
    
    $contactTypeID = $db->getColumnByName('contact_type', 'id', 'partner');

    $newContact = [
        'firstname' => $data['firstname'],
        'lastname' => $data['lastname'],
        'full_name' => $fullName,
        'company' => $data['company'],
        'salutation' => $data['salutation'],
        'language' => $data['language'],
        'phone' => $data['phone'],
        'email' => $data['mail'],
        'timezone' => $data['timezone'],
        'contact_type_id' => $contactTypeID
    ];

    $contactID = createContact($newContact);

    $partnerUsergroup = $db->getByName('usergroup','partner');

    // Insert the new user into the database
    $result = $db->insert('user', [
        'username' => $data['username'],
        'verification_code'=>$verification,
        'usergroup_id' => $partnerUsergroup['id'],
        'contact_id' => $contactID,
        'email' => $data['mail'],
        'status' => 'active',
        'password_hash' => $hash,
        'timezone' => $data['timezone']
    ]);

    $partnerUserID = $db->connection->lastInsertId();
    $db->update('contact',['user_id'=>$partnerUserID],['id'=>$contactID]);
    $db->insert('partner',['user_id'=>$partnerUserID,'full_name'=>$fullName,'commission'=>$data['commission'],'contact_id'=>$contactID]);

    session_start();
    $user = $db->get('user',['id'=>$partnerUserID]);
    $_SESSION['loggedInUser'] = $user;
    $user_id = $partnerUserID;
    session_write_close();


    if (!empty($appInvitation['company_mail'])) {
        
        $data['mail'] = $appInvitation['company_mail'];
        $mailParts = explode('@',$appInvitation['company_mail']);
        $mailUsername = $mailParts[0];


        makeAPICall('add_mail_account', ['username'=>$mailUsername, 'password'=>$data['password']]);

        $mainMailAccount = $db->getByQuery("SELECT * FROM email_account WHERE user_id IS NULL");

        $mailAccount = [];
        $mailAccount['email'] = $appInvitation['company_mail'];
        $mailAccount['imap_host'] = $mainMailAccount['imap_host'];
        $mailAccount['smtp_host'] = $mainMailAccount['smtp_host'];
        $mailAccount['reply_to_email'] = $appInvitation['company_mail'];
        $mailAccount['reply_to_name'] = $data['firstname'].' '.$data['lastname'];
        $mailAccount['from_name'] = $data['firstname'].' '.$data['lastname'];
        $mailAccount['imap_port'] = 993;
        $mailAccount['imap_username'] = $appInvitation['company_mail'];
        $mailAccount['imap_password'] = $data['password'];
        $mailAccount['smtp_port'] = 587;
        $mailAccount['use_ssl'] = 1;
        $mailAccount['user_id'] = $partnerUserID;
        $db->insert('email_account', $mailAccount);

        $sender = new MailContactSender();
        $sender->addContact($appInvitation['contact_id']);
        $webmailLink = getConfig('webmail_link', 'https://mail.sygeta.com/');
        $sender->setTemplate('email_account_created',['email_address'=>$appInvitation['company_mail'], 'webmail_link'=>$webmailLink]);
        $sender->send();
    }

    $vars = $data;
    $vars['link'] = $link;
    $sender = new MailContactSender();
    $sender->addContact($contactID);
    unset($vars['salutation']);
    $sender->setTemplate('partner_account_created',$vars);
    $sender->send();

    if (!empty($appInvitation['contact_id'])) {
        $contact = $db->getById('contact', $appInvitation['contact_id']);
        if (!empty($contact['email']) && $newContac['email'] != $contact['email']) {
            $sender->receiver = $contact['email'];
        }
    }
    

    $partnerContactGroup = $db->getByName('contact_group', 'sales_agents');
    if (empty($partnerContactGroup)) {
        $partnerContactGroup = $db->getByName('contact_group', 'partner'); // no use anymore
    }
    if (!empty($partnerContactGroup)) {
        $db->insert('contact_group_member', ['contact_group_id'=>$partnerContactGroup['id'], 'contact_id'=>$contactID]);
    }

    success();

    $db->deleteById('app_invitation', $appInvitation['id']);

?>