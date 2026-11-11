<?php
namespace Metayota;
include_once('../track-link/lib.php');
include_once('../metayota/library.php'); // your DB class

// Get the code from URL
$code = $_GET['t'] ?? '';

if (!$code) {
    http_response_code(400);
    echo "Invalid tracking link.";
    exit;
}

// Decode the tracking_id and contact_id
list($trackingId, $contactId) = TrackingLink::decode($code);

// Fetch target URL from tracking_link table
$row = $db->getById('tracking_link', $trackingId);

if (!$row || empty($row['target_url'])) {
    http_response_code(404);
    echo "Link not found.";
    exit;
}

$targetUrl = $row['target_url'];

// Log the click
$db->insertIgnore('tracking_click', [
    'tracking_link_id' => $trackingId,
    'contact_id'  => $contactId,
    'click_time'  => date('Y-m-d H:i:s')
]);

// Redirect to target URL
header("Location: $targetUrl");
exit;

?>