<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../user-lib/user-lib.php');  

$user = $db->getById('user', $data['user_id']);

if (!empty($user)) {
    changePassword($user['id'], $data['password']);
    msg('msg_password_updated', [], 'success');
    success();
} else {
    msg('reset_password_token_invalid');
}
?>