<?php namespace Metayota; include_once('../metayota/library.php');   
unset($data['usergroup_id']);
$db->update('user',$data,array('id'=>$user_id));


$user = $db->getById('user', $user_id);
if (!empty($user)) {
    $contactID = $db->getColumnById('contact','id', $user['contact_id']);
    $db->update('contact', ['timezone'=>$data['timezone']], $contactID);
}

success();
?>