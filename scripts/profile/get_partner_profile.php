<?php namespace Metayota; include_once('../metayota/library.php');  
$profile = $db->get('partner',array('user_id'=>$user_id));
echo json_encode($profile);
?>