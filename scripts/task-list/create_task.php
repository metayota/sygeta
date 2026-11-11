<?php namespace Metayota; include_once('../metayota/library.php');   
    $data['user_id'] = $user_id;
    $db->insert('task',$data);
    success('task_successfully_created');
?>