<?php 
    namespace Metayota;
    include_once('../metayota/library.php');
    include_once('../php-mailer/php-mailer.php');
    include_once('../translation-service/translation-service.php');
    include_once('../mail-contact-sender/mail-contact-sender.php');  
?><div class="register card">
    <h1><t>customer_registration_title</t></h1>
    <p><t>customer_registration_text</t></p>
    <p if="!this.hide_text" class="teaser-text"><?= translate('register_text') ?></p>
    <form.resource (submit)="this.registerUser(event)" resourcetype="register" submitLabel="<?= translate('register') ?>">
    </form.resource>
</div>