<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    include_once('../access-check/access-check.php');
    checkReadPermissions('contact', $data['contact_id']);

    callAction('webservices-steps','update-current-steps',['contact_id'=>$data['contact_id']]);
    $contact = $db->getById('contact',$data['contact_id']);
/*
    $stepTypeSQL = "SELECT st.id,st.order_no,t.translation,stt.sql_template as title_translated FROM step_type st LEFT JOIN step_type_template stt ON st.step_type_template = stt.name LEFT JOIN translation t ON st.title = t.translation_key AND t.language=:language ORDER BY st.order_no";
    $stepTypes = $db->getAllByQuery($stepTypeSQL,['language'=>$GLOBALS['language']]);

    $allSteps = [];
    $highestCompletedOrderNo = -1;
    $highestCompletedId = null;
    $contact = $db->getById('contact', $data['contact_id']);
    $customer = $db->get('customer',['contact_id'=>$contact['id']]);

    foreach($stepTypes as $stepType) {
        if (!empty($stepType['sql_template'])) {
            try {
                $whereData = [];
                $queryNotPossible = false;

                if (str_contains($stepType['sql_template'],':customer_id')) {
                    if (!empty($customer)) {
                        $whereData['customer_id'] = $customer['id'];
                    } else {
                        $result = null;
                        $queryNotPossible = true;
                    }
                }

                if (str_contains($stepType['sql_template'],':contact_id')) {
                    if (!empty($contact)) {
                        $whereData['contact_id'] = $contact['id'];
                    } 
                }



                $result = $db->getByQuery($stepType['sql_template'],$whereData);
            } catch(\Exception $e) {
                $result = null;
            }
        } else {
            $result = null;
        }
        $completed =  !empty($result);

        $stepType['completed'] = $completed;
        $allSteps[] = $stepType;

        // Check if this step is completed and has a higher order_no
        if ($completed && $stepType['order_no'] > $highestCompletedOrderNo) {
            $highestCompletedOrderNo = $stepType['order_no'];
            $highestCompletedId = $stepType['id'];
        }
    }
*/
    // Output the ID of the step_type with the highest order_no that is completed
    
    echo json_encode(['currentStep' => $contact['step_type_id']]);
?>