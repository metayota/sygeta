<?php

namespace Metayota;

include_once('../metayota/library.php');


include_once('../api/contact-legal-bases-lib.php');



include_once('../access-check/access-check.php');

$method = $_SERVER['REQUEST_METHOD'];
$contactLegalBasisService = new ContactLegalBasisService();

switch ($method) {
    case 'GET':
        checkReadPermissions('contact', $get['contact_id']);
        
        if (empty($get['contact_id'])) {
            error('msg_missing_parameter', 'contact_id');
        }
        
        // Check if requesting a specific legal basis
        if (!empty($get['id'])) {
            $legalBasis = $contactLegalBasisService->getById($get['id'], $get['contact_id'], true);
            
            if (empty($legalBasis)) {
                notFound('Contact Legal Basis');
            } else {
                jsonResponse($legalBasis);
            }
        } else {
            // Get all legal bases for the contact
            $legalBases = $contactLegalBasisService->getAllByContactId($get['contact_id'], true);
            jsonResponse($legalBases);
        }
        break;
        
    case 'POST':
        checkWritePermissions('contact', $get['contact_id']);
        
        if (empty($get['contact_id'])) {
            error('msg_missing_parameter', 'contact_id');
        }
        
        $legalBasisId = $contactLegalBasisService
            ->setContactId($get['contact_id'])
            ->setData($data)
            ->create(true);
        
        jsonResponse([
            'id' => $legalBasisId,
            'message' => 'Legal basis created successfully'
        ]);
        break;
        
    case 'PATCH':
        checkWritePermissions('contact', $get['contact_id']);
        
        if (empty($get['contact_id'])) {
            error('msg_missing_parameter', 'contact_id');
        }
        
        if (empty($get['id'])) {
            error('msg_missing_parameter', 'id');
        }
        
        $legalBasisId = $contactLegalBasisService
            ->setContactId($get['contact_id'])
            ->setData($data)
            ->update($get['id'], true);
        
        if ($legalBasisId === false) {
            notFound('Contact Legal Basis');
        } else {
            jsonResponse([
                'id' => $legalBasisId,
                'message' => 'Legal basis updated successfully'
            ]);
        }
        break;
        
    case 'DELETE':
        checkWritePermissions('contact', $get['contact_id']);
        
        if (empty($get['contact_id'])) {
            error('msg_missing_parameter', 'contact_id');
        }
        
        if (empty($get['id'])) {
            error('msg_missing_parameter', 'id');
        }
        
        $deleted = $contactLegalBasisService->delete($get['id'], $get['contact_id'], true);
        
        if (!$deleted) {
            notFound('Contact Legal Basis');
        } else {
            jsonResponse([
                'message' => 'Legal basis deleted successfully'
            ]);
        }
        break;
        
    default:
        error('msg_method_not_allowed');
}
?>