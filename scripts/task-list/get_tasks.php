<?php namespace Metayota; include_once('../metayota/library.php');  
$tasks = $db->getAll('task',['hidden'=>0,'user_id'=>$user_id]);
echo json_encode($tasks);
?>