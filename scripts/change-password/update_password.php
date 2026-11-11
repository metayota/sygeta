<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../user-lib/user-lib.php');  
	$result = $db->fetchQuery("SELECT password_hash FROM user WHERE id = :user_id AND status = 'active'", [
		'user_id' => $user_id
	]);

	if ($result && password_verify($data['current_password'], $result['password_hash'])) {
		changePassword($user_id, $data['new_password']);
	} else {
		msg('msg_entered_current_password_wrong');
	}
?>