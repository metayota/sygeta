<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-contact-sender/mail-contact-sender.php');  

include('../event-reminder-mails/lib.php'); 
// Run the reminder system
echo "Starting event reminder system...\n";
sendEventReminders();
echo "Event reminder system completed.\n";

?>