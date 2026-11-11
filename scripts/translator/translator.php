<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    if (getConfig('main_server')) {
        $db = $serverDB;
    }

    $page = ($data['page']-1) * 1;
    $limit = $page * 15;
    if (str_contains($data['search'],'key:')) {
        $searchString = '';
        $searchKey = trim(str_replace('key:','',$data['search']));
    } else {
        $searchString = trim($data['search']);
        $searchKey = null;
    }

    $search = '%'.$searchString.'%';
    $count = !empty($data['count']);
    $limitQuery = $count ? '' : " LIMIT $limit, 15";
    
    if (empty($data['language_b'])) {
        $select = $count ? "count(*) as count" : "*";
        $fn = $count ? 'getByQuery' : 'getAllByQuery';
        $searchKeyWhere = !empty($searchKey) ? " AND (translation_key='$searchKey' ) " : "";
        $result = $db->$fn("SELECT $select FROM translation WHERE language=:language_a AND (translation LIKE :search  OR translation_key LIKE :search) $searchKeyWhere ORDER BY translation $limitQuery",['language_a'=>$data['language_a'],'search'=>$search]);
    } else {
        $select = $count ? "count(*) as count" : "ta.*, tb.translation as translation_b, tb.language as language_b";
        $fn = $count ? 'getByQuery' : 'getAllByQuery';
        $searchKeyWhere = !empty($searchKey) ? " AND (ta.translation_key='$searchKey' OR tb.translation_key='$searchKey' ) " : "";
        $result = $db->$fn("SELECT $select FROM translation ta LEFT JOIN translation tb ON tb.translation_key = ta.translation_key AND tb.language=:language_b WHERE ta.language=:language_a AND (ta.translation LIKE :search OR tb.translation LIKE :search OR ta.translation_key LIKE :search) $searchKeyWhere ORDER BY ta.translation $limitQuery",['language_a'=>$data['language_a'], 'language_b'=>$data['language_b'],'search'=>$search]);
    }
    echo json_encode($result);
?>