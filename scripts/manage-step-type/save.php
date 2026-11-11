<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    $stepTypeChanges = [];
    $stepTypeChanges['step_type_template'] = $data['step_type_template'];
    $stepTypeChanges['name'] = $data['name'];
    $stepTypeChanges['title'] = $data['title'];
    $stepTypeChanges['order_no'] = $data['order_no'];
    $stepTypeChanges['hidden'] = $data['hidden'];
    $stepTypeChanges['step_type_template_params'] = json_encode($data['step_type_template_params']);
    
    if ($data['id'] == 'new') {
        $db->insert('step_type', $stepTypeChanges);
        echo json_encode(['type'=>'success','success'=>true,'new_step_type_id'=>$db->lastInsertId()]);
    } else {
        $db->update('step_type', $stepTypeChanges, ['id'=>$data['id']]);
        success('msg_step_type_template_saved');
    }
?>