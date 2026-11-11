<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="page manage-documents">
    <div if="!this.complete">
        <database.table.editor where="{{user_id:loggedInUser$.id}}" add_link="/add-document/{this.customer_id}" resourcetype="document" table="document"></database.table.editor>
    </div>
    <p class="icon-text" if="!!this.complete">
        <img src="/images/icons/check-all.svg" />
        <t>documents_submitted_text</t>
    </p>
</div>
<h1 if="this.categories?.length > 0 ">
    <t>documents_overview</t>
</h1>
<div for="category of this.categories">
    <h2>{category.title}</h2>
    <div for="checklistItem of category.documents" class="checklist">
        <div class="checklist-item">
            <div class="{checklistItem.done ? 'checklist-done' : 'checklist-unchecked'}"></div>
            <div class="link" link="/add-document/#{checklistItem.document_type_id}">{checklistItem.document_name_translated}</div>
            <div for="document of checklistItem.documents">
                <a target="_blank" href="/uploads/document/{document.file}">{document.file}</a>
            </div>
            <!--div if="checklistItem.number_of_documents > 0">{checklistItem.number_of_documents} {checklistItem.number_of_documents == 1 ? translate('document') : translate('documents')}</div-->
        </div>
    </div>
</div>

<div if="!this.complete">
    <h2>
        <t>documents_submit</t>
    </h2>
    <p>
        <t>documents_submit_text</t>
    </p>
    <form.checkbox element="completeCheckboxEl" label="<?= htmlentities(translate('confirm_all_uploaded')) ?>">
    </form.checkbox>
    <form.button (click)="this.confirmComplete()" label="<?= translate('confirm') ?>"></form.button>
</div>