<?php namespace Metayota; include_once('../metayota/library.php');  ?><div>
    <h1><t>partner_availability_title</t></h1>

    <database.table.editor where="{{user_id:loggedInUser$.id, partner_id: this.partner_id }}" resourcetype="partner_availability" table="partner_availability"></database.table.editor>
</div>