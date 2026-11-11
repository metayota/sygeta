<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?>
<db.row.editor title="<?= translate('edit_line_diagram') ?>" resourcetype="line_diagram" table="line_diagram" rowid="{this.line_diagram_id}"></db.row.editor>

<h1><t>line_diagram_series_title</t></h1>
<database.table.editor resourcetype="line_diagram_series" table="line_diagram_series" where="{ {diagram_id:this.line_diagram_id} }"></database.table.editor>

<div for="serie of this.series">
    <h1><t>line_diagram_datapoint_title</t>: {serie.title_translated}</h1>
    <database.table.editor resourcetype="line_diagram_item" table="line_diagram_item" where="{ {diagram_id:this.line_diagram_id,series_id:serie.id} }"></database.table.editor>
</div>

<h1><t>preview</t></h1>
<form.button (click)="this.refresh()" label="<t>refresh</t>"></form.button>
<line-diagram element="lineDiagram" diagram_id="{this.line_diagram_id}"></line-diagram>