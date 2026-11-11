<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    $contactID = $_SESSION['loggedInUser']['contact_id'];
    $db->insertIgnore('documents_confirmed',['contact_id'=>$contactID, 'confirmed'=>true]);

    success('documents_confirmed_success');
?>