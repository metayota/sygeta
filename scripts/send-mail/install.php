<?php namespace Metayota; include_once('../metayota/library.php');  
// Change directory two levels up
chdir(__DIR__ . "/../..");

// Run the Composer command to install PHPMailer
$output = shell_exec("composer require phpmailer/phpmailer");

?>