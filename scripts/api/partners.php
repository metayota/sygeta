<?php
namespace Metayota;
include('../metayota/library.php');
include('../create-contact/lib.php');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        requireAccess('db.table.get_all', 'partner');
        
        $sql = "SELECT p.id, p.user_id, p.full_name, p.contact_id, p.supervisor_partner_id, t.translation as role_name, p.commission 
                FROM partner p 
                LEFT JOIN translation t ON t.translation_key = p.role_name AND t.language = :language";
        $partners = $db->getAllByQuery($sql, ['language' => $language]);
        
        jsonResponse($partners);
        
        break;
}

?>