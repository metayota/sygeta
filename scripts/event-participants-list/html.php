<?php namespace Metayota; include_once('../metayota/library.php');  ?><div class="event-participants-list">
    <h2><?= translate('event_participants_list_title') ?></h2>
    <ul for="participant of this.participants">
        <li>{participant.full_name} - {participant.email}</li>
    </ul>
    <p if="this.participants?.length == 0">
        <?= translate('no_participants_found') ?>
    </p>
</div>