<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-contact-sender/mail-contact-sender.php');   

requireAccess('db.table.edit.owned','contact');

function generatePassword() {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $password = '';
    
    for ($i = 0; $i < 9; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    
    return $password;
}

    $user = [];
    $contact = $db->getById('contact',$data['contact_id']);
    $appSettings = $db->get('app_settings');
    $password = generatePassword();
    $user['username'] = mb_strtolower($contact['firstname'].'-'.$contact['lastname']);
    $user['password_hash'] = password_hash($password,PASSWORD_DEFAULT); 
    $user['status'] = 'active';
    $user['usergroup_id'] = 4;
    $user['email'] = $contact['email'];
    $user['contact_id'] = $contact['id'];
    $user['timezone'] = $appSettings['default_timezone'];
    $db->insert('user',$user);

    $customer['user_id'] = $contact['user_id'];
    $customer['contact_id'] = $contact['id'];
    $customer['full_name'] = $contact['full_name'];
    
    $partner = $db->get('partner',['user_id'=>$contact['user_id']]);
    $customer['partner_id'] = $partner['id'];

    $db->insert('customer',$customer);

    $contactGroup = $db->getByName('contact_group', 'prospective_customer');

    $db->insert('contact_group_member', ['contact_id'=>$contact['id'], 'contact_group_id'=>$contactGroup['id']]);

    $sender = new MailContactSender();
    $sender->addContact($contact['id']);
    $sender->setTemplate('account_created',['username'=>$user['username'],'password'=>$password]);
    $sender->send();

    success('account_created_and_mail_sent');
    
?>