<?php
namespace Metayota;
include_once('../metayota/library.php');
include_once('../translation-service/translation-service.php');

class ContactService
{
    private $db;
    private $userId;
    private $data = [];
    
    public function __construct($useDb = null, $userId = null)
    {
        global $user_id;
        global $db;
        $this->db = $useDb ?? $db;
        $this->userId = $userId ?? $user_id;
    }
    
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }
    
    public function create()
    {
        $defaultContactType = getConfig('default_contact_type', 'prospective_customer');
        $contactType = $this->db->getByName('contact_type', $defaultContactType);
        
        $contactGroup = $this->db->getByName('contact_group', 'prospective_customer');
        $newlyAddedStepType = $this->db->getByName('step_type', 'newly_added');

        if (isset($this->data['partner_id'])) {
            $partner = $this->db->getById('partner', $this->data['partner_id']);
            $partnerID = $partner['id'];
            $userID = $partner['user_id'];
        } else {
            $partner = $this->db->get('partner', ['user_id'=>$this->userId]);
            $partnerID = $partner['id'];
            $userID = $partner['user_id'];
        }


        $contact = [
            'salutation' => $this->data['salutation'] ?? null,
            'company' => $this->data['company'] ?? null,
            'department' => $this->data['department'] ?? null,
            'full_name' => $this->getFullName(),
            'firstname' => $this->data['firstname'] ?? null,
            'lastname' => $this->data['lastname'] ?? null,
            'email' => $this->data['email'] ?? null,
            'website' => $this->data['website'] ?? null,
            'phone' => $this->data['phone'] ?? null,
            'street' => $this->data['street'] ?? null,
            'zip_code' => $this->data['zip_code'] ?? null,
            'city' => $this->data['city'] ?? null,
            'country' => $this->data['country'] ?? null,
            'language' => $this->data['language'] ?? null,
            'timezone' => $this->data['timezone'] ?? null,
            'contact_type_id' => $this->data['contact_type_id'] ?? $contactType['id'],
            'step_type_id' => $this->data['step_type_id'] ?? $newlyAddedStepType['id'],
            'partner_id' => $partnerID,
            'user_id' => $userID,
            'access_key' => $this->data['access_key'] ?? generateKey()
        ];
        
        $this->db->insert('contact', $contact);
        $contactId = $this->db->lastInsertId();
        
        // Handle contact groups
        if (!empty($this->data['contact_groups'])) {
            $this->updateContactGroups($contactId, $this->data['contact_groups']);
        } elseif (!empty($contactGroup)) {
            $this->db->insert('contact_group_member', [
                'contact_group_id' => $contactGroup['id'],
                'contact_id' => $contactId
            ]);
        }
        
        return $contactId;
    }
    
    public function update($id)
    {
        $existing = $this->getById($id);
        
        if (empty($existing)) {
            return false;
        }
        
        $updateData = [];
        
        $allowedFields = [
            'salutation', 'company', 'department', 'firstname', 'lastname', 
            'email', 'website', 'phone', 'street', 'zip_code', 'city', 
            'country', 'language', 'timezone', 'contact_type_id', 
            'step_type_id', 'partner_id', 'user_id', 'access_key'
        ];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $this->data)) {
                $updateData[$field] = $this->data[$field];
            }
        }
        
        // Handle full_name: only update if explicitly provided OR if related fields changed
        if (isset($this->data['full_name'])) {
            // Explicitly provided full_name takes precedence
            $updateData['full_name'] = $this->data['full_name'];
        } elseif (isset($this->data['firstname']) || isset($this->data['lastname']) || isset($this->data['company'])) {
            // Auto-generate full_name if firstname, lastname, or company changed
            $mergedData = array_merge($existing, $this->data);
            $this->data = $mergedData;
            $updateData['full_name'] = $this->getFullName();
        }
        
        if (empty($updateData)) {
            // Still handle contact groups even if no main fields updated
            if (isset($this->data['contact_groups'])) {
                $this->updateContactGroups($id, $this->data['contact_groups']);
            }
            return $id;
        }
        
        $this->db->update('contact', $updateData, ['id' => $id]);
        
        // Handle contact groups
        if (isset($this->data['contact_groups'])) {
            $this->updateContactGroups($id, $this->data['contact_groups']);
        }
        
        return $id;
    }
    
    public function getById($id)
    {
        $contact = $this->db->getById('contact', $id);
        
        return $contact;
    }
    
    public function getByEmail($email)
    {
        $contact = $this->db->get('contact', ['email' => $email]);
        
        return $contact;
    }
    
    public function delete($id)
    {
        $existing = $this->getById($id);
        
        if (empty($existing)) {
            return false;
        }
        
        // Delete contact group memberships first
        $this->db->delete('contact_group_member', ['contact_id' => $id]);
        
        // Delete the contact
        $this->db->delete('contact', ['id' => $id]);
        return true;
    }
    
    public function upsertByEmail()
    {
        if (empty($this->data['email'])) {
            return $this->create();
        }
        
        $existingContact = $this->getByEmail($this->data['email']);
        
        if (empty($existingContact)) {
            return $this->create();
        } else {
            return $this->update($existingContact['id']);
        }
    }
    
    private function getFullName()
    {
        // If full_name is explicitly provided, use it
        if (!empty($this->data['full_name'])) {
            return $this->data['full_name'];
        }
        
        // If company is set, use company name
        if (!empty($this->data['company'])) {
            return $this->data['company'];
        }
        
        // Otherwise construct from firstname and lastname
        $firstname = $this->data['firstname'] ?? '';
        $lastname = $this->data['lastname'] ?? '';
        
        return trim($firstname . ' ' . $lastname);
    }
    
    private function updateContactGroups($contactId, $groupNames)
    {
        if (!is_array($groupNames)) {
            return;
        }
        
        // Convert group names to group IDs
        $newGroupIds = [];
        foreach ($groupNames as $groupName) {
            $group = $this->db->getByName('contact_group', $groupName);
            if (!empty($group)) {
                $newGroupIds[] = $group['id'];
            }
        }
        
        // Get existing group memberships for this contact
        $existingMemberships = $this->db->getAll('contact_group_member', ['contact_id' => $contactId]);
        $existingGroupIds = array_column($existingMemberships, 'contact_group_id');
        
        // Determine which groups to add and which to remove
        $groupsToAdd = array_diff($newGroupIds, $existingGroupIds);
        $groupsToRemove = array_diff($existingGroupIds, $newGroupIds);
        
        // Remove memberships that are no longer needed
        foreach ($groupsToRemove as $groupId) {
            $this->db->delete('contact_group_member', [
                'contact_id' => $contactId,
                'contact_group_id' => $groupId
            ]);
        }
        
        // Add new memberships
        foreach ($groupsToAdd as $groupId) {
            $this->db->insert('contact_group_member', [
                'contact_group_id' => $groupId,
                'contact_id' => $contactId
            ]);
        }
    }
}


function generateKey() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomString = '';
    
    for ($i = 0; $i < 12; $i++) {
        $randomString .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    $formattedString = implode('-', str_split($randomString, 4));
    return urlencode($formattedString);
}

// Legacy function for backward compatibility
function createContact($contact) {
    $service = new ContactService();
    return $service->setData($contact)->create();
}
?>