<?php namespace Metayota; include_once('../metayota/library.php');  ?>

    <h2><t>sales_document_line_items</t></h2>
    <database.table.editor
        element="databaseTableEditor"
        table="sales_document_item"
        resourcetype="sales_document_item"
        where="{{document_id: this.document_id}}">
    </database.table.editor>
    
    <div if="this.show_preview !== false">
        <form.button label="<?= translate('preview_document') ?>" (click)="router$.goto('/sales-document-detail-view/'+this.document_id)"></form.button>
    </div>