<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../php-mailer/php-mailer.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-contact-sender/mail-contact-sender.php');  ?><div class="register card">
    <h1><?= translate('partner_registration_title') ?></h1>
    <p if="!this.hide_text" class="teaser-text"><?= translate('partner_registration_text') ?></p>
    <form.resource element="partnerRegistrationEl" value="{this.invitedUser}" (submit)="this.registerUser(event)" resourcetype="partner-registration" submitLabel="<?= translate('register') ?>">
    </form.resource>
</div>