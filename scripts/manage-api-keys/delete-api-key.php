<?php 
    namespace Metayota;
    include('../metayota/library.php');
    include('../translation-service/translation-service.php');

    $db->deleteById('api_key', $data['id']);

    success('msg_api_key_deleted');
?>