<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
include('../step-actions/lib.php');
include_once('../access-check/access-check.php');
checkReadPermissions('contact', $data['contact_id']);


$stepTypeSQL = "SELECT st.*,t.translation as title_translated,stt.sql_template,st.link, stt.link as link_template FROM step_type st LEFT JOIN translation t ON st.title = t.translation_key AND t.language=:language LEFT JOIN step_type_template stt ON st.step_type_template = stt.name WHERE (st.hidden=0 OR st.hidden IS NULL) ORDER BY st.order_no";
$stepTypes = $db->getAllByQuery($stepTypeSQL,['language'=>$GLOBALS['language']]);

if (!empty($data['contact_id'])) {
    $contact = $db->getById('contact', $data['contact_id']);
    $customer = $db->get('customer',['contact_id'=>$contact['id']]);

    $allSteps = [];
    foreach($stepTypes as $stepType) {
        $completed = false;

        if (empty($stepType['link'])) {
            $stepType['link'] = $stepType['link_template'];
        }
        
        if (!empty($stepType['sql_template'])) {
            $conditionSql = $stepType['sql_template'];
            
            // Get template parameters
            if (!empty($stepType['step_type_template_params'])) {
                $allReplacements = json_decode($stepType['step_type_template_params'], true);
            } else {
                $allReplacements = [];
            }
            
            // Add contact_id and customer_id to replacements
            $allReplacements['contact_id'] = $contact['id'];
            if (!empty($customer)) {
                $allReplacements['customer_id'] = $customer['id'];
            }

            try {
                // First: Replace {placeholders} using the lib function
                $conditionSql = replacePlaceholders($conditionSql, $allReplacements);

                // Second: Extract named parameters for the SQL query
                $where = extractNamedParameters($conditionSql, $allReplacements);

                // Execute the query with named parameters
                if (allNamedParametersFilled($conditionSql, $where)) {
                    $result = $db->getByQuery($conditionSql, $where);
                    $completed = !empty($result) && $result != false;
                } else {
                    $result = false;
                    $completed = false;
                }
                
                if ($completed) {
                    $allReplacements = array_merge($result, $allReplacements);
                    $stepType['link'] = replacePlaceholders($stepType['link'], $allReplacements);
                }
             } catch(\Exception $e) {
                $stepType['title_translated'] = $stepType['title_translated']. ' ('.$e->getMessage().')';
                $completed = false;
            }
        }
        
        // Replace placeholders in link
        if (!empty($customer)) {
            $stepType['link'] = str_replace('{customer_id}', $customer['id'], $stepType['link']);
        }
        $stepType['link'] = str_replace('{contact_id}', $contact['id'], $stepType['link']);

        $stepType['completed'] = $completed;
        $allSteps[] = $stepType;
    }
    
    echo json_encode($allSteps);
}
?>