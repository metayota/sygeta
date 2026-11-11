<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
if (!empty($get['usergroup_id'])) {
    sendCacheHeaders();
} else {
    $usergroup = empty($_SESSION['loggedInUser']) ?  1 : $_SESSION['loggedInUser']['usergroup_id']; 
}

    $sql = "SELECT mi.*, t.translation as title_translated 
            FROM page mi 
            LEFT JOIN translation t ON t.translation_key = mi.title AND t.language = :language 
            INNER JOIN menu_usergroup mug ON mug.page_id = mi.id AND mug.usergroup_id = :usergroup
            WHERE mi.parent_page IS NULL AND mi.hide_in_navigation != 1
            ORDER BY 
            CASE 
                WHEN mi.show_first = 1 THEN 0   -- Always first
                WHEN mi.show_last = 1 THEN 2    -- Always last
                ELSE 1                          -- Normal position
            END,
            t.translation ASC";
    $menuItems = $db->getAllByQuery($sql,['language'=>$get['language'],'usergroup'=>$get['usergroup_id']]);
    echo json_encode($menuItems);

?>