<?php
namespace Metayota;

include('../sygeta-lib/translations.php');
include('../contacts-lib/contacts-lib.php');

class ContactLegalBasisService {
    private $db;
    private $user_id;
    private $contact_id;
    private $data = [];
    
    public function __construct() {
        global $db, $user_id;
        $this->db = $db;
        $this->user_id = $user_id;
    }
    
    public function setContactId($contact_id) {
        $this->contact_id = $contact_id;
        return $this;
    }
    
    public function setData($data) {
        $this->data = $data;
        return $this;
    }
    
    
    /**
     * Get a specific legal basis by ID
     */
    public function getById($id, $contact_id) {
        return $this->db->get('contact_legal_basis', [
            'id' => $id,
            'contact_id' => $contact_id
        ]);
    }
    
    /**
     * Get all legal bases for a contact
     */
    public function getAllByContactId($contact_id) {
        global $language;

        $sql = 'SELECT 
        clb.*, 
        lb.name as legal_basis,
        t.translation as legal_basis_translated,
        tSource.translation as source,
        tDescription.translation as description

        FROM contact_legal_basis clb 
        
        LEFT JOIN legal_basis lb 
        ON lb.id = clb.legal_basis_id
        
        LEFT JOIN translation t
        ON lb.title = t.translation_key
        AND t.language = :language

        LEFT JOIN translation tSource
        ON clb.source = tSource.translation_key
        AND tSource.language=:language

        LEFT JOIN translation tDescription
        ON clb.description = tDescription.translation_key
        AND tDescription.language=:language

        WHERE clb.contact_id=:contact_id';

        $legalBasisList = $this->db->getAllByQuery($sql, ['contact_id'=>$contact_id, 'language'=>$language]);
        foreach($legalBasisList as &$legalBasis) {
            $legalBasis['date_formatted'] = formatDatetimeForUser($legalBasis['date']);
        }
        return $legalBasisList;
    }
    
    /**
     * Create a new legal basis entry
     */
    public function create() {
        global $language;

        $source = null;
        $description = null;

        if (!empty($this->data['source'])) {
            $source = insertTranslationWithRandomCode('legal_base_source', $this->data['source']);
        }
        if (!empty($this->data['description'])) {
            $description = insertTranslationWithRandomCode('legal_base_description', $this->data['description']);
        }
        
        $insertData = [
            'contact_id' => $this->contact_id,
            'source' => $source,
            'legal_basis_id' => $this->data['legal_basis_id'] ?? null,
            'date' => $this->data['date'] ?? date('Y-m-d H:i:s'),
            'description' => $description
        ];
        
        $this->db->insert('contact_legal_basis', $insertData);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update an existing legal basis entry
     */
    public function update($id) {
        
        // Verify the legal basis exists for this contact
        $existing = $this->getById($id, $this->contact_id);
        if (empty($existing)) {
            return false;
        }
        
        $updateData = [];
        
        if (isset($this->data['source'])) {
            $updateData['source'] = $this->data['source'];
        }
        
        if (isset($this->data['legal_basis_id'])) {
            $updateData['legal_basis_id'] = $this->data['legal_basis_id'];
        }

        if (isset($this->data['legal_basis'])) {
            $updateData['legal_basis_id'] = $this->db->getColumnByName('legal_basis', 'id', $this->data['legal_basis']);
        }
        
        if (isset($this->data['date'])) {
            $updateData['date'] = $this->data['date'];
        }
        
        if (isset($this->data['description'])) {
            $updateData['description'] = $this->data['description'];
        }
        
        if (empty($updateData)) {
            return $id;
        }
        
        $this->db->update('contact_legal_basis', $updateData, [
            'id' => $id,
            'contact_id' => $this->contact_id
        ]);
        
        return $id;
    }
    
    /**
     * Delete a legal basis entry
     */
    public function delete($id, $contact_id) {     
        // Verify the legal basis exists for this contact
        $existing = $this->getById($id, $contact_id, false);
        if (empty($existing)) {
            return false;
        }
        
        $this->db->delete('contact_legal_basis', [
            'id' => $id,
            'contact_id' => $contact_id
        ]);
        
        return true;
    }
}
?>