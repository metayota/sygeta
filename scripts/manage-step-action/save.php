<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    $stepAction = [];
    if ($data['id'] != 'new') { 
        $stepAction['id'] = $data['id'];
    }
    $stepAction['step_action_template'] = $data['step_action_template'];
    $stepAction['parameters'] = json_encode($data['parameters']);
    $stepAction['title'] = $data['title'];
    $stepAction['display_condition'] = $data['display_condition'];
    if (!empty($data['step_type_id'])) {
        $stepAction['step_type_id'] = $data['step_type_id'];
    }
    if ($data['id'] == 'new') { 
        $db->insert('step_action', $stepAction);
        $stepActionID = $db->lastInsertId();
        echo json_encode(['success'=>true,'type'=>'success','message'=>translate('msg_saved'),'new_step_action_id'=>$stepActionID]);
    } else {
        $db->update('step_action', $stepAction);
        success('msg_saved');
    }
?>