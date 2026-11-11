<?php 
    namespace Metayota;
    include_once('../metayota/library.php');
    include_once('../sync-functions/sync-functions.php');
    include_once('../translation-service/translation-service.php');
    $apiKey = getConfig('metayota_sync_api_key');
    if (empty($apiKey) || $apiKey != $_GET['api_key']) {
        errorMsg(translate('api_key_not_correct'));
    }

    $resources = $db->getAllByQuery('SELECT name, title, hash, version, version_txt, last_update FROM resource WHERE visibility="published" ORDER BY name');
    echo(json_encode($resources));
?>