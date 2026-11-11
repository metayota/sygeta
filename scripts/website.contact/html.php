<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../send-mail/send-mail.php');   include_once('../translation-service/translation-service.php');  ?><div class="card">
    <h1><?= translate('contact_title') ?></h1>
    <?= translate('contact_description') ?>
    <div class="website-contact">
        <div if="!this.submitted">
            <form.resource icon="envelope" element="resForm" (submit)="this.sendMail(event)" submitlabel="<?= translate('submit') ?>" resourcetype="website.contact" autocomplete="off"></form.resource>
        </div>
        <div if="!!this.submitted">
            <p><b><?= translate('msg_sent_thank_you') ?></b></p>
        </div>
    </div>
</div>