<?php namespace Metayota; include_once('../metayota/library.php');   
$data['user_id'] = $user_id;
$profile = $db->get('profile',array('user_id'=>$user_id));
if (!$profile) {
    $db->insert('profile',$data); 
} else {
    $db->update('profile',$data,array('user_id'=>$user_id));
}
success();
?>