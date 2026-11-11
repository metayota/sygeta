<?php namespace Metayota; include_once('../metayota/library.php');  

function handleStripePayment($user_id, $amount, $currency, $payment) {
    // You can overwrite this code with your own implementation
    global $db;
    $db->query("UPDATE account SET value=value+:add WHERE user_id=:user_id",['user_id'=>$user_id,'add'=>$amount]);
}

?>