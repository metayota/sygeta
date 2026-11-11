<?php namespace Metayota; include_once('../metayota/library.php');   
    $editAll = hasAccess('database',$data['reference_table']);
    $editOwned = !$editAll && hasAccess('db.table.edit.owned',$data['reference_table']);
    if ($editAll || $editOwned) {
        if (empty($data['own_id'])) {
            $tableQuoted = $db->quoteIdentifier($data['own_table']);
            $sql = "SELECT MAX(id) as max_id FROM ".$tableQuoted;
            $data['own_id'] = $db->getByQuery($sql)['max_id'];
        }
        
        $ownCol = $data['own_id_column'];
        $otherCol = $data['other_id_column'];
        
        // Hole bestehende Einträge für diese own_id
        $existingEntries = $db->getAll($data['reference_table'], [$ownCol => $data['own_id']]);
        $existingIds = array_column($existingEntries, $otherCol);
        
        $val = is_string($data['value']) ? json_decode($data['value']) : $data['value'];
        $newIds = $val;
        
        // Bestimme welche IDs hinzugefügt, entfernt oder beibehalten werden sollen
        if ($newIds == null) {
            $newIds = [];
        }
        if ($existingIds == null) {
            $existingIds = [];
        }
        $toAdd = array_diff($newIds, $existingIds);
        $toRemove = array_diff($existingIds, $newIds);
        
        // Entferne nicht mehr benötigte Einträge
        foreach($toRemove as $removeId) {
            $db->delete($data['reference_table'], [
                $ownCol => $data['own_id'], 
                $otherCol => $removeId
            ]);
        }
        
        // Füge neue Einträge hinzu
        foreach($toAdd as $itemID) {
            $newData = [$otherCol => $itemID, $ownCol => $data['own_id']];
            if ($editOwned) {
                $newData['user_id'] = $user_id;
            }
            $db->insert($data['reference_table'], $newData);
        }
        success();
    } else {
        http_response_code(403);
        echo json_encode(['message'=>'You have no access to the database table '.$data['reference_table'],'highlight'=>'db-list-data','resource'=>$data['reference_table']]);
        die();
    }
?>