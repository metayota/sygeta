<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../invite-functions/invite-functions.php');  

require_once '../../vendor/autoload.php';

// Specify your OAuth client ID

$client_id = getConfig('google_oauth_client_id');

// Get the ID token from the request
$id_token = $data['id_token'];

$client = new Google_Client(['client_id' => $client_id]);
$payload = $client->verifyIdToken($id_token);

if ($payload) {
    $userid = $payload['sub'];
    $email = $payload['email'];

    $user = $db->get('user',['google_id'=>$userid]);

    if (empty($user)) {
        $user = $db->get('user',['email'=>$email]);
    }
    if (!empty($user)) {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        if ($user['status'] != 'locked') {
            $_SESSION['loggedInUser'] = $user;
            
            $lastLogin = date("Y-m-d H:i:s");
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            // 'status'=>'active', email is not automatically verified?? UNLESS IT'S THE SAME LIKE GOOGLE?
            $udateUserData = ['google_id'=>$userid, 'login_ip'=>$_SERVER['REMOTE_ADDR'], 'login_useragent'=>$_SERVER['HTTP_USER_AGENT'], 'language' => translate('language_code'), 'last_login'=>$lastLogin];
            if ($user['email'] == $email) {
                $udateUserData['status'] = 'active';
            }
            $db->update('user',$udateUserData,['id'=>$user_id]);
            

        }
    } else {

        $useUsername = $payload['given_name'];
        $existingUsername = $db->get('user',['username'=>$useUsername]);
        
        if (!empty($existingUsername)) {
            do {
                $useUsername = $payload['given_name'].rand(0,10000);
            } while( !empty($db->get('user',['username'=>$useUsername])) );
        }
        $user = ['email'=>$email,'firstname'=>$payload['given_name'],'username'=>$useUsername, 'status'=>'active', 'registered_with_google' => 1, 'google_id' => $userid,'usergroup_id'=>4, 'registration_ip' => $_SERVER['REMOTE_ADDR'],'language' => translate('language_code')];
        $db->insert('user', $user);
        $user_id = $db->connection->lastInsertId();
        $user['id'] = $user_id;
        $_SESSION['loggedInUser'] = $user;
        $materials = $db->getAll('material');
        foreach($materials as $material) {
            $db->insert('user_material', ['user_id' => $user_id, 'material_id'=>$material['id'], 'total_production'=>0,'total_consumption'=>0,'total_production_consumption'=>0, 'total_storage'=>$material['min_storage'], 'material_amount'=>$material['start_amount']]);
        }

        $invite_id = null;
        if (!empty($data['invite_id'])) {
            $invite_id = $data['invite_id'];
        } else if (!empty($_COOKIE['invite_id'])) {
            $invite_id = $_COOKIE['invite_id'];
        } else {
            $invitationClick = $db->get('invitation_click',['ip_address'=>$_SERVER['REMOTE_ADDR']]);
            if (!empty($invitationClick)) {
                $invite_id = $invitationClick['invite_id'];
            }
        }
        if (!empty($invite_id)) {
            $inviter_id = stringToNumber($invite_id);
            $db->insert('successful_invitation',['user_id_inviter'=>$inviter_id,'user_id_invitee'=>$user_id]);
            claimBonus('successful_invitation',$user_id);
            claimBonus('successful_invitation',$inviter_id);
        }

    }
  
    http_response_code(200);
    success();
} else {
    http_response_code(401);
    echo 'ID token verification failed';
}

?>