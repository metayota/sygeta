<?php
    namespace Metayota;
    include_once('../metayota/library.php');
    include_once('../key-generator/key-generator.php');
    include_once('../.translation-service/translation-service.php');

    $apiKey = generateKeyWithDashes();

    if (empty($data['user_id'])) {
        $newUser = [];
        $newUser['username'] = $data['username'];
        $newUser['usergroup_id'] = $data['usergroup_id'];
        $db->insert('user', $newUser);
        $userID = $db->lastInsertId();
    } else {
        $userID = $data['user_id'];

    }

    $db->insert('api_key', [
        'user_id' => $userID,
        'api_key' => $apiKey
    ]);

    success('msg_api_key_generated');
    
?>