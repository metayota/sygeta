<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    include_once('../access-check/access-check.php');
    checkReadPermissions('contact', $data['contact_id']);
    $stepActions = $db->getAll('step_action',['step_type_id'=>$data['current_step']]);
    $visibleStepActions = [];
    foreach($stepActions as $stepAction) {
        $stepAction['title_translated'] = translate($stepAction['title']);
        if (!empty($stepAction['condition_sql'])) {
            $where = [];
            if (str_contains($stepAction['condition_sql'],':customer_id')) {
                $where['customer_id'] = $data['customer_id'];
            }
            if (str_contains($stepAction['condition_sql'],':contact_id')) {
                $where['contact_id'] = $data['contact_id'];
            }
            $result = $db->getByQuery($stepAction['condition_sql'],$where);
            if ($result) {
                $visibleStepActions[] = $stepAction;
            }
        } else {
            $visibleStepActions[] = $stepAction;
        }
    }
    echo json_encode($visibleStepActions);
?>