<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><h1><t>event_participants</t></h1>

<div class="participant-list" for="participant of this.participants">
    <div>
        <div>
            {participant.firstname} {participant.lastname}
        </div>
        <div>
            <div class="small-button" if="!participant.attended"><form.button (click)="this.markAsAttended(participant)" label="<?= htmlentities(translate('mark_as_attended')) ?>"></form.button></div>
        </div>
    </div>
</div>