<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../chat-gpt/chat-gpt.php');   include_once('../deepl/deepl.php');   include_once('../translation-service/translation-service.php');   include_once('../ai-translator/ai-translator.php');  ?><div class="form-text-translatable {this.class} form-box"> 
	<label if="this.label !== undefined" class="form-label">{translate(this.label)}</label>
    <div if="!this.unique">
        <dropdown sort="{false}" (change)="this.setExistingTranslation(event)" options="{this.existing_options}"></dropdown>
    </div>
    <div class="input-box"> 
	<input 
        if="!this.getUseTextarea() && (this.unique || this.create_new_translation)"
		name="{this.name}"
        class="input-{this.type ? this.type : 'text'} form-element"
		spellcheck="false" 
		autocomplete="{this.autocomplete == true ?  'on' : 'off'}"
		placeholder="{this.getOtherTranslationNotEmpty()}" 
		element="input" 
		name="{this.name}" 
		type="{this.type != 'undefined' ? this.type : 'text'}" 
		value="{this.translated_value}" 
		(blur)="this.blur()"
        (input)="this.resourceChanged(node.value)" 
        (focus)="this.loadRecommended(node.value)"
        (keypress)="this.keypress(event)"
		readonly="{this.readonly}"/>

    <textarea if="this.getUseTextarea()" name="{this.name}"
        class="input-{this.type ? this.type : 'text'} form-element"
		spellcheck="false" 
		autocomplete="{this.autocomplete == true ?  'on' : 'off'}"
		placeholder="{this.getOtherTranslationNotEmpty()}" 
		element="textarea" 
		name="{this.name}" 
		type="{this.type != 'undefined' ? this.type : 'text'}" 
		value="{this.translated_value}" 
		(blur)="this.blur()"
		(input)="this.resourceChanged(node.value)" 
        (keypress)="this.keypress(event)"
		readonly="{this.readonly}"></textarea>
        
        <button type="button" if="this.show_autotranslate" class="autotranslate-button" (click)="this.checkAutoTranslate()"><img src="/images/icons/{this.translationInProgress ? 'loading.gif' : '18-chat-bot.svg'}"/></button>
        <button type="button" class="show-translations-button" (click)="this.toggleTranslationInfo()"><img src="/images/icons/18-language.svg"/></button>
    
    </div>
    
    <div element="translation_info" style="display:none;" class="translation-info">
        <div if="this.show_key">
            <label class="form-label"><?= translate('translation_key') ?></label>
            <input readonly="{!this.unique && !this.create_new_translation}" type="text" (input)="this.translationKeyChanged(node.value)" value="{this.value}"/>
        </div>
        
        <div for="other_language of this.other_languages">
            <label class="form-label">{other_language.language_translated}</label>
            <input type="text" (input)="this.otherLanguageChanged(other_language, node.value)" value="{other_language.translation}"/>
        </div>
    </div>
</div>
<p class="error-message" if="!!this.errorMessage">{this.errorMessage}</p>