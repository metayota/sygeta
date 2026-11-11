<?php 
namespace Metayota;
include_once('../metayota/library.php');
include_once('../translation-service/translation-service.php');
include('../create-contact/lib.php');

requireAccess('db.table.edit.owned', 'contact');

$contactService = new ContactService($db, $user_id);
$contactId = $contactService->setData($data)->create();

if (!empty($data['legal_basis'])) {
    $legalBase = $data['legal_basis'];
    $now = date('Y-m-d H:i:s', time());
    $values = ['contact_id'=>$contactId, 'legal_basis_id'=>$legalBase['legal_basis_id'],'description'=>$legalBase['description'], 'source'=>$legalBase['source'], 'date'=>$now];
    $db->insert('contact_legal_basis', $values);
}

success('msg_contact_created');
?>