<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-contact-sender/mail-contact-sender.php');   include_once('../translation-service/translation-service.php');  

// Assume $db is your DB connection object
include('../step-actions/lib.php');
// Hole alle Step Types mit Bedingung, sortiert nach steigender order_no
$stepTypes = $db->getAllByQuery("SELECT st.*,stt.sql_template FROM step_type st LEFT JOIN step_type_template stt ON st.step_type_template = stt.name ORDER BY order_no ASC");

// Hole alle Kontakte
if (!empty($data['contact_id'])) {
    $contacts = $db->getAllByQuery("SELECT id FROM contact WHERE id=:contact_id",['contact_id'=>$data['contact_id']]);
} else {
    $contacts = $db->getAllByQuery("SELECT id FROM contact");
}

foreach ($contacts as $contact) {
    $contactId = (int)$contact['id'];
    $highestStepType = null;
    $highestOrderNo = -1;

    $customer = $db->get('customer', ['contact_id'=>$contactId]);

    foreach ($stepTypes as $stepType) {
        $conditionSql = $stepType['sql_template'];
        if (!empty($conditionSql)) {
            if (!empty($stepType['step_type_template_params'])) {
                $allReplacements = json_decode($stepType['step_type_template_params'], true);
            } else {
                $allReplacements = [];
            }
            $allReplacements['contact_id'] = $contact['id'];
            if (!empty($customer)) {
                $allReplacements['customer_id'] = $customer['id'];
            }

            // First: Replace {variables} with actual values
            $conditionSql = replacePlaceholders($conditionSql, $allReplacements);

            // Second: Extract named parameters for the SQL query
            $where = extractNamedParameters($conditionSql, $allReplacements);

            // Execute the query with named parameters
            if ( allNamedParametersFilled($conditionSql, $where) ) {
                $result = $db->getAllByQuery($conditionSql, $where);
            } else {
                $result = false;
            }
            if (!empty($result)) {
                // Wenn die order_no höher ist als bisherige, speichere sie
                if ((int)$stepType['order_no'] > $highestOrderNo) {
                    $highestStepType = $stepType;
                    $highestOrderNo = (int)$stepType['order_no'];
                }
            }
        }
    }

    // Wenn wir einen gültigen höchsten Step gefunden haben → update contact
    if ($highestStepType) {
        $stepTypeId = (int)$highestStepType['id'];
        $db->update('contact', ['step_type_id' => $stepTypeId], ['id' => $contactId]);
    }
}

echo "Cronjob finished: contact.step_type_id updated.\n";

?>