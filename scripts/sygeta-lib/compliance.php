<?php 

function gdprEnabled() {
    $sql = 'SELECT c.eu_compliance FROM company_settings cs LEFT JOIN country c ON c.country_code=cs.country';
    $euCompliance = $db->getColumnByQuery($sql);
    return ($euCompliance == true);
}

function insertLegalBasis($contactID, $legalBasisName, $source, $description) {
    global $db;
    $legalBasisID = $db->getColumnByName('legal_basis', 'id', $legalBasisName);
    $db->insert('contact_legal_basis', [
        'contact_id' => $contactID,
        'source' => $source,
        'legal_basis_id' => $legalBasisID,
        'description' => $description,
        'date' => date('Y-m-d H:i:s')
    ]);
}

?>