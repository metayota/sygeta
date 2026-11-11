<?php namespace Metayota; include_once('../metayota/library.php');   
    $partner = $db->get('partner',['user_id'=>$user_id]);
    $partnerID = $partner['id'];

    if (empty($partnerID)) {
        $customers = $db->getByQuery("SELECT count(*) as count FROM customer c");
    } else {
        $customers = $db->getByQuery("SELECT count(*) as count FROM customer c WHERE c.user_id=:user_id",['user_id'=>$user_id]);
    }

    $eventInvitations = $db->getByQuery("SELECT count(*) as count FROM event_invitation li LEFT JOIN contact c ON li.contact_id = c.id AND c.user_id=:user_id",['user_id'=>$user_id]); // li.invitation_date
    
    $transactions = $db->getByQuery("SELECT sum(amount) as total_amount FROM transaction WHERE user_id=:user_id",['user_id'=>$user_id]);
    $positiveTransactions = $db->getByQuery("SELECT sum(commission_amount) as total_amount FROM booked_commission WHERE partner_id=:partner_id",['partner_id'=>$partnerID]);
    
    // Add payout query
    $payouts = $db->getByQuery("SELECT sum(amount) as total_amount FROM payout WHERE partner_id=:partner_id",['partner_id'=>$partnerID]);

    $mailsSentSQL = "SELECT 
    COUNT(mm.id) as email_count
    FROM partner p
    JOIN contact c ON c.partner_id = p.id
    JOIN mail_message mm ON mm.receiver_contact_id = c.id
    WHERE p.id = :partner_id -- specify partner_id if needed
    GROUP BY p.id
    ORDER BY email_count DESC;";
    $mailsSent = $db->getByQuery($mailsSentSQL, ['partner_id'=>$partnerID]);

    $salesActivities = $db->getByQuery(
        "SELECT COUNT(*) as count FROM contact_activity WHERE partner_id = :partner_id",
        ['partner_id' => $partnerID]
    );

    $contactCount = $db->getByQuery(
        "SELECT COUNT(*) as count FROM contact WHERE partner_id = :partner_id",
        ['partner_id' => $partnerID]
    );

    $meetingsCount = $db->getByQuery(
        "SELECT COUNT(*) as count FROM meeting_participant WHERE contact_id = :contact_id",
        ['contact_id' => $partner['contact_id']]
    );

    $result['meetings_count'] = $meetingsCount['count'];
    $result['contact_count'] = $contactCount['count'];
    
    $result['mails_sent'] = $mailsSent['email_count'];
    $result['sales_activity_count'] = $salesActivities['count'];

    $result['invitation_count'] = $eventInvitations['count'];
    $result['customer_count'] = $customers['count'];
    
    // Calculate account balance: commissions - payouts
    $result['account_balance'] = ($positiveTransactions['total_amount'] ?? 0) - ($payouts['total_amount'] ?? 0);
    $result['total_income'] = $positiveTransactions['total_amount'];
    echo json_encode($result);
?>