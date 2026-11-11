<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-contact-sender/mail-contact-sender.php');   include_once('../user-lib/user-lib.php');   
    $appSettings = $db->get('app_settings');
    $contactType = $db->getByName('contact_type', 'partner');
    $contact = ['firstname' => $data['firstname'], 'lastname' => $data['lastname'], 'salutation' => $data['salutation'], 'language' => $data['language'], 'email' => $data['email']];
    $contact['full_name'] = $contact['firstname'].' '.$contact['lastname'];
    $contact['contact_type_id'] = $contactType['id'];
    $db->insert('contact', $contact);

    $contactID = $db->lastInsertId();
    $invitationKey = generateRandomFormattedString();
    $appInvitation = [
        'accepted'=>0,
        'invitation_date'=>date('Y-m-d H:i:s'),
        'invitation_key' => $invitationKey,
        'contact_id' => $contactID,
        'usergroup_id' => $data['usergroup_id'],
        'company_mail' => $data['company_mail']
    ];
    $db->insert('app_invitation', $appInvitation);

    $appInvitationID = $db->lastInsertId();
 
    $sender = new MailContactSender();
    $sender->addContact($contactID);
    $sender->setTemplate('businesspartner_invitation',['register_link'=>$appSettings['app_url'].$contact['language']. '/partner-registration#'.$invitationKey]);
    $sender->send();

    success('msg_partner_invited');
?>