<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../chat-gpt/chat-gpt.php');   include_once('../deepl/deepl.php');   include_once('../translation-service/translation-service.php');   include_once('../ai-translator/ai-translator.php');  
    function checkPermissionsForTranslation($translationKey, $useDB=null) {
        global $db;
        global $user_id;
        if (empty($useDB)) {
            $useDB = $db;
        }
        if (hasAccess('database','translation')) {
            // User has access
        } else if (hasAccess('db.table.edit.owned','translation')) {
            $anyTranslation = $useDB->get('translation', ['translation_key'=>$translationKey]); //TODO: Actually check permissions on the db
            if (!empty($anyTranslation) && $anyTranslation['user_id'] != $user_id) {
                http_response_code(403);
                echo json_encode(['message'=>'You have no access to update data in the database table translation','highlight'=>'db','resource'=>'translation']);
                die();            
            }
        } else {
            http_response_code(403);
            echo json_encode(['message'=>'You have no access to update data in the database table translation','highlight'=>'db','resource'=>'translation']);
            die();
        }
    }
?>