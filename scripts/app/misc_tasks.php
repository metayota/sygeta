<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
  
    $currentUser = $db->getById('user', $user_id);
    $currentUsergroup = $db->getById('usergroup', $currentUser['usergroup_id']);

    $needsAvailability = ['independent_agent','partner','admin'];
    if ( in_array($currentUsergroup['name'], $needsAvailability) ) {
        $partner = $db->get('partner', ['user_id'=>$user_id]);
        if (!empty($partner)) {
            $availability = $db->get('partner_availability', ['partner_id'=>$partner['id']]);
            if (empty($availability )) {
                echo json_encode(['type'=>'success','message'=>translate('fill_out_partner_availability'), 'link'=>'/availability','button_name'=>translate('btn_goto_availability')]);
                die();
            }
        }
    }
    success();  
?>