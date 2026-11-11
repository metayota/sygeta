<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../slugify/slugify.php');  ?><h1><t>create_sales_document</t></h1>
<form.resource value="{this.value}" element="salesDocumentEl" (change)="this.itemChanged(event)" resource_name="create-sales-document"></form.resource>

<div if="this.document_type?.has_body_text">
    <form.text.translated translation_category="document_title" unique="{true}" element="titleEl" label="<?= translate('subject') ?>"></form.text.translated>
    <tinymce.translated element="bodyTextEl" label="<?= translate('body_text') ?>"></tinymce.translated>
</div>
<div if="this.document_type?.custom_form">
    <form.resource element="customFormEl" resource_name="{this.document_type.custom_form}"></form.resource>
</div>

<div if="!this.waiting">
    <form.button (click)="this.createSalesDocument()" label="<?= translate('save') ?>"></form.button>
</div>


<loader loading="{this.waiting}"></loader>