<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
$db->query("SET FOREIGN_KEY_CHECKS = 0;");
$db->query("TRUNCATE TABLE bulk_mailing");
$db->query("TRUNCATE TABLE bulk_mailing_receiver");
$db->query("TRUNCATE TABLE calendar_event");
$db->query("TRUNCATE TABLE calendar_event_participant");
$db->query("TRUNCATE TABLE clicks");
$db->query("TRUNCATE TABLE contact_form");
$db->query("TRUNCATE TABLE customer");
$db->query("TRUNCATE TABLE contact_activity");
$db->query("TRUNCATE TABLE customer_history");
$db->query("TRUNCATE TABLE document");
$db->query("TRUNCATE TABLE documents_confirmed");
$db->query("TRUNCATE TABLE email_account");




$db->query("TRUNCATE TABLE email_cache");
$db->query("TRUNCATE TABLE error");
$db->query("TRUNCATE TABLE newsletter");
$db->query("TRUNCATE TABLE newsletter_receiver");
$db->query("TRUNCATE TABLE contact_group_member");
$db->query("TRUNCATE TABLE bank_account");

$db->query("TRUNCATE TABLE line_diagram");
$db->query("TRUNCATE TABLE line_diagram_item");
$db->query("TRUNCATE TABLE line_diagram_series");
$db->query("TRUNCATE TABLE lost_deal");
$db->query("TRUNCATE TABLE mail_message");
$db->query("TRUNCATE TABLE news");
$db->query("TRUNCATE TABLE app_invitation");
$db->query("TRUNCATE TABLE meeting");
$db->query("TRUNCATE TABLE meeting_invitation");
$db->query("TRUNCATE TABLE meeting_participant");
$db->query("TRUNCATE TABLE partner"); 
$db->query("TRUNCATE TABLE partner_availability");

$db->query("TRUNCATE TABLE user");
$hash = password_hash($data['admin_password'], PASSWORD_DEFAULT);

$db->query('INSERT INTO `user` (`id`, `username`, `email`, `verification_code`, `forgot_token`, `password_hash`, `usergroup_id`, `status`, `register_date`, `contact_id`, `picture`) VALUES (1, \'admin\', :admin_mail, NULL, NULL, :password_hash, 3, \'active\', \'2024-11-27 13:52:00\', NULL, \'\');',['admin_mail'=>$data['admin_mail'], 'password_hash'=>$hash]);


$db->query("TRUNCATE TABLE contact");
$db->query("INSERT INTO `partner` (`id`, `user_id`, `full_name`, `commission`, `contact_id`, `supervisor_partner_id`, `role_name`) VALUES (1, 1, 'Administrator', NULL, 1, NULL, NULL);");
$db->query("INSERT INTO `contact` (`id`, `salutation`, `firstname`, `lastname`, `email`, `website`, `phone`, `street`, `company`, `full_name`, `language`, `user_id`, `partner_id`, `zip_code`, `city`, `contact_type_id`, `step_type_id`, `department`, `timezone`, `access_key`, `country`) VALUES (1, 'mister', 'Administrator', '-', 'support01@sygeta.com', NULL, NULL, NULL, NULL, 'Administrator', 'en', 1, NULL, NULL, NULL, 2, NULL, NULL, 'Europe/Berlin', NULL, NULL);");


$db->query("TRUNCATE TABLE remember_me");
$db->query("TRUNCATE TABLE reminder");
$db->query("TRUNCATE TABLE sales_document");
$db->query("TRUNCATE TABLE sales_document_item");
$db->query("TRUNCATE TABLE self_disclosure");
$db->query("TRUNCATE TABLE closed_deal");
$db->query("TRUNCATE TABLE shortlink");
$db->query("TRUNCATE TABLE task");
$db->query("TRUNCATE TABLE transaction");
$db->query("TRUNCATE TABLE event");
$db->query("TRUNCATE TABLE event_date");
$db->query("TRUNCATE TABLE event_invitation");
$db->query("TRUNCATE TABLE event_participant");

if ($_SERVER['HTTP_HOST'] == 'app.futurplus.de') {
    $db->query("TRUNCATE TABLE master_data");
    $db->query("TRUNCATE TABLE master_data_nationality");

    $db->query("TRUNCATE TABLE property");
    $db->query("INSERT INTO `property` (`id`, `address`, `city`, `price`, `bedrooms`, `bathrooms`, `description`, `property_type`, `listing_type`, `area`, `location`, `image_url`, `contact_info`, `listing_url`, `shower_and_tub`, `year_built`, `final_energy_demand`, `detail_html`, `immo_nr`, `energy_certificate`, `main_energy_source`, `energy_efficiency_class`, `owner`, `house_condition`, `zip_code`, `region_id`, `lot_size`, `living_area`, `total_area`, `latitude`, `longitude`, `map_title`, `map_text`, `partner_id`, `customer_buyer_id`, `travel_directions`) VALUES (1, 'Lindenallee 100', 'city_79jp7vbi', 750000, 3, 2, 'property_description_433xcmd6', 1, 'abc', 1120, 'suburban', 'sample.jpg', 'contact_info_contact_info_tczha1zt', '', 1, 1994, 100, '<p><strong>abc</strong></p>', '12', '', 12, 'A+', '', 'Gut', '23421', 3, 1000, 119, 150, 52.6018000000, 13, 'map_title_map_title_zivsvwwh', 'map_text_lsnm98gn', NULL, NULL, NULL);");


    $db->query("TRUNCATE TABLE property_picture");
    $db->query("INSERT INTO `property_picture` (`id`, `image`, `title`, `property_id`, `description`, `second_image`) VALUES (1, 'kitchen-01.jpg', 'image_title_title_ifcehf9h', 1, 'description_dq3h0clr', NULL), (2, 'living-room-01.jpg', 'image_title_title_u2mwk257', 1, 'description_xcnp3uin', 'living-room-02.jpg');");

    $db->query("INSERT INTO `email_account` (`id`, `user_id`, `email`, `imap_host`, `imap_port`, `imap_username`, `imap_password`, `use_ssl`, `smtp_host`, `smtp_port`, `from_name`, `reply_to_email`, `reply_to_name`) VALUES (1, NULL, 'info@app.futurplus.de', 'e2.metayota.com', 993, 'info@app.futurplus.de', 'VWY09TDI70FB60JJDLN9', 1, 'e2.metayota.com', '587', 'Futurplus', 'info@app.futurplus.de', 'Futurplus');");
    $db->query("INSERT INTO `event` (`id`, `title`, `description`, `name`, `event_type`, `preparation_time`, `post_buffer_time`, `event_link`) VALUES (1, 'webinar_title_title_immobilien_als_kapitalanlage', '', 'immobilien-als-kapitalanlage', 'online', NULL, NULL, '');");
    $db->query("INSERT INTO `event_date` (`id`, `start_date`, `end_date`, `event_id`) VALUES (1, '2025-12-20 15:00:00', '2025-12-20 16:00:00', 1);");


    $db->query("
    INSERT INTO `line_diagram` (`id`, `title`, `description`, `created_at`, `updated_at`, `property_id`) VALUES
    (1, 'title_rnjj7mt4', 'description_9jcamd3x', '2025-10-06 15:47:13', NULL, 1),
    (2, 'title_hjmslazg', 'description_y7gnffqk', '2025-10-06 16:03:32', NULL, 1);
    ");

    $db->query("
    INSERT INTO `line_diagram_item` (`id`, `diagram_id`, `x_value`, `y_value`, `created_at`, `series_id`) VALUES
    (1, 1, 2005, 3.39, NULL, 1),
    (2, 1, 2008, 3.43, NULL, 1),
    (3, 1, 2011, 3.50, NULL, 1),
    (4, 1, 2014, 3.52, NULL, 1),
    (5, 1, 2017, 3.71, NULL, 1),
    (6, 1, 2020, 3.77, NULL, 1),
    (7, 1, 2023, 3.85, NULL, 1),
    (8, 2, 2005, 1500.00, NULL, 3),
    (9, 2, 2008, 1600.00, NULL, 3),
    (10, 2, 2011, 2100.00, NULL, 3),
    (11, 2, 2014, 2900.00, NULL, 3),
    (12, 2, 2017, 4200.00, NULL, 3),
    (13, 2, 2020, 5400.00, NULL, 3),
    (14, 2, 2023, 5800.00, NULL, 3);
    ");
    $db->query("
    INSERT INTO `line_diagram_series` (`id`, `title`, `diagram_id`) VALUES
    (1, 'title_mtrfbrqr', 1),
    (3, 'title_7wcdek0n', 2);
    ");
}

$db->query("SET FOREIGN_KEY_CHECKS = 1;");


// Define folders and their exceptions
$foldersConfig = [
    '../../uploads/document/' => [],
    '../../uploads/logos/' => ['Logo-Futurplus.png'],
    '../../uploads/profile-pictures/' => [],
    '../../uploads/property-detail-pictures/' => ['kitchen-01.jpg', 'living-room-01.jpg', 'living-room-02.jpg'],
    '../../uploads/property-pdf/' => [],
    '../../uploads/customer-documents/' => [],
    './../uploads/properties/' => ['sample.jpg'],
    '../../uploads/self-disclosures/' => []
];

$results = [];

foreach ($foldersConfig as $folder => $exceptions) {
    // Check if folder exists
    if (!is_dir($folder)) {
        $results[$folder] = "Folder does not exist - skipped";
        continue;
    }
    
    // Get all files in the folder
    $files = array_diff(scandir($folder), ['.', '..']);
    
    $deletedCount = 0;
    $skippedCount = 0;
    
    foreach ($files as $file) {
        $filePath = $folder . $file;
        
        // Skip if it's a directory
        if (is_dir($filePath)) {
            continue;
        }
        
        // Skip if file is in exceptions list
        if (in_array($file, $exceptions)) {
            $skippedCount++;
            continue;
        }
        
        // Delete the file
        if (unlink($filePath)) {
            $deletedCount++;
        }
    }
    

}
success('msg_success_reset_crm');
?>