<?php namespace Metayota; include_once('../metayota/library.php');   

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
    $user['username'] = $contact['firstname'].'-'.$contact['lastname'];
    $user['password_hash'] = password_hash($password,PASSWORD_DEFAULT); 
    $user['status'] = 'active';
    $user['usergroup_id'] = 4;
    $user['email'] = $contact['email'];

    $password = generatePassword();
    $db->insert('user',$user);

    $sender = new MailSender();
    $sender->addContact($contact['id']);
    $sender->setTemplate('account_created',['username'=>$user['username'],'password'=>$password]);
    $sender->send();
    
?>