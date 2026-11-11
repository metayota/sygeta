<?php 
    namespace Metayota;
    include_once('../metayota/library.php');
    include_once('../translation-service/translation-service.php');

    $legalText = $db->get('legal_text', ['name'=>$data['name'], 'archived'=>false]);
    $companySettings = $db->get('company_settings');
    $allVars = [];
    foreach($companySettings as $key => $value) {
        $allVars['company_'.$key] = $value;
    }
    $allVars['company_region'] = translate($allVars['company_region']);
    $legalText['content_translated'] = translate($legalText['content'], $allVars);
    echo json_encode($legalText);
?>