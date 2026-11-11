<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../send-mail/send-mail.php');   include_once('../translation-service/translation-service.php');  ?><div class="activate-mail box-middle card">
    <h1>
        <?= translate('activate_mail_title') ?>
    </h1>
    <p>
        <?= translate('activate_mail_description') ?>
    </p>
    <form.resource submitlabel="<?= translate('activate_mail_btn') ?>" (submit)="this.resend(event)" resourcetype="verify-mail"></form.resource>
    <?= callAction('login-problems','html') ?>
</div>