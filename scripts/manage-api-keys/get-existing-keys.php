<?php
    namespace Metayota;
    include('../metayota/library.php');
    include('../translation-service/translation-service.php');
    
    $sql = "SELECT ak.*, ug.name, u.username, t.translation as usergroup FROM api_key ak 
            LEFT JOIN user u 
            ON ak.user_id = u.id 
            LEFT JOIN usergroup ug ON u.usergroup_id = ug.id
            LEFT JOIN translation t ON ug.title = t.translation_key 
            AND t.language=:language";
    $existingKeys = $db->getAllByQuery($sql, ['language'=>$language]);
    jsonResponse($existingKeys);
?>