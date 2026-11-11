<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div>
    <h1><t>reset_crm_title</t></h1>
    <form.resource element="resetCrmForm" resource_name="reset-crm"></form.resource>
    <form.checkbox (change)="this.setAttribute('buttonEnabled', event)" label="<?= htmlentities(translate('reset_crm_confirm')) ?>"></form.checkbox>
    <form.button label="<t>reset_crm</t>" enabled="{this.buttonEnabled}" (click)="this.resetCRM()" class="form-button"></form.button>
</div>