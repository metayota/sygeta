<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');  ?><h1>{this.meetingTypeObj?.booking_title}</h1>
<p>{this.meetingTypeObj?.booking_description}</p>

<div if="this.meetingInvitation?.accepted === 1">
    <p><t>meeting_booked_success</t></p>
</div>

<div if="this.meetingInvitation?.accepted !== 1 && this.justBooked !== true">
    <dropdown element="dateEl" label="<?= translate('date') ?>" (change)="this.pickedDate(event)" sort="{false}" options="{this.dates}"></dropdown>
    <dropdown element="timeEl" value="{this.timeValue}" label="<?= translate('time') ?>"  sort="{false}" options="{this.times}"></dropdown>
    <form.button (click)="this.book()" label="<?= translate('book_meeting_btn') ?>"></form.button>
</div>

<timezone-handler (change)="this.fetchData()"></timezone-handler>

<div if="this.bookedMeetings && this.bookedMeetings.length > 0">
    <h1><t>booked_meetings_of_this_type</t></h1>
    <div class="presentations-booked" for="bookedMeeting of this.bookedMeetings">
        <div class="icon-text">
            <img src="/images/icons/check-all.svg" />
            <span>{bookedMeeting.text}</span>
        </div>
    </div>
</div>