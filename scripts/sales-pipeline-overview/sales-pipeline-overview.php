<?php 
namespace Metayota; 

include_once('../metayota/library.php');   
include_once('../translation-service/translation-service.php');  
include_once('../access-check/access-check.php');

// Check appropriate permissions for viewing step configuration
// Adjust this based on your access control requirements
checkReadPermissions('step_type', null);

$stepTypeSQL = "SELECT st.*, t.translation as title_translated, t2.translation as description_translated, stt.sql_template, st.link, stt.link as link_template 
                FROM step_type st 
                LEFT JOIN translation t ON st.title = t.translation_key AND t.language = :language 
                LEFT JOIN translation t2 ON st.description = t2.translation_key AND t2.language = :language 
                LEFT JOIN step_type_template stt ON st.step_type_template = stt.name 
                WHERE (st.hidden = 0 OR st.hidden IS NULL) 
                ORDER BY st.order_no";

$stepTypes = $db->getAllByQuery($stepTypeSQL, ['language' => $GLOBALS['language']]);

$allStepsWithActions = [];

foreach ($stepTypes as $stepType) {
    // Get all step actions for this step type
    $stepActions = $db->getAll('step_action', ['step_type_id' => $stepType['id']]);
    
    $stepType['actions'] = [];
    
    foreach ($stepActions as $stepAction) {
        $stepAction['title_translated'] = translate($stepAction['title']);
        $stepAction['description_translated'] = translate($stepAction['description']);
        
        // Get the step action template details
        $stepActionTemplate = $db->getByName('step_action_template', $stepAction['step_action_template']);
        
        if (!empty($stepActionTemplate['visualizer'])) {
            [$callResource, $callAction] = explode('/', $stepActionTemplate['visualizer']);
            $stepAction['visualized_html'] = callAction($callResource, $callAction, ['step_action'=>$stepAction, 'step_action_template'=>$stepActionTemplate]);
        }

        // Include template information (without executing any SQL or replacing placeholders)
        $stepAction['link_template'] = $stepActionTemplate['link'] ?? null;
        $stepAction['condition_sql_template'] = $stepActionTemplate['condition_sql'] ?? null;
        $stepAction['resource_template'] = $stepActionTemplate['resource'] ?? null;
        $stepAction['action_template'] = $stepActionTemplate['action'] ?? null;
        $stepAction['success_sql_template'] = $stepActionTemplate['success_sql'] ?? null;
        $stepAction['visualizer'] = $stepActionTemplate['visualizer'] ?? null;
        
        $stepType['actions'][] = $stepAction;
    }
    
    $allStepsWithActions[] = $stepType;
}

echo json_encode($allStepsWithActions);
?>