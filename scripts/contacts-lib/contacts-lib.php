<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  

function getTimezoneForCurrentUser() {
    global $db;
    global $user_id;
    $userTimezone = $db->getColumnById('user','timezone', $user_id);
    if (empty($userTimezone)) {
        $userTimezone = $db->getColumn('app_settings','default_timezone');
    }
    return $userTimezone;
}

function formatCurrencyForContact($contact, $value, $currency) {
    global $db;
    $contactLanguage = $db->get('language', ['language_key'=>$contact['language']]);
    $locale = $contactLanguage['locale'];
    $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
    return $formatter->formatCurrency($value, $currency); 
}

function getPartnerContact($contact) {
    global $db;
    $partner = $db->getById('partner', $contact['partner_id']);
    return $db->getById('contact', $partner['contact_id']);
}

function getTimezoneForContact($contact) {
    global $db;
    if (is_array($contact)) {
        $contactTimezone = $contact['timezone'];
    } else {
        $contactTimezone = $db->getColumnById('contact','timezone',$contact);
    }
    if (empty($contactTimezone)) {
        $contactTimezone = $db->getColumn('app_settings','default_timezone');
    }
    return $contactTimezone;
}


function formatDatetimeForUser($mysqlDatetime) {
    global $db;
    $dateFormat = translate('php_datetime_format');
    $timezone = getTimezoneForContact($contact);
    $utcStartDate = new \DateTime($mysqlDatetime, new \DateTimeZone('UTC'));
    $timezoneCurrentUser = getTimezoneForContact($_SESSION['loggedInUser']['contact_id']);
    $utcStartDate->setTimezone(new \DateTimeZone($timezoneCurrentUser));
    return $utcStartDate->format($dateFormat); 
}

function formatDatetimeForContact($mysqlDatetime, $contact) {
    global $db;
    if (!is_array($contact)) {
        $contact = $db->getById('contact', $contact);
    }

    $dateFormat = translateLanguage($contact['language'], 'php_datetime_format');
    $timezone = getTimezoneForContact($contact);
    $utcStartDate = new \DateTime($mysqlDatetime, new \DateTimeZone('UTC'));
    $utcStartDate->setTimezone(new \DateTimeZone($timezone));
    return $utcStartDate->format($dateFormat);  
}

function getContactByID($contact_id) {
    global $db;
    global $user_id;
    
    // Is responsbile partner
    $contact = $db->getById('contact', $contact_id);
    $loggedInPartnerID = $db->getColumn('partner', 'id', ['user_id'=>$user_id]);
    if ($loggedInPartnerID == $contact['partner_id']) {
        return $contact;
    }
    if (hasAccess('db.table.get_all', 'contact')) {
        return $contact;
    }
    if ($_SESSION['loggedInUser']['contact_id'] == $contact['id']) {
        return $contact;
    }
    return false;
}

function getSalutation($contact) {
    $firstName = isset($contact['firstname']) ? trim($contact['firstname']) : '';
    $lastName = isset($contact['lastname']) ? trim($contact['lastname']) : '';
    $salutation = isset($contact['salutation']) ? $contact['salutation'] : '';
    $lang = $contact['language'];

    if ($salutation == 'mister') {
        if (!empty($lastName)) {
            return translateLanguage($lang, 'salutation_mister_lastname', ['lastname' => $lastName]);
        } else if (!empty($firstName)) {
            return translateLanguage($lang, 'salutation_hello_firstname', ['firstname' => $firstName]);
        } else {
            return translateLanguage($lang, 'salutation_mister_generic');
        }
        
    } else if ($salutation == 'miss' || $salutation == 'mrs') {
        if (!empty($lastName)) {
            return translateLanguage($lang, 'salutation_miss_lastname', ['lastname' => $lastName]);
        } else if (!empty($firstName)) {
            return translateLanguage($lang, 'salutation_hello_firstname', ['firstname' => $firstName]);
        } else {
            return translateLanguage($lang, 'salutation_miss_generic');
        }
        
    } else {
        if (!empty($firstName)) {
            return translateLanguage($lang, 'salutation_hello_firstname', ['firstname' => $firstName]);
        } else if (!empty($lastName)) {
            return translateLanguage($lang, 'salutation_neutral_lastname', ['lastname' => $lastName]);
        } else {
            return translateLanguage($lang, 'salutation_formal');
        }
    }
}
?>