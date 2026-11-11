<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><h1><t>add_document_title</t></h1>

<dropdown element="documentTypeEl" options="{this.documentTypes}" label="<?= htmlentities(translate('document_type')) ?>" value="{this.selectDocumentType}"></dropdown>

<form.file element="fileEl" label="<?= htmlentities(translate('file')) ?>" folder="document"></form.file>

<form.button (click)="this.addDocument()" label="<?= htmlentities(translate('add')) ?>"></form.button>