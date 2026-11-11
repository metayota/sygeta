<?php namespace Metayota; include_once('../metayota/library.php');  
$profile = $db->get('user',array('id'=>$user_id));
echo json_encode($profile);
?>