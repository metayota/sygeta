<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div if="!loggedInUser$.username">
    <p>
        <b><?= translate('troubles_logging_in') ?></b>
    </p>
    <p>
        <a link="/forgot-password"><?= translate('reset_password') ?></a>
    </p>
    <p>
        <a link="/verify-mail"><?= translate('resend_registration_mail') ?></a>
    </p>
</div>