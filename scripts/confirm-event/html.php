<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-contact-sender/mail-contact-sender.php');  ?><div if="this.participant && this.event && this.contact">
    <div if="this.participant.status == 'confirmed'">
        <h1><?= translate('event_confirmation') ?></h1>
        <p><?= translate('thank_you_confirmed') ?></p>

        <div class="status-badge confirmed">
            <?= translate('status_confirmed') ?>
        </div>
    </div>

    <div if="this.participant.status == 'declined'">
        <h1><?= translate('event_declined') ?></h1>
        <p><?= translate('decline_message') ?></p>

        <div class="status-badge declined">
            <?= translate('status_declined') ?>
        </div>
    </div>

    <h2>{this.event.calendar_text}</h2>
    <p>{this.event.description}</p> 
    <p>
        <?= translate('event_start') ?>: {this.event.event_start}<br>
        <?= translate('event_end') ?>: {this.event.event_end}<br>
        <?= translate('event_venue') ?>: {this.event.venue}
    </p>

    <h2><?= translate('participant_info') ?></h2>
    <p>
        {this.contact.salutation} {this.contact.firstname} {this.contact.lastname}
    </p>

    <div if="this.event.conference_url">
        <h2><?= translate('online_access') ?></h2>
        <p><?= translate('conference_link') ?>: {this.event.conference_url}</p>
    </div>

</div>