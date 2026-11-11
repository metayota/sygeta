<?php namespace Metayota; include_once('../metayota/library.php');  
include_once('../access-check/access-check.php');
$id = $data['id'];
checkWritePermissions('task', $id);
$db->update('task', ['hidden' => 1], ['id' => $id]);
success();
?>