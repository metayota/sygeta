<?php
namespace Metayota;
include('../metayota/library.php');
include('../create-contact/lib.php');

$method = $_SERVER['REQUEST_METHOD'];
$contactService = new ContactService($db, $user_id);

switch ($method) {
    case 'GET':
        requireAccess('db.table.get_all', 'contact_group');
        
        $sql = "SELECT cg.name, t.translation as title FROM contact_group cg LEFT JOIN translation t ON t.translation_key=cg.title AND t.language=:language";
        $contactGroups = $db->getAllByQuery($sql, ['language'=>$language]);
        
        jsonResponse($contactGroups);
        
        break;

}

?>