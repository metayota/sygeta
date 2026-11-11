<?php namespace Metayota; include_once('../metayota/library.php');  ?><h1><t>resource_title_edit_meeting_type</t></h1>
<db.row.editor title="{false}" resourcetype="meeting_type" row_name="{router$.params.tab}"></db.row.editor>

<h1><t>manage_meeting_type_reminders</t></h1>

<database.table.editor table="meeting_reminder" resourcetype="meeting_reminder" where="{{meeting_type:router$.params.tab}}"></database.table.editor>