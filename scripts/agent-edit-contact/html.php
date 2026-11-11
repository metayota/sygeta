<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="agent-edit-contact two-column-form">
    <h1><t>edit_own_contact_title</t></h1>
    <form.resource hide_parameters="{['contact_groups','contact_type_id','timezone']}" submitlabel="save" (submit)="this.save(event)" resource_name="contact" value="{this.ownContact}"></form.resource>
</div>