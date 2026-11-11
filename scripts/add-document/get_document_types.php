<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   

    if (!empty($data['contact_id'])) {
        $contactID = $data['contact_id'];
    } else {
        $contactID = $_SESSION['loggedInUser']['contact_id'];
    }


    $sql = "SELECT DISTINCT dt.* 
            FROM document_type dt
            LEFT JOIN document_type_contact_group dtcg ON dt.name = dtcg.document_type
            LEFT JOIN contact_group cg ON dtcg.contact_group = cg.name
            LEFT JOIN contact_group_member cgm ON cg.id = cgm.contact_group_id
            WHERE cgm.contact_id = :contact_id";
    $suitableTypes = $db->getAllByQuery($sql,['contact_id'=>$contactID]);
    foreach($suitableTypes as &$suitableType) {
        $suitableType['title_translated'] = translate($suitableType['title']);
    }
    echo json_encode($suitableTypes);
?>