<?php namespace Metayota; include_once('../metayota/library.php');  ?>
<db.row.editor table="event_type" resourcetype="event_type" rowid="{router$.params.tab}"></db.row.editor>

<database.table.editor table="event_reminder" resourcetype="event_reminder" where="{{event_type:this.eventType?.name}}"></database.table.editor>