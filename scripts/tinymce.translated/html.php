<?php namespace Metayota; include_once('../metayota/library.php');  ?><div element="formBoxEl" class="form-tinymce-translated form-box {this.class} {!!this.errorMessage ? 'has-error' : ''}"> 
    <label if="this.label !== undefined" class="form-label">{translate(this.label)}</label>
    
    <tinymce 
        value="{this.translated_value}" 
        (change)="this.onTinyMCEChange(event)" 
        element="tinymce_element"
        readonly="{this.readonly}">
    </tinymce>

    <div if="this.showAutotranslate">
        <form.button label="<?= translate('autotranslate') ?>" (click)="this.checkAutoTranslate()"></form.button>
    </div>
    <loader loading="{this.translating}"></loader>
    
    <p class="error-message" if="!!this.errorMessage">{this.errorMessage}</p>
</div>