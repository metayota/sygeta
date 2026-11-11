<?php 
namespace Metayota;
include_once('../metayota/library.php');
include_once('../translation-service/translation-service.php');

$sql = 'SELECT c.eu_compliance FROM company_settings cs LEFT JOIN country c ON c.country_code=cs.country';
$euCompliance = $db->getColumnByQuery($sql);

$legitimateInterestID = $db->getColumn('legal_basis', 'id', ['name'=>'legitimate_interest']);

echo json_encode(['default_language'=>$_GET['language'], 'eu_compliance'=> $euCompliance, 'legitimate_interest_id'=>$legitimateInterestID]);
?>