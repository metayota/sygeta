<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    $stepAction = isset($data['id']) ? $db->getById('step_action', $data['id']) : null;
    if (!empty($stepAction)) {
        if (!empty($stepAction['step_action_template'])) {
            $stepAction['step_action_template_obj'] = $db->getByName('step_action_template', $stepAction['step_action_template']);
        }
        if (!empty($stepAction['parameters'])) {
            $stepAction['parameters'] = json_decode($stepAction['parameters']);
        }
        echo json_encode($stepAction);
    } else {
        echo json_encode(['id'=>'new']);
    }
?>