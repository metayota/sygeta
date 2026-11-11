<?php
namespace Metayota;
include('../metayota/library.php');
include('../create-contact/lib.php');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        requireAccess('db.table.get_all', 'legal_basis');
        
        $sql = "SELECT lb.name, t.translation as title, t2.translation as description FROM legal_basis lb LEFT JOIN translation t ON t.translation_key=lb.title AND t.language=:language LEFT JOIN translation t2 ON t2.translation_key=lb.description AND t2.language=:language";
        $legalBases = $db->getAllByQuery($sql, ['language'=>$language]);
        
        jsonResponse($legalBases);
        
        break;

}

?>