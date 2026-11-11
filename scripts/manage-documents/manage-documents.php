<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
if (empty($data['customer_id']) || !is_numeric($data['customer_id'])) {
    $contactID = $_SESSION['loggedInUser']['contact_id'];
} else {
    $customer = $db->getById('customer',$data['customer_id']);
    $partner = $db->getById('partner',$customer['partner_id']);
    $contactID = $customer['contact_id'];
    if ($partner['user_id'] == $user_id || hasAccess('customer_documents')) { // is the contact's partner
        $userID = $customer['user_id'];
        $user = $db->getById('user',$userID);
        $usergroupID = $user['usergroup_id'];
    } else {
        die('No Access / METAYOTA912');
    }
}

$sql = "SELECT DISTINCT dt.*, dc.id as category_id, dc.name as category_name, dc.title as category_title
        FROM document_type dt
        INNER JOIN document_category dc ON dt.document_category = dc.name
        INNER JOIN document_type_contact_group dtcg ON dt.name = dtcg.document_type
        INNER JOIN contact_group cg ON dtcg.contact_group = cg.name
        INNER JOIN contact_group_member cgm ON cg.id = cgm.contact_group_id
        WHERE cgm.contact_id = :contact_id
        ORDER BY dc.name, dt.title";

$suitableTypes = $db->getAllByQuery($sql, ['contact_id'=>$contactID]);
$categories = [];

foreach ($suitableTypes as $type) {
    $documentList = $db->getAll('document', ['contact_id'=>$contactID, 'document_type_id' => $type['id']]);
    $count = count($documentList);
    
    $documentItem = [
        'document_type_id' => $type['id'],
        'number_of_documents' => $count,
        'documents' => $documentList,
        'done' => $count > 0,
        'document_name' => $type['title'],
        'document_name_translated' => translate($type['title'])
    ];
    
    // Find or create category
    $categoryId = $type['category_id'];
    $categoryIndex = -1;
    
    for ($i = 0; $i < count($categories); $i++) {
        if ($categories[$i]['id'] == $categoryId) {
            $categoryIndex = $i;
            break;
        }
    }
    
    if ($categoryIndex === -1) {
        $categories[] = [
            'id' => $type['category_id'],
            'name' => $type['category_name'],
            'title' => translate($type['category_title']),
            'documents' => []
        ];
        $categoryIndex = count($categories) - 1;
    }
    
    $categories[$categoryIndex]['documents'][] = $documentItem;
}

echo json_encode($categories);
?>