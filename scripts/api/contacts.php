<?php
namespace Metayota;
include_once('../metayota/library.php');
include_once('../create-contact/lib.php');
include_once('../access-check/access-check.php');
include_once('../api/lib.php');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}


$method = $_SERVER['REQUEST_METHOD'];
$contactService = new ContactService($db, $user_id);

switch ($method) {
    case 'GET':
        
        if (empty($get['id'])) {
            error('msg_missing_parameter', 'id');
        }
        
        $contact = $contactService->getById($get['id'], true);
        
        if (empty($contact)) {
            notFound('Contact');
        } else {
            checkWritePermissions('contact', $get['id']);
            jsonResponse($contact);
        }
        break;
        
    case 'POST':

        if (hasAccess('database',$table)) {
            if (isset($data['partner_id'])) {
                 $userID = $db->getColumnById('partner', 'user_id', $data['partner_id']);
                 $data['user_id'] = $userID;
            }
            // User has access
        } else if (hasAccess('db.table.edit.owned',$table)) {
            $partnerID = $db->getColumn('partner', 'id', ['user_id'=>$user_id]);

            $data['partner_id'] = $partnerID;
            $data['user_id'] = $user_id;
        } else {
            requireAccess('db.table.edit.owned',$table);
        }
        
        $contactId = $contactService->setData($data)->create();
        
        jsonResponse([
            'id' => $contactId,
            'message' => 'Contact created successfully'
        ]);
        break;
        
    case 'PATCH':
        
        
        if (empty($get['id'])) {
            error('msg_missing_parameter', 'id');
        }

        checkWritePermissions('contact', $get['id']);
        
        $contactId = $contactService->setData($data)->update($get['id'], true);
        
        if ($contactId === false) {
            notFound('Contact');
        } else {
            jsonResponse([
                'id' => $contactId,
                'message' => 'Contact updated successfully'
            ]);
        }
        break;
        
    case 'PUT':
        checkWritePermissions('contact', $get['id']);
        
        $contactId = $contactService->setData($data)->upsertByEmail();
        
        jsonResponse([
            'id' => $contactId,
            'message' => 'Contact saved successfully'
        ]);
        break;
        
    case 'DELETE':
        
        
        if (empty($get['id'])) {
            error('msg_missing_parameter', 'id');
        }

        checkWritePermissions('contact', $get['id']);
        
        $deleted = $contactService->delete($get['id'], true);
        
        if (!$deleted) {
            notFound('Contact');
        } else {
            jsonResponse([
                'message' => 'Contact deleted successfully'
            ]);
        }
        break;
        
    default:
        error('msg_method_not_allowed');
}
?>