<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><h1>
    <t>event_title</t>
</h1>
<p if="this.events?.length == 0">
    <t>no_event_added</t>
</p>
<div class="event-list" for="eventObj of this.events">
    <div class="card card-colored">
        <div class="mdc-card__primary-action">
            <div class="mdc-card__media mdc-card__media--16-9" style="background-image: url('{eventObj.image}');"></div>
            <div class="event-content">
                <h2 class="mdc-typography--headline6">{eventObj.title_translated}</h2>
                <p class="mdc-typography--body2">{eventObj.description_translated}</p>
                <h3>
                    <t>next_event_dates</t>
                </h3>
                <ul for="date of eventObj.dates">
                    <li class="mdc-typography--subtitle2"><b>{date.start_date_formatted}</b> <t>from_to_separator</t>
                        <b>{date.end_time_formatted}</b></li>
                </ul>
            </div>
        </div>
        <div class="button-box" if="loggedInUser$.usergroup_id != 4">

            <button class="form-button button-primary" onclick="router$.goto('/event-detail/{eventObj.id}')">
                <span class="mdc-button__label">
                    <?= translate('view_details') ?>
                </span>
            </button>
            <button class="form-button button-primary" onclick="router$.goto('/invite-event/{eventObj.id}')">
                <span class="mdc-button__label">
                    <?= translate('invite_to_event') ?>
                </span>
            </button>

        </div>
    </div>
</div>