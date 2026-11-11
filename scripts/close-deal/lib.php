<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-contact-sender/mail-contact-sender.php');  
include_once('../contacts-lib/contacts-lib.php');

function bookDeal($closedDealId) {
    global $db;
    
    // Prüfen ob Deal bereits gebucht wurde
    $existingBookings = $db->getAll('booked_commission', array('closed_deal_id' => $closedDealId));
    if (!empty($existingBookings)) {
        return array(
            'success' => false,
            'message' => translate('error_ui_refresh'),
            'error_code' => 'ALREADY_BOOKED'
        );
    }
    
    // Deal Details abrufen
    $deal = $db->getById('closed_deal', $closedDealId);
    if (!$deal) {
        return array(
            'success' => false,
            'message' => translate('error_ui_refresh'),
            'error_code' => 'DEAL_NOT_FOUND'
        );
    }

    if ($deal['booked'] == 1) {
        die();
    }

    
    // Deal Status auf "booked" setzen
    $bookDate = date('Y-m-d H:i:s');
    $db->update('closed_deal', 
        array('booked' => true, 'book_date' => $bookDate),
        array('id' => $closedDealId)
    );

    $customer = $db->getById('customer', $deal['customer_id']);
    
    // Kommissionen berechnen
    $commissionData = calculateCommissionsForDeal($closedDealId);
    
    if (empty($commissionData['commissions'])) {
        return array(
            'success' => false,
            'message' => translate('no_commissions_to_book'),
            'error_code' => 'NO_COMMISSIONS'
        );
    }
    
    $bookedCommissions = array();

    $decimalSeparator = translate('number_format_decimal_separator');
    $groupSeparator = translate('number_format_group_separator');
    
    // Alle Kommissionen buchen
    foreach ($commissionData['commissions'] as $commission) {
        $currencyRow = $db->get('currency', ['currency_code'=>$commissionData['currency']]);

        if (!empty($commission['partner_id'])) {
            $partner = $db->getById('partner', $commission['partner_id']);
            $contact = $db->getById('contact', $partner['contact_id']);
            $contactID = $contact['id'];
        } else if (!empty($commission['contact_id'])) {
            $contact = $db->getById('contact',$commission['contact_id']);
            $contactID = $contact['id'];
        }

        $bookingData = array(
            'closed_deal_id' => $closedDealId,
            'partner_id' => $commission['partner_id'],
            'contact_id' => $contactID,
            'total_deal_amount' => number_format($commissionData['deal_amount'], 2, $decimalSeparator, $groupSeparator),
            'level' => $commission['level'],
            'commission_percent' => number_format($commission['commission_rate'], 2, $decimalSeparator, $groupSeparator),
            'commission_amount' => number_format($commission['commission_amount'], 2, $decimalSeparator, $groupSeparator),
            'book_date' => $bookDate,
            'currency' => $commissionData['currency']
        );
        
        $db->insert('booked_commission', $bookingData);
        $bookingData['book_date_formatted'] = formatDatetimeForContact($bookDate, $contact);
        $bookingData['currency_name'] = translateLanguage($contact['language'], $currencyRow['currency_name']);
        $bookingData['currency_symbol'] = $currencyRow['currency_symbol'];
        $bookingData['deal_title'] = translate($deal['title']);
        $bookingData['customer_name'] = $customer['full_name'];
        $bookedCommissions[] = $bookingData;
    }

    foreach($bookedCommissions as $bookedCommission) {
        //try {
            $sender = new MailContactSender();
            $sender->addContact($bookedCommission['contact_id']);
            $sender->setTemplate('commission_booked',$bookedCommission);
            $sender->send();
        //} catch(\Exception $e) {

        //} 
    }
    
    return array(
        'success' => true,
        'message' => translate('deal_booked_successfully'),
        'booked_commissions' => $bookedCommissions,
        'total_commission' => $commissionData['total_commission'],
        'deal_amount' => $commissionData['deal_amount'],
        'currency' => $commissionData['currency'],
        'book_date' => $bookDate
    );
}


function calculateCommissionsForDeal($dealId) {
    global $db;
    
    $deal = $db->getById('closed_deal', $dealId);
    $customer = $db->getById('customer',$deal['customer_id']);
    $commissionModel = $db->getById('commission_model', $deal['commission_model']);
    
    $dealAmount = floatval($deal['deal_amount']);
    $partnerId = $deal['sales_representative_partner_id'];
    $currency = $deal['currency'] ?: 'EUR';
    
    $levels = array($commissionModel['sale_level_1'], $commissionModel['sale_level_2'], 
                   $commissionModel['sale_level_3'], $commissionModel['sale_level_4'], 
                   $commissionModel['sale_level_5']);
    
    $commissions = array();
    $totalCommission = 0;
    $currentPartnerId = $partnerId;
    $level = 1;

    $decimalSeparator = translate('number_format_decimal_separator');
    $groupSeparator = translate('number_format_group_separator');
    
    while ($currentPartnerId && $level <= 5) {
        $partner = $db->getById('partner', $currentPartnerId);
        $contact = $db->getById('contact', $partner['contact_id']);
        if ($commissionModel['fixed_partner_commission']) {
            $commissionRate = floatval($partner['commission']);
        } else {
            $commissionRate = floatval($levels[$level - 1]);
        }
        
        if ($commissionRate > 0) {
            $commissionAmount = round(($commissionRate / 100) * $dealAmount, 2);
            $totalCommission += $commissionAmount;
            
            $commissions[] = array(
                'level' => $level,
                'partner_id' => $currentPartnerId,
                'contact_id' => $partner['contact_id'],
                'partner_name' => $partner['full_name'],
                'email' => $contact['email'],
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'commission_amount_formatted' => number_format($commissionAmount, 2, $decimalSeparator, $groupSeparator)
            );
        }
        
        $currentPartnerId = $partner['supervisor_partner_id'];
        $level++;
    }

    $deal['title_translated'] = translate($deal['title']);
    return array(
        'customer' => $customer,
        'deal' => $deal,
        'deal_amount' => $dealAmount,
        'deal_amount_formatted' => number_format($dealAmount, 2, $decimalSeparator, $groupSeparator),
        'currency' => $currency,
        'total_commission' => round($totalCommission, 2),
        'total_commission_formatted' => number_format($totalCommission, 2, $decimalSeparator, $groupSeparator),
        'total_commission_percentage' => round(($totalCommission / $dealAmount) * 100, 2),
        'commissions' => $commissions
    );
}

?>