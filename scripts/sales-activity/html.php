<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><h1><t>sales_activity_title</t></h1>

<sales-activity-diagram></sales-activity-diagram>


<div for="activity of this.salesActivities">
    <h3>{activity.title_translated}</h3>
    <p class="activity-subtitle">{activity.activity_date_formatted} - {activity.activity_type_translation} - {activity.partner_full_name}</p>
    <p>{activity.description_translated}</p>
</div>
