<?php namespace Metayota; include_once('../metayota/library.php');   
$data['user_id'] = $user_id;
$profile = $db->get('partner',array('user_id'=>$user_id));
if (!$profile) {
    $db->insert('partner',$data); 
} else {
    $db->update('partner',$data,array('user_id'=>$user_id));
}
success();
?>