<?php
    namespace Metayota;
    include('../metayota/library.php');

    $usergroupID = $db->getColumnById('user', 'usergroup_id', $data['user_id']);
    $usergroupTitle = $db->getColumnById('usergroup', 'title', $usergroupID);
    jsonResponse(['usergroupTitle'=>$usergroupTitle]);
?>