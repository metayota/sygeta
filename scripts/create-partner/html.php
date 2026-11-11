<?php namespace Metayota; include_once('../metayota/library.php');  ?><div>
    <h1><t>create_partner_title</t></h1>
    <p><t>create_partner_description</t></p>
    <form.resource
        (submit)="this.create(event)"
        submitlabel="<?= htmlentities(translate('create_partner_submit')) ?>"
        value="{this.formData}"
        resourcetype="partner">
    </form.resource>
</div>