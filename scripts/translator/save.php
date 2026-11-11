<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    if (getConfig('main_server')) {
        $db = $serverDB;
    }

    $translations = $data;
    foreach($translations as $translation) {
     //   if (isset($translation['changed'])) {
            if (!empty($translation['available-in-javascript']) && $translation['available-in-javascript']) {
                $db->insertIgnore('translation_area',['translation_key'=>$translation['translation_key'],'area'=>'client']);
            }
            $mainWhere = ['translation_key'=>$translation['translation_key'],'language'=>$translation['language']];
            $existingMainTranslation = $db->get('translation', $mainWhere);
            if (empty($existingMainTranslation)) {
                $mainWhere['translation'] = $translation['translation'];
                $db->insert('translation',$mainWhere);
            } else {
                $db->update('translation',['translation'=>$translation['translation']],$mainWhere);
            }

            if (!empty($translation['language_b'])) {
                $where = ['translation_key'=>$translation['translation_key'],'language'=>$translation['language_b']];
                $existing = $db->get('translation', $where);
                if (empty($existing)) {
                    $where['translation'] = $translation['translation_b'];
                    $db->insert('translation',$where);
                } else {
                    $db->update('translation',['translation'=>$translation['translation_b']],$where);
                }
            }
     //   }
    }
    msg('translation_added',[],'success');
?>