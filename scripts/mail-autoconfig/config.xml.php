<?php
    namespace Metayota;
    include_once('../metayota/library.php');

    header('Content-Type: application/xml; charset=utf-8');

    // Extract email from GET parameter
    $email = isset($_GET['emailaddress']) ? htmlspecialchars($_GET['emailaddress']) : '';

    // Extract domain from email or fallback to server hostname
    $domain = '';
    if ($email && strpos($email, '@') !== false) {
    $domain = substr($email, strpos($email, '@') + 1);
    } elseif (isset($_SERVER['HTTP_HOST'])) {
    $domain = $_SERVER['HTTP_HOST'];
    if (strpos($domain, 'www.') === 0) {
        $domain = substr($domain, 4);
    }
    } else {
    $domain = 'example.com';
    }

    // Generate display names from domain
    $domainParts = explode('.', $domain);
    $displayShortName = ucfirst($domainParts[0]);
    $displayName = $displayShortName . ' Mail';

    // Default email if none provided
    if (empty($email)) {
        $email = 'user@' . $domain;
    }

    $baseMailAccount = $db->getByQuery("SELECT * FROM email_account WHERE user_id IS NULL");

    // Mail server configuration (always e2.metayota.com)
    $imapHost = $baseMailAccount['imap_host'];
    $imapPort = $baseMailAccount['imap_port'];
    $smtpHost = $baseMailAccount['smtp_host'];
    $smtpPort = $baseMailAccount['smtp_port'];
?>
<?xml version="1.0" encoding="UTF-8"?>
<clientConfig version="1.1">
 <emailProvider id="<?= $domain ?>">
   <domain><?= $domain ?></domain>
   <displayName><?= $displayName ?></displayName>
   <displayShortName><?= $displayShortName ?></displayShortName>
   <version>2025-10-01</version>
   <incomingServer type="imap">
     <hostname><?= $imapHost ?></hostname>
     <port><?= $imapPort ?></port>
     <socketType>SSL</socketType>
     <authentication>password-cleartext</authentication>
     <username><?= $email ?></username>
   </incomingServer>
   <outgoingServer type="smtp">
     <hostname><?= $smtpHost ?></hostname>
     <port><?= $smtpPort ?></port>
     <socketType>SSL</socketType>
     <authentication>password-cleartext</authentication>
     <username><?= $email ?></username>
   </outgoingServer>
 </emailProvider>
</clientConfig>