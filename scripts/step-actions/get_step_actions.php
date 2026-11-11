<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    require_once('../step-actions/lib.php');
    include_once('../access-check/access-check.php');
    checkReadPermissions('contact', $data['contact_id']);

    $contact = $db->getById('contact', $data['contact_id']);
    $customer = $db->get('customer', ['contact_id' => $contact['id']]);

    $baseReplacements = ['contact_id' => $contact['id']];
    if (!empty($customer)) {
        $baseReplacements['customer_id'] = $customer['id'];
    }

    // Alle step_types laden (ohne hidden-Filter, da für step_actions irrelevant)
    $stepTypeSQL = "SELECT st.*, stt.sql_template FROM step_type st
                    LEFT JOIN step_type_template stt ON st.step_type_template = stt.name
                    ORDER BY st.order_no";
    $stepTypes = $db->getAllByQuery($stepTypeSQL, []);

    // Hilfsfunktion: Ist ein step_type abgeschlossen?
    function isStepCompleted($stepType, $baseReplacements, $db) {
        if (empty($stepType['sql_template'])) {
            return false;
        }
        $allReplacements = $baseReplacements;
        if (!empty($stepType['step_type_template_params'])) {
            $allReplacements = array_merge(json_decode($stepType['step_type_template_params'], true), $allReplacements);
        }
        try {
            $conditionSql = replacePlaceholders($stepType['sql_template'], $allReplacements);
            $where = extractNamedParameters($conditionSql, $allReplacements);
            if (allNamedParametersFilled($conditionSql, $where)) {
                $result = $db->getByQuery($conditionSql, $where);
                return !empty($result);
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    // Hilfsfunktion: step_action verarbeiten (Replacements, Template-Felder befüllen)
    function prepareStepAction($stepAction, $baseReplacements, $db) {
        $stepAction['title_translated'] = translate($stepAction['title']);
        $allReplacements = $baseReplacements;
        if (!empty($stepAction['parameters'])) {
            $allReplacements = array_merge(json_decode($stepAction['parameters'], true), $allReplacements);
        }
        $stepActionTemplate = $db->getByName('step_action_template', $stepAction['step_action_template']);
        $stepAction['link']          = replacePlaceholders($stepActionTemplate['link'], $allReplacements);
        $stepAction['condition_sql'] = replacePlaceholders($stepActionTemplate['condition_sql'], $allReplacements);
        $stepAction['resource']      = replacePlaceholders($stepActionTemplate['resource'], $allReplacements);
        $stepAction['action']        = replacePlaceholders($stepActionTemplate['action'], $allReplacements);
        $stepAction['success_sql']   = replacePlaceholders($stepActionTemplate['success_sql'], $allReplacements);
        $stepAction['_replacements'] = $allReplacements;
        return $stepAction;
    }

    // Hilfsfunktion: Prüft success_sql / condition_sql und gibt zurück ob Aktion sichtbar
    function checkSqlVisibility($stepAction, $db) {
        $allReplacements = $stepAction['_replacements'];

        if (!empty($stepAction['success_sql']) && !preg_match('/\{[a-zA-Z0-9_]+\}/', $stepAction['success_sql'])) {
            $where = extractNamedParameters($stepAction['success_sql'], $allReplacements);
            $result = $db->getByQuery($stepAction['success_sql'], $where);
            return !$result;
        } elseif (!empty($stepAction['condition_sql']) && !preg_match('/\{[a-zA-Z0-9_]+\}/', $stepAction['condition_sql'])) {
            $where = extractNamedParameters($stepAction['condition_sql'], $allReplacements);
            $result = $db->getByQuery($stepAction['condition_sql'], $where);
            return (bool)$result;
        }
        return true;
    }

    // -------------------------------------------------------------------------
    // step_type des current_step und dessen order_no ermitteln
    // -------------------------------------------------------------------------
    $currentStepType = null;
    foreach ($stepTypes as $st) {
        if ($st['id'] == $data['current_step']) {
            $currentStepType = $st;
            break;
        }
    }
    $currentOrderNo = $currentStepType ? (int)$currentStepType['order_no'] : null;

    $visibleStepActions = [];

    // =========================================================================
    // 1) step_just_finished
    //    Aktionen des current_step, wenn dieser abgeschlossen ist
    // =========================================================================
    if ($currentStepType && isStepCompleted($currentStepType, $baseReplacements, $db)) {
        $actions = $db->getAllByQuery(
            "SELECT * FROM step_action WHERE step_type_id = :step_type_id AND display_condition = 'step_just_finished'",
            ['step_type_id' => $data['current_step']]
        );
        foreach ($actions as $stepAction) {
            $stepAction = prepareStepAction($stepAction, $baseReplacements, $db);
            if (checkSqlVisibility($stepAction, $db)) {
                $visibleStepActions[] = $stepAction;
            }
        }
    }

    // =========================================================================
    // 2) step_finished
    //    Alle step_actions mit display_condition = step_finished laden,
    //    dann pro zugehörigem step_type prüfen ob dieser abgeschlossen ist
    // =========================================================================
    $allStepFinishedActions = $db->getAllByQuery(
        "SELECT * FROM step_action WHERE display_condition = 'step_finished'",
        []
    );

    // step_types für schnellen Zugriff per ID indexieren
    $stepTypeById = [];
    foreach ($stepTypes as $st) {
        $stepTypeById[$st['id']] = $st;
    }

    //echo "doing action:";
    //var_dump($allStepFinishedActions);
    foreach ($allStepFinishedActions as $stepAction) {
        $relatedStepType = $stepTypeById[$stepAction['step_type_id']] ?? null;
        if (!$relatedStepType) {
      //      echo "related step not found";
            continue;
        }

        if (!isStepCompleted($relatedStepType, $baseReplacements, $db)) {
        //    echo "step is not completed";
            continue;
        }

        $stepAction = prepareStepAction($stepAction, $baseReplacements, $db);
        if (checkSqlVisibility($stepAction, $db)) {
            $visibleStepActions[] = $stepAction;
        } else {
     //       echo "sql visibility not";
        }
    }

    // =========================================================================
    // 3) step_next_in_queue
    //    Nächster step nach current_step (kleinste order_no > currentOrderNo)
    //    Aktionen dieses Steps mit display_condition = step_next_in_queue laden
    // =========================================================================
    if ($currentOrderNo !== null) {
        $nextStepType = null;
        foreach ($stepTypes as $st) {
            $orderNo = (int)$st['order_no'];
            if ($orderNo > $currentOrderNo) {
                if ($nextStepType === null || $orderNo < (int)$nextStepType['order_no']) {
                    $nextStepType = $st;
                }
            }
        }

        if ($nextStepType) {
            $actions = $db->getAllByQuery(
                "SELECT * FROM step_action WHERE step_type_id = :step_type_id AND display_condition = 'step_next_in_queue'",
                ['step_type_id' => $nextStepType['id']]
            );
            foreach ($actions as $stepAction) {
                $stepAction = prepareStepAction($stepAction, $baseReplacements, $db);
                if (checkSqlVisibility($stepAction, $db)) {
                    $visibleStepActions[] = $stepAction;
                }
            }
        }
    }

    // _replacements ist nur intern, nicht nach aussen geben
    foreach ($visibleStepActions as &$sa) {
        unset($sa['_replacements']);
    }

    echo json_encode($visibleStepActions);
?>