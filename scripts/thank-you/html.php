<?php namespace Metayota; include_once('../metayota/library.php');  ?><div class="thank-you">
    <h1>
        <?= translate('thank_you_header'); ?>
    </h1>
    <p>
        <?= translate('email_verification_request'); ?>
    </p>
    <p>
        <?= translate('email_verification_instructions'); ?>
    </p>
    <p>
        <b><?= translate('activation_mail_missing'); ?></b>
    </p>
    <p>
        <a link="/verify-mail"><?= translate('resend_verification_mail_link'); ?></a>
    </p>
</div>