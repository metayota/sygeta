<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="two-column-form row-editor-{this.resourcetype}">
    <h1 if="this.title && this.title !== false">{this.title ? this.title : translate('edit_'+this.resourcetype)} </h1>
    <form.resource showdocumentation="{this.showdocumentation}" element="form" (submit)="this.save(event)" label="{this.label}" submitlabel="<?= translate('save') ?>" resourcetype="{this.resourcetype}" value="{this.value}"></form.resource>
</div>