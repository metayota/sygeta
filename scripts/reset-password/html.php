<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../user-lib/user-lib.php');  ?><div class="page reset-password">
    <h1>
        <?= translate('set_new_password_title') ?>
    </h1>
    <p>
        <?= translate('set_new_password_description') ?>
    </p>


    <section class="content-row">
        <form.resource element="resetForm" submitlabel="<?= translate('set_new_password') ?>" (submit)="this.resetPw(event)" resourcetype="reset-password"></form.resource>
    </section>

    <p>
        <?= translate('troubles_logging_in') ?>
    </p>
    <p>
        <a link="/verify-mail"><?= translate('resend_registration_mail') ?></a>
    </p>
    <p>
        <a link="/forgot-password"><?= translate('reset_password') ?></a>
    </p>
</div>