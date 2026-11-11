<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../invite-functions/invite-functions.php');  

require_once('../../vendor/autoload.php'); 

$client = new \Google_Client();
$client_id = getConfig('google_oauth_client_id');
$client->setClientId($client_id);
$client_secret = getConfig('google_oauth_client_secret');
$client->setClientSecret($client_secret);
$lang = isset($GLOBALS['language']) ? $GLOBALS['language'] : 'en';
$client->setRedirectUri("https://www.alterworld.de/$lang/call/google-addressbook");
$client->addScope('https://www.googleapis.com/auth/contacts.readonly');
$client->addScope('https://www.googleapis.com/auth/contacts.other.readonly');


function getContact($person) {
    $contact = [];

    $names = $person->getNames();
    $emails = $person->getEmailAddresses();
    $organizations = $person->getOrganizations();
    $genders = $person->getGenders();

    if (!empty($names)) {
        $contact['firstname'] = $names[0]->getGivenName(); 
        $contact['lastname'] = $names[0]->getFamilyName();  
    } 
    if (!empty($emails)) {
        foreach ($emails as $email) {
            if ($email->getMetadata()->getPrimary() || !isset($contact['mail_address'])) {
                $contact['mail_address'] = $email->getValue();
            }
        }
    }
    if (!empty($organizations)) {
        foreach ($organizations as $org) {
            if ($org->getMetadata()->getPrimary()) {
                $contact['company'] = $org->getName();
            }
        }
    }
    $contact['gender'] = null;
    if (!empty($genders)) {
        $contact['gender'] = $genders[0]->getValue();
    }
    return $contact;
}

if (isset($_GET['code'])) {
    $contacts = [];
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);

    // The access token is now available in the $token variable.
    // You can now use it to call Google APIs.

    // For instance, to get user's contacts:
    $service = new \Google_Service_PeopleService($client);
    $pageSize = 500; // Seitengröße

    // Kontakte abrufen
    $pageToken = NULL;
    do {
        $connections = $service->people_connections->listPeopleConnections(
            'people/me', 
            array(
                'pageSize' => $pageSize,
                'pageToken' => $pageToken,
                'personFields' => 'names,emailAddresses,organizations'
            )
        );

      //  $connections = $request->execute();

        foreach($connections->getConnections() as $person) {
            $contacts[] = getContact($person);
        }

     //   $pageToken = $connections->getNextPageToken();
    } while ($pageToken);


    // Andere Kontakte abrufen
    $pageToken = NULL;
    do {
        $otherContacts = $service->otherContacts->listOtherContacts(
            array(
                'pageSize' => $pageSize,
                'pageToken' => $pageToken,
                'readMask' => 'names,emailAddresses'
            )
        );

     //   $otherContacts = $request->execute();

        foreach($otherContacts->getOtherContacts() as $person) {
            $contacts[] = getContact($person);
        }

       // $pageToken = $otherContacts->getNextPageToken();
    } while ($pageToken);

    importContacts($contacts,$user_id); 
    $user = $db->getById('user',$user_id);
    header('Location: /'.$user['language'].'/invite/addressbook');
} else {
    $authUrl = $client->createAuthUrl();
    // Redirect user to consent URL
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
}
?>