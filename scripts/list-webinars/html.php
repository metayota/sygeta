<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><h1>
    <t>webinar_title</t>
</h1>
<p if="this.webinars?.length == 0">
    <t>no_webinars_added</t>
</p>
<div class="webinar-list" for="webinar of this.webinars">
    <div class="card card-colored">
        <div class="mdc-card__primary-action">
            <div class="mdc-card__media mdc-card__media--16-9" style="background-image: url('{webinar.image}');"></div>
            <div class="webinar-content">
                <h2 class="mdc-typography--headline6">{webinar.title_translated}</h2>
                <p class="mdc-typography--body2">{webinar.description_translated}</p>
                <h3>
                    <t>next_webinar_dates</t>
                </h3>
                <ul for="date of webinar.dates">
                    <li class="mdc-typography--subtitle2"><b>{date.start_date_formatted}</b> bis
                        <b>{date.end_time_formatted}</b></li>
                </ul>
            </div>
        </div>
        <div class="button-box">

            <button class="form-button button-primary" onclick="router$.goto('/webinar-detail/{webinar.id}')">
                <span class="mdc-button__label">
                    <?= translate('view_details') ?>
                </span>
            </button>
            <button class="form-button button-primary" onclick="router$.goto('/invite-webinar/{webinar.id}')">
                <span class="mdc-button__label">
                    <?= translate('invite_to_webinar') ?>
                </span>
            </button>

        </div>
    </div>
</div>