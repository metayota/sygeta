<?php 
namespace Metayota; 
include_once('../metayota/library.php');
include_once('../translation-service/translation-service.php');
?>
<div class="create-contact">
    <h1><t>create_contact</t></h1>
    <form.resource value="{{language:CreateContact.default_language}}" element="form"  resource_name="create-contact"></form.resource>

    <div if="this.euCompliance" class="legal-basis">
        <h2><t>legal_basis_title</t></h2>
        <p><t>legal_basis_description</t></p> 
        <form.resource element="legalBasisForm" value="{{legal_basis_id:CreateContact.legitimate_interest_id}}" resource_name="contact_legal_basis"></form.resource>
    </div>

    <form.button (click)="this.createContact()" label="<?= htmlentities(translate('btn_create_contact')) ?>"></form.button>
</div>