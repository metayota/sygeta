<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="two-column-form row-creator-{this.resource_name}">
    <breadcrumbs></breadcrumbs>
    <h1 if="this.title && this.title !== false">{this.title} </h1>
    <form.resource showdocumentation="{this.showdocumentation}" element="form" (submit)="this.save(event)" label="{this.label}" submitlabel="<?= translate('save') ?>" resourcetype="{this.resource_name}" value="{this.value}"></form.resource>
</div>