<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-helper/mail-helper.php');   include_once('../translation-service/translation-service.php');   include_once('../invite-functions/invite-functions.php');  ?><div class="event-invitation">
    <h1>
        <t>event_invitation_title</t>
    </h1>
    <h2>{this.event?.title_translated}</h2>
    {this.event?.description_translated}
    <p><b>
            <t>event_participation_terms</t>
        </b></p>
    <h3>
        <t>next_event_dates</t>
    </h3>
    <ul for="date of this.event?.dates">
        <li><b>{date.start_date_formatted}</b> bis <b>{date.end_time_formatted}</b></li>
    </ul>
    <h2>
        <t>register_event_title</t>
    </h2>
    <div if="!this.partner_code">

        <div if="!this.confirmed">
            <button (click)="this.confirmParticipation()" class="form-button">
                <t>confirm_event_participation</t>
            </button>
        </div>
    </div>
    <div if="this.partner_code && !this.confirmed">
        <form.resource resource_name="event-invitation" (submit)="this.registerForWebinar(event)"
            submitlabel="<?= htmlentities(translate('register_event_button')) ?>"></form.resource>
    </div>
    <div if="this.confirmed">
        <p class="event-success"><b>&check; <t>event_confirmed_text</t></b></p>
        <p><b>
                <t>link_for_video_stream</t>
            </b></p>
        <ul for="date of this.event?.dates">
            <li><b>{date.start_date_formatted}</b>
                <t>to</t> <b>{date.end_time_formatted}</b><br /><a href="{date.event_link}"
                    target="_blank">{date.event_link}</a>
            </li>
        </ul>
    </div>
</div>