<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    $sql = "SELECT st.id as value,t.translation as name FROM `step_type` st LEFT JOIN translation t ON t.translation_key = st.title AND t.language=:language ORDER BY st.order_no";
    $stepTypes = $db->getAllByQuery($sql,['language'=>$GLOBALS['language']]);
  
  //  $newlyAdded = ['name'=>translate('newly_added_contacts'), 'value'=>null];
///    array_unshift($stepTypes,$newlyAdded);
    echo json_encode($stepTypes);
?>