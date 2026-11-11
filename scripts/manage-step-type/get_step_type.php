<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   

    if ($data['step_type_id'] == 'new') {
        echo json_encode(['id'=>'new']);
    } else {
        $stepType = $db->getById('step_type', $data['step_type_id']);
        if (!empty($stepType)) {
            if (!empty($stepType['step_type_template'])) {
                $stepType['step_type_template_obj'] = $db->getByName('step_type_template', $stepType['step_type_template']);
            }
            if (!empty($stepType['step_type_template_params'])) {
                $stepType['step_type_template_params'] = json_decode($stepType['step_type_template_params']);
            }
            echo json_encode($stepType);
        } else {
            echo json_encode([]);
        }
    }
?>