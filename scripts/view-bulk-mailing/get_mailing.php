<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    include_once('../access-check/access-check.php');
    checkReadPermissions('bulk_mailing', $data['id']);

    $mailing = $db->getById('bulk_mailing',$data['id']);
    $mailing['title_translated'] = translate($mailing['title']);

    $where = ['bulk_mailing_id'=>$mailing['id']];
    $sql = "SELECT bmr.sent, bmr.id as bulk_mailing_id, c.full_name,c.id,bmr.file FROM bulk_mailing_receiver bmr LEFT JOIN contact c ON bmr.contact_id = c.id WHERE bmr.bulk_mailing_id=:bulk_mailing_id";
    $mailing['receivers'] = $db->getAllByQuery($sql,$where);

    echo json_encode($mailing);
?>