<?php namespace Metayota; include_once('../metayota/library.php');  
function changePassword($userID, $newPassword) {
    // Datenbankverbindung sicherstellen
    global $db;

    // Benutzer mit aktivem Status abrufen
    $user = $db->fetchQuery("SELECT id FROM user WHERE id = :user_id AND status = 'active'", [
        'user_id' => $userID
    ]);

    if (!$user) {
        msg('msg_user_not_found_or_inactive');
        return;
    }

    // Neues Passwort hashen
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Passwort in Tabelle 'user' aktualisieren
    $db->update('user', ['password_hash' => $hash], ['id' => $userID]);

    // E-Mail-Adresse aus Tabelle 'email_accounts' holen
    $mailAccount = $db->get('email_account', ['user_id' => $userID]);
    if ($mailAccount && empty($mailAccount['email'])) {
       

        // API-Aufruf vorbereiten
        $apiKey = getConfig('metayota_api_key');
        $payload = json_encode([
            'email' => $mailAccount['email'],
            'new_password' => $newPassword,
            'api_key' => $apiKey
        ]);

        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => $payload
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents('https://www.metayota.de/call/mail-api/change-password', false, $context);

        // IMAP-Passwort in 'email_accounts' aktualisieren
        $db->update('email_account', ['imap_password' => $newPassword], ['id' => $mailAccount['id']]);
    }

    $db->update('user', ['forgot_token' => null], ['id' => $userID]);

    msg('msg_password_changed', [], 'success');
}

function generateRandomFormattedString($totalLength = 32) {
    // The allowed characters
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $maxRandomChars = 0;
    
    // Determine the maximum number of random characters (M)
    // such that: M + (ceil(M / 4) - 1) <= $totalLength
    // We loop from 0 up to $totalLength to find the maximum valid M.
    for ($M = 0; $M <= $totalLength; $M++) {
        // Number of groups when split into chunks of 4
        $groups = (int) ceil($M / 4);
        // Total length equals random characters plus number of dashes (groups - 1)
        $currentTotal = $M + ($groups > 0 ? $groups - 1 : 0);
        if ($currentTotal <= $totalLength) {
            $maxRandomChars = $M;
        } else {
            break; // further increasing M would exceed $totalLength
        }
    }
    
    // Generate a string of $maxRandomChars random characters
    $randomString = '';
    $maxIndex = strlen($characters) - 1;
    for ($i = 0; $i < $maxRandomChars; $i++) {
        // random_int is used for a cryptographically secure random number
        $randomString .= $characters[random_int(0, $maxIndex)];
    }
    
    // Split the string into chunks of 4 characters and join them with a dash
    $chunks = str_split($randomString, 4);
    $formattedString = implode('-', $chunks);
    
    // Return the URL-encoded string (optional)
    return urlencode($formattedString);
}
?>