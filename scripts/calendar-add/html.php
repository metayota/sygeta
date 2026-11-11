<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-contact-sender/mail-contact-sender.php');  ?><div class="calendar-add">
    <h1><t>add_calendar_entry_title</t></h1>
    <p><t>add_calendar_entry_text</t></p>
    <form.resource element="form" (submit)="this.submitEvent(event)" submitlabel="<?= htmlentities(translate('add_calendar_event')) ?>" resourcetype="calendar_event"></form.resource>
</div>