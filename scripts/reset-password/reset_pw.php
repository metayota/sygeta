<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../user-lib/user-lib.php');  
$token = $data['token'];
$user = null;

if (!empty($token)) {
    $user = $db->get('user', ['forgot_token' => $token]);
}

if (!empty($user)) {
    // Token ungültig machen & Passwort ändern über bestehende Funktion
    $db->update('user', ['forgot_token' => null], ['id' => $user['id']]);

    // Passwortänderung und Mail-API via zentrale Funktion
    changePassword($user['id'], $data['password']);

    msg('msg_password_updated', [], 'success');
    success();
} else {
    msg('reset_password_token_invalid');
}
?>