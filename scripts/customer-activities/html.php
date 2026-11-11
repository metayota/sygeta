<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div for="activity of this.contact_activities">
    <h3>{activity.title_translated}</h3>
    <p class="activity-subtitle">{activity.activity_date_formatted} - {activity.activity_type_translation} - {activity.partner_full_name}</p>
    <p>{activity.description_translated}</p>
</div>

<div if="this.mode == 'standard'">
    <form.button (click)="this.setAttribute('mode','add_activity')" label="<?= translate('add_activity') ?>"></form.button>
</div>

<div if="this.mode == 'add_activity'">
    <form.resource label="<?= translate('add_activity') ?>" value="{ { activity_date:this.getCurrentMySQLDate()} }" hide_parameters="{['contact_id']}" (cancel)="this.setAttribute('mode','standard')" (submit)="this.submit(event)" cancellabel="<?= translate('cancel') ?>" submitlabel="<?= translate('submit_customer_activity') ?>" resourcetype="contact_activity"></form.resource>
</div>