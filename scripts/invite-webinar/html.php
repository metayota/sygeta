<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../php-mailer/php-mailer.php');  ?><div class="invite-webinar">
    <div class="breadcrumbs">
        <a link="/list-events"><t>events</t></a> &raquo; <a link="/webinar-detail/{this.webinar_id}"><t>{this.webinar?.title_translated}</t></a> &raquo; <t>invite_contacts_to_webinar</t>
    </div>
  <h1><?= translate('invite_contacts_to_webinar') ?></h1>
  <p if="this.contacts.length === 0"><?= translate('no_uninvited_contacts') ?></p>
  <form if="this.contacts.length > 0" (submit)="this.sendInvitations()">
    <div for="contact of this.contacts">
        <div class="contact-checkbox">
            <form.checkbox label="{contact.full_name} ({contact.email})" (change)="this.toggleContact(contact.id,event)"></form.checkbox>
        </div>
    </div>
    <form.button (click)="this.sendInvitations()" label="<?= translate('send_invitations') ?>"></form.button>
  </form>
  <p if="this.invitationSent"><t>invitations_sent_successfully</t></p>
</div>