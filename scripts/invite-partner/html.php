<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-contact-sender/mail-contact-sender.php');   include_once('../user-lib/user-lib.php');  ?><div class="invite-partner two-column-form">
    <h1><t>invite_partner_title</t></h1>
    <p><t>invite_partner_text</t></p>

    <form.resource element="form" value="{ {language:'de',usergroup_id:2} }" (submit)="this.invite(event)" resource_name="invite-partner" submitlabel="<?= htmlentities(translate('invite_btn')) ?>"></form.resource>
</div>

