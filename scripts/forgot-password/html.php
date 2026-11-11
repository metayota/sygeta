<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../send-mail/send-mail.php');   include_once('../translation-service/translation-service.php');  ?><div class="forgot-password box-middle">
    <h1>
        <?= translate('forgot_password_title') ?>
    </h1>
    <p>
        <?= translate('forgot_password_text') ?>
    </p>
    <form.resource element="forgot" submitlabel="<?= translate('forgot_password_button') ?>" (submit)="this.forgotPassword(event)" resourcetype="forgot-password"></form.resource>

    <?= callAction('login-problems','html') ?>
</div>