<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../invite-functions/invite-functions.php');  
$contacts = [];
if (!empty($data['csv_file'])) {
    $file = fopen('../../uploads/addressbook/'.$data['csv_file'], 'r');
    if ($file !== false) {
        $header = fgetcsv($file); // read the header

        if ($header !== false) {
            $firstNameKey = getMatchingKey($header, ['vorname', 'firstname', 'first_name', 'first name', 'fname','given name','given_name']);
            $lastNameKey = getMatchingKey($header, ['nachname', 'lastname', 'last_name', 'last name', 'lname', 'family name','family_name']);
            $emailKey = getMatchingKey($header, ['e-mail', 'email', 'e_mail', 'mail', 'email address', 'mail address', 'e-mail adresse','e-mail address','E-mail 1 - Value']);
            $companyKey = getMatchingKey($header, ['company','company_name','company name']);
            
            

            while (($line = fgetcsv($file)) !== FALSE) {
                
                if (count($header) == count($line)) {
                    $contact = array_combine($header, $line);
                    $contact[$emailKey] = filter_var($contact[$emailKey], FILTER_VALIDATE_EMAIL);
                    if ($emailKey !== null) {
                        $contacts[] = [
                            'firstname' => $contact[$firstNameKey],
                            'lastname' => $contact[$lastNameKey],
                            'mail_address' => $contact[$emailKey],
                            'company' => $contact[$companyKey],
                            'source' => 'csv_import',
                            'user_id' => $user_id
                        ];
                    }

                }
            }
        }
        fclose($file);
    }
}

if (empty($contacts)) {
    // Regex zum Filtern von E-Mail-Adressen
    
    $pattern = '/[\w\.-]+@[\w\.-]+\.\w+/';
    if (!empty($data['csv_file'])) {
        $text = file_get_contents('../../uploads/addressbook/'.$data['csv_file']);
    } else {
        $text = $data['email_addresses'];
    }
    // Findet E-Mail-Adressen und speichert sie in $matches
    preg_match_all($pattern, $text, $matches);
    

    
    $ids = [];
    foreach ($matches[0] as $email) {
        $existingContact = $db->get('contact',['user_id'=>$user_id,'mail_address'=>$email]);
        if (empty($existingContact )) {
            $db->insert('contact',['mail_address'=>$email,'source'=>'csv_import','user_id'=>$user_id]);
            $ids[] = $db->connection->lastInsertId();
        } else {
            if ($existingContact['deleted'] == 1) {
                $db->update('contact',['deleted'=>0], ['id'=>$existingContact->id]);
            }
            
        }
    }
    
    if (!empty($data['email_addresses'])) { // user clicked submit button and needs the ids
        echo json_encode(['contact_ids'=>$ids]);
        die();
    } else {
        msg('successfully_uploaded_addressbook',[],'success');
    }
    
}

function getMatchingKey($header, $variants) {
    foreach ($header as $key) {
        if (in_array(strtolower($key), $variants, true)) {
            return $key;
        }
    }

    return null;
}

// For testing purposes
importContacts($contacts, $user_id);/*
foreach($contacts as $contact) {
    $existingWhere = ['user_id'=>$user_id,'mail_address'=>$contact['mail_address']];
    $existingContact = $db->get('contact',$existingWhere);
    if (empty($existingContact)) {
        $db->insert('contact', $contact);
    } else {
        $contact['firstname'] = empty($contact['firstname']) ? $existingContact['firstname'] : $contact['firstname'];
        $contact['lastname'] = empty($contact['lastname']) ? $existingContact['lastname'] : $contact['lastname'];
        $contact['company'] = empty($contact['company']) ? $existingContact['company'] : $contact['company'];
        $db->update('contact',$contact,$existingWhere);
    }
}*/

msg('successfully_uploaded_addressbook',[],'success');

?>