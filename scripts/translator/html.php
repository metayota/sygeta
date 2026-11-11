<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="translator">
    <h1><?= translate('translation_tool_title') ?></h1>

    <div class="translator-headline">
        <div>
            <form.text value="{this.search}" (change)="this.updateSearch(event)" label="<?= translate('search') ?>"></form.text>
        </div>
        <div>
            <form.button (click)="this.addNewTranslation(event)" label="<?= translate('add_translation') ?>"></form.button>
        </div>
        <div>
        
            <form.button (click)="router$.goto('<?= getConfig('main_server') || getConfig('wordpress') ? '/editor/view' : '' ?>/complete-translations')" label="<?= translate('complete_translations') ?>"></form.button>
        
        </div>
    </div>
    <hr/>

    <div style="display:grid;grid-template-columns:1fr 1fr">
        <dropdown value="{translate('language_code')}" (change)="this.updateLanguageA(event)" label="<?= translate('language_a') ?>"
            options="{ this.languages }">
        </dropdown>
        <dropdown (change)="this.updateLanguageB(event)" label="<?= translate('language_b') ?>"
            options="{ this.languages }">
        </dropdown>
    </div>


    <div for="translation of this.translations">
        <h3 style="user-select:all;">{translation.translation_key}</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr" if="!this.displayTextarea(translation)">
            <div style="margin-right:20px">
                <form.text (enter)="this.pressedEnter(translation)" (change)="this.changedTranslation(translation,event)" value="{translation.translation}"></form.text>
                <p if="translation.original_changed"><button (click)="this.resetTranslation(translation)"><?= translate('reset_other_languages') ?></button></p>
            </div>
            <div style="margin-left:20px" if="this.language_b != undefined">
                <form.text (enter)="this.pressedEnterB(translation)" (change)="this.changedTranslationB(translation,event)" value="{translation.translation_b}"></form.text>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr" if="this.displayTextarea(translation)">
            <div style="margin-right:20px">
                <form.textarea (change)="this.changedTranslation(translation,event)" value="{translation.translation}"></form.textarea>
                <p if="translation.original_changed"><button (click)="this.resetTranslation(translation)"><?= translate('reset_other_languages') ?></button></p>
            </div>
            <div style="margin-left:20px" if="this.language_b != undefined">
                <form.textarea (change)="this.changedTranslationB(translation,event)" value="{translation.translation_b}"></form.textarea>
            </div>
        </div>
    </div>

    <form.button (click)="this.saveChanges()" label="<?= translate('save') ?>"></form.button>

    <paginator (change)="this.pageChanged(event)" number_of_pages="{this.number_of_pages}"></paginator>

</div>