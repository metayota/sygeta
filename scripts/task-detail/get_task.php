<?php namespace Metayota; include_once('../metayota/library.php');  
include_once('../access-check/access-check.php');
$id = $data['id'];
checkReadPermissions('task', $id);
$task = $db->get('task', ['id' => $id]);
echo json_encode($task);
?>