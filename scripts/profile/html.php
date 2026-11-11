<?php namespace Metayota; include_once('../metayota/library.php');  ?><div class="profile two-column-form" style="color:black;">
    <h1><t>edit_profile</t></h1>
    <form.resource hide_parameters="{['usergroup_id','contact_id','register_date']}" showdocumentation="{true}" value="{this.profile}" submitlabel="<?= translate('save') ?>" (submit)="this.updateUserProfile(event)" resourcetype="user"></form.resource>
</div>

<agent-edit-contact></agent-edit-contact>

<h1><t>other_settings</t></h1>
<ul>
    <li><a link="/change-password"><t>update_password_title</t></a></li>
</ul>