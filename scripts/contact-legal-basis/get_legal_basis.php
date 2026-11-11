<?php
    namespace Metayota;
    include_once('../metayota/library.php');
    include_once('../access-check/access-check.php');
    include_once('../translation-service/translation-service.php');
    include_once('../contacts-lib/contacts-lib.php');
    include_once('../api/contact-legal-bases-lib.php');


    if (!empty($data['contact_id'])) {
        checkReadPermissions('contact', $data['contact_id']);
        $legalBasesService = new ContactLegalBasisService();
        
        echo json_encode($legalBasisList);
    }
?>