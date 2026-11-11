<?php namespace Metayota; include_once('../metayota/library.php');  
function numberToString($number) {
    // Generate a 3-digit random number, padding with zeros if necessary
    $randNum = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

    // Generate a last digit that never ends with zero
    $lastDigit = rand(1, 9);

    $number = $number * 1000000 + intval($randNum . $lastDigit);
    $number = strrev(strval($number)); // Reverse number 1234 = 4321

    // Convert to base 36 (includes numbers and letters)
    return base_convert($number, 10, 36);
}

function stringToNumber($string) {
    // Convert back from base 36 to base 10
    $number = base_convert($string, 36, 10);

    $number = strrev(strval($number)); // Reverse the number again
    $number = intval(substr($number, 0, -6)); // remove the last 4 digits (random number)

    return $number;
}

function getInviteLink($user_id) {
    return 'https://www.alterworld.de/join/'.numberToString($user_id);
}

function importContacts($contacts, $user_id) {
    global $db;
    foreach($contacts as $contact) {
        $contact['user_id'] = $user_id;
        if (!empty($contact['mail_address'])) {
            $existingWhere = ['user_id'=>$user_id,'mail_address'=>$contact['mail_address']];
            $existingContact = $db->get('contact',$existingWhere);
            if (empty($existingContact)) {
                $db->insert('contact', $contact);
            } else {
                $contact['firstname'] = empty($contact['firstname']) ? $existingContact['firstname'] : $contact['firstname'];
                $contact['lastname'] = empty($contact['lastname']) ? $existingContact['lastname'] : $contact['lastname'];
                $contact['company'] = empty($contact['company']) ? $existingContact['company'] : $contact['company'];
                $contact['deleted'] = 0;
                $db->update('contact',$contact,$existingWhere);
            }
        }
    }
}

function getInviteSubject($version, $user_id, $contact=null) {
    global $db;
    $user = $db->getById('user',$user_id);
    $link = getInviteLink($user_id);
    $variables = ['firstname'=>$user['firstname'], 'lastname' => $user['lastname'], 'username'=>$user['username'],'link'=>$link];
    return translate('mail_invite_subject_'.$version, $variables);
}


function claimBonus($event_type, $user_id,$goldBought=0) {
    if (empty($user_id)) {
        return;
    }
    global $db;
    $bonuses = $db->getAll('invite_bonus', ['event_type'=>$event_type]);
    foreach($bonuses as $bonus) {
        if ($bonus['goldmark']) {
            $db->query("UPDATE user SET gold = (gold+:add_gold) WHERE id=:user_id",['user_id'=>$user_id,'add_gold'=>$bonus['goldmark']]);
            $db->insert('gold_transaction',['user_id_receiver'=>$user_id,'gold'=>$bonus['goldmark'],'transaction_type'=>'invitation_bonus']);
        }
        if ($bonus['material_id'] && $bonus['material_amount']) {
            $db->query('UPDATE user_material SET material_amount=material_amount+:material_amount, recalculate=1 WHERE user_id=:user_id AND material_id=:material_id',['material_id'=>$bonus['material_id'],'material_amount'=>$bonus['material_amount'],'user_id'=>$user_id]);
        }
        if (!empty($bonus['purchase_percentage']) && $bonus['purchase_percentage'] > 0) {
            $goldBonus = $goldBought / 100 * $bonus['purchase_percentage'];
            if (!empty($bonus['max_gold'])) {
                if ($goldBonus > $bonus['max_gold']) {
                    $goldBonus = $bonus['max_gold'];
                }
            }
            $db->query("UPDATE user SET gold = (gold+:add_gold) WHERE id=:user_id",['user_id'=>$user_id,'add_gold'=>$goldBonus]);
            $db->insert('gold_transaction',['user_id_receiver'=>$user_id,'gold'=>$goldBonus,'transaction_type'=>'invitation_bonus']);
        }
    }
}

function getInviteText($version, $user_id, $contact=null) {
    global $db;
    $salutationKey = 'salutation';
    
    $user = $db->getById('user',$user_id);
    $link = getInviteLink($user_id);
    $variables = ['firstname'=>$user['firstname'], 'lastname' => $user['lastname'], 'username'=>$user['username'],'link'=>$link];
    $variables['firstname_available'] = empty($contact) || empty($contact['firstname']) ? 'no_firstname' : 'with_firstname';
    $variables['lastname_available'] = empty($contact) || empty($contact['lastname']) ? 'no_lastname' : 'with_lastname';
    $variables['gender'] = empty($contact) || empty($contact['gender']) ? 'neutral' : $contact['gender'];
    $variables['contact_firstname'] = empty($contact) || empty($contact['firstname']) ? '' : $contact['firstname'];
    $variables['contact_lastname'] = empty($contact) || empty($contact['lastname']) ? '' : $contact['lastname'];
    return translate('mail_invite_text_'.$version, $variables);
}

function handleInviteBonus($user_id_invitee,$invite_id) {
    // first check if the user has a cookie
    $succesfulInvitation = $db->get('successful_invitations',['user_id_invitee' => $user_id_invitee]);
    if (!empty($succesfulInvitation)) {
        return; // Already claimed bonus
    }
    $inviteID = $invite_id;
    if (empty($inviteID)) {
        $inviteID = $_COOKIE['invite_id'];
    }
    if (empty($inviteID)) {
        $ip = $_SERVER['REMOTE_ADDR'];
      
    }
    $db->insert('successful_invitations', []);
}
?>