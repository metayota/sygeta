<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   

$ugroup =$get['usergroup_id'];
//sendCacheHeaders();
$menuItem = $db->getByName('page', $get['page']);
if (!empty($menuItem)) {

    $parentPage = $db->getByName('page', $menuItem['parent_page']);
    $siblings = $db->getAllByQuery(
        'SELECT p.*, t.translation as title_translated,
            CASE WHEN p.parent_page IS NULL THEN 0 ELSE 1 END AS sort_order
        FROM page p 
        LEFT JOIN translation t ON p.title = t.translation_key AND t.language = :language 
        INNER JOIN menu_usergroup mug ON mug.page_id = p.id AND mug.usergroup_id = :usergroup
        WHERE p.hide_in_navigation != 1 AND (
            p.parent_page = :parent_page 
            OR p.parent_page = :page_name 
            OR p.name = :page_name 
            OR p.name = :parent_page
        )
        ORDER BY sort_order, t.translation',
        [
            'language' => $get['language'],
            'usergroup' => $ugroup,
            'parent_page' => $parentPage['name'],
            'page_name' => $menuItem['name']
        ]
    );

    $menuItem['siblings'] = $siblings;

    $menuItem['resourcetype'] = json_decode($menuItem['resourcetype']);
    $menuItem['page'] = $menuItem;
    $menuItem['title_translated'] = translate($menuItem['title']);

    echo json_encode($menuItem);
} else {
    echo json_encode(['status'=>'not_found']);
}

?>