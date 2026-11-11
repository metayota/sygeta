<?php namespace Metayota; include_once('../metayota/library.php');  
require_once '../../vendor/autoload.php';
use Firebase\JWT\JWT;

include_once('../shortlink/lib.php');
include_once('../slugify/slugify.php');

function createJitsiMeeting($roomName, $userName, $isModerator = false) {
    $payload = [
        "iss" => getConfig('jitsi_app_id'),
        "aud" => "jitsi", 
        "sub" => "meet.sygeta.com",
        "room" => $roomName,
        "exp" =>  time() + (365 * 24 * 3600),
        "moderator" => $isModerator,
        "context" => [
            "user" => [
                "id" => uniqid(),
                "name" => $userName,
                "moderator" => $isModerator 
            ]
        ]
    ];
    
    $jwt = JWT::encode($payload,  getConfig('jitsi_app_secret'), 'HS256');
    return "https://meet.sygeta.com/" . $roomName . "?jwt=" . $jwt;
}


function createJitsiMeetingShortlink($roomName, $userName, $isModerator = false) {
   global $db;
   
   $roomNameSlugified = slugifyAlphaNumeric($roomName);
   $roomNameSlugified = str_replace('-', '', $roomNameSlugified);

   // App URL aus Datenbank holen
   $appSettings = $db->get('app_settings', []);
   $appUrl = rtrim($appSettings['app_url'], '/'); // Trailing slash entfernen
   
   // Jitsi Meeting URL erstellen
   $jitsiUrl = createJitsiMeeting($roomNameSlugified, $userName, $isModerator);
   
   // Shortlink erstellen
   $shortCode = createShortLink($jitsiUrl);
   
   return $appUrl . '/s/' . $shortCode;
}

?>