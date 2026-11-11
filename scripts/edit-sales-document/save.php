<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../access-check/access-check.php');  
    include_once('../access-check/access-check.php');

    $id = $data['id'];
    checkWritePermissions('sales_document', $id);
    $document = $data['document'];
    $db->update('sales_document', $document, ['id' => $id]);
    echo success('saved_success');
?>