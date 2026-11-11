<?php
    namespace Metayota;
    include_once('../metayota/library.php');


    header('Content-Type: application/xml; charset=utf-8');

    $baseMailAccount = $db->getByQuery("SELECT * FROM email_account WHERE user_id IS NULL");

    // Mail server configuration (always e2.metayota.com)
    $imapHost = $baseMailAccount['imap_host'];
    $imapPort = $baseMailAccount['imap_port'];
    $smtpHost = $baseMailAccount['smtp_host'];
    $smtpPort = $baseMailAccount['smtp_port'];

?><?xml version="1.0" encoding="utf-8"?>
<Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006">
    <Response xmlns="http://schemas.microsoft.com/exchange/autodiscover/outlook/responseschema/2006a">
        <Account>
            <AccountType>email</AccountType>
            <Action>settings</Action>
            <Protocol>
                <Type>IMAP</Type>
                <Server><?= $imapHost ?></Server>
                <Port><?= $imapPort  ?></Port>
                <DomainRequired>off</DomainRequired>
                <LoginName>%EMAILADDRESS%</LoginName>
                <SPA>off</SPA>
                <SSL>on</SSL>
                <AuthRequired>on</AuthRequired>
                <UsePOPAuth>off</UsePOPAuth>
                <SMTPLast>off</SMTPLast>
            </Protocol>
            <Protocol>
                <Type>SMTP</Type>
                <Server><?= $smtpHost ?></Server>
                <Port><?= $smtpPort  ?></Port>
                <DomainRequired>off</DomainRequired>
                <LoginName>%EMAILADDRESS%</LoginName>
                <SPA>off</SPA>
                <Encryption>TLS</Encryption>
                <AuthRequired>on</AuthRequired>
            </Protocol>
        </Account>
    </Response>
</Autodiscover>