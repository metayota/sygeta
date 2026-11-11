<?php namespace Metayota; include_once('../metayota/library.php');  
$partnerID = $db->getColumn('partner','id',['user_id'=>$user_id]);

$transactions = [];

// Get commissions
$commissions = $db->getAll('booked_commission', ['partner_id' => $partnerID]);
foreach ($commissions as $commission) {
    $transactions[] = [
        'amount' => '+' . number_format($commission['commission_amount'], 2),
        'reason' => 'Commission',
        'date' => $commission['datetime'],
        'type' => 'commission'
    ];
}

// Get payouts
$payouts = $db->getAll('payout', ['partner_id' => $partnerID]);
foreach ($payouts as $payout) {
    $transactions[] = [
        'amount' => '-' . number_format($payout['amount'], 2),
        'reason' => 'Payout',
        'date' => $payout['datetime'],
        'type' => 'payout'
    ];
}

// Sort by date (newest first)
usort($transactions, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

echo json_encode($transactions);
?>