<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  

    

    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!empty($get['access_key'])) {
     //   var_dump($get['access_key']);
        $contact = $db->get('contact', ['access_key'=>$get['access_key']]);
        
     //   var_dump($contact);
        if (!empty($contact)) {
            $user = $db->get('user', ['contact_id'=>$contact['id']]);
            if (!empty($user)) {
                $_SESSION['loggedInUser'] = $user;
            } else {
                // Create transient user...?
                $usergroupRow = $db->getByName('usergroup', 'customer');
             //   var_dump($usergroupRow);die('user?');
                $loggedInUser = ['id'=>0, 'username'=>$contact['full_name'],'usergroup_id'=>$usergroupRow['id'], 'contact_id'=>$contact['id'],'virtual_user'=>true];
                $_SESSION['loggedInUser'] = $loggedInUser;
                $GLOBALS['loggedInUser'] = $loggedInUser;
            }

            header('Location: '.$get['redirect']);
        }
        die('access key is set');
    }

    $username =$data['username'];
    if (!empty($username)) {
        $result = $db->fetchQuery("SELECT * FROM user WHERE (username=:username OR email=:username) AND status='active'",array('username' => $username));
      
        if (!empty($result) && password_verify($data['password'],$result['password_hash'])) {
            $user_id = $result['id'];
            if (!empty($data['remember_me'])) {
                $token = substr(bin2hex(random_bytes(64)),0,64);
                $lastLogin = date("Y-m-d H:i:s");
                $userAgent = $_SERVER['HTTP_USER_AGENT'];
                $db->insert('remember_me',array('user_id'=>$user_id,'token'=>$token,'last_login'=>$lastLogin,'user_agent'=>$userAgent));
                $expires30days = time() + 3600 * 24  * 30;
                setcookie ('token', $token, $expires30days, '/', '', false, true );
            }
            $userRow = $db->getByQuery("SELECT u.*,c.firstname,c.lastname FROM user u LEFT join contact c ON u.contact_id=c.id WHERE u.id=:user_id",['user_id'=>$user_id]);
            $_SESSION['loggedInUser'] = $userRow;
            echo json_encode($_SESSION['loggedInUser']);
            die();
        }
    } 
        
    errorMsg(translate('incorrect_login_data'));    
    
?>