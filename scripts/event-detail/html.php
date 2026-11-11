<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../invite-functions/invite-functions.php');  ?><div class="mdc-card event-detail">
    <div class="breadcrumbs">
        <a link="/list-events">
            <t>events</t>
        </a> &raquo; <t>{this.event?.title_translated}</t>
    </div>
    <h1 class="mdc-typography--headline4">{this.event?.title_translated}</h1>

    <p class="mdc-typography--body2">{this.event?.description_translated}</p>
    <h3>
        <t>next_event_dates</t>
    </h3>
    <ul for="date of this.event?.dates">
        <li><b>{date.start_date_formatted}</b> <t>date_from_to</t> <b>{date.end_time_formatted}</b> - <a target="_blank" href="{date.event_link}">{date.event_link}</a></li>
    </ul>
    <h3>{this.event?.participants?.count} <t>event_participants</t></h3>
    <ul for="participant of this.event?.participants">
        <li>{participant.firstname} {participant.lastname}</li>
    </ul>
    <p if="">this.event?.participants</p>
    <div class="button-box">
        <button link="/invite-event/{this.event?.id}" class="form-button">
            <?= translate('invite_for_event') ?>
        </button>
    </div>
    <h3><t>personal_invitation_link</t></h3>
    <p><t>personal_invitation_link_desc</t></p>
    <div class="button-box invitation-link-box">
        <input (click)="this.copyPartnerLink(node)" type="text" value="{this.partnerLink}" spellcheck="false" readonly /><button class="form-button" (click)="this.fetchPartnerLink()"><t>generate_new_link</t></button>
    </div>
</div>