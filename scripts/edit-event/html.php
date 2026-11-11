<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../send-mail/send-mail.php');   include_once('../mail-helper/mail-helper.php');  ?><div class="edit-event">
    <h1><t>edit_event</t></h1>
    <loader loading="{this.loading}"></loader>
    <div if="!this.loading">
        <form.resource 
            element="form"
            resource_name="calendar_event"
            showdocumentation="{true}"
            hide_parameters="{['video_conferencing']}"
            submitlabel="<t>update_event</t>"
            (submit)="this.handleSubmit(event)"
            value="{this.eventData}">
        </form.resource>
    </div>
</div>