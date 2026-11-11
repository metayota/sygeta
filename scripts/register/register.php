<?php 
    namespace Metayota;
    include_once('../metayota/library.php');
    include_once('../php-mailer/php-mailer.php');
    include_once('../translation-service/translation-service.php');
    include_once('../mail-contact-sender/mail-contact-sender.php');
    include_once('../sygeta-lib/compliance.php');

    include('../create-contact/lib.php');
	if (isset($data['mail'])) {

        $pattern = '/^[a-zA-Z_\-öäüÖÜÄ0-9]+$/u'; // Regular expression pattern
        if (!preg_match($pattern, $data['username'])) {
            msg('msg_invalid_username'); 
        }

        $user = $db->get('user',['email'=>$data['mail']]);
        if (!empty($user)) {
            msg('mail_already_exists');
        }

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

      
        $newContact = [
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'full_name' => $data['firstname'].' '.$data['lastname'],
            'company' => $data['company'],
            'salutation' => $data['gender'],
            'email' => $data['mail'],
            'timezone' => $data['timezone']
        ];

        $contactID = createContact($newContact);

        // Insert the new user into the database
		$result = $db->insert('user', 
            array('username' => $data['username'],
            'verification_code'=>$verification,
            'usergroup_id' => 4,
            'contact_id' => $db->lastInsertId(),
            'email' => $data['mail'],
            'status' => 'active',
            'password_hash' => $hash,
            'timezone' => $data['timezone'])
        );		

        $user_id = $db->connection->lastInsertId();

        session_start();
        $user = $db->get('user',['id'=>$user_id]);
        $_SESSION['loggedInUser'] = $user;
        session_write_close();

        $vars = $data;
        $vars['link'] = $link;

        $vars = $data;
        $vars['link'] = $link;
        $sender = new MailContactSender();
        $sender->addContact($contactID);
        $sender->setTemplate('user_account_created',$vars);
        $sender->send();

        if (gdprEnabled()) {
            insertLegalBasis($contactID, 'contract', 'legal_basis_source_website_registration', 'legal_basis_terms_and_conditions_accepted');
        }

		success();
	} 
?>