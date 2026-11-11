<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="mail-sender">
    <div class="error-message" if="this.error">
        {this.error}
    </div>
    <div class="success-message" if="this.success">
        <t>email_sent_successfully</t>
    </div>

    <form.filter placeholder="<t>enter_recipients</t>" element="recipientsEl" multiple="{true}" label="<t>email_to</t>" options="{this.contactOptions }"></form.filter>

    <form.text 
        label="<t>email_subject</t>" 
        (change)="this._subject = event"
        value="{this.subject}"
      
        required="true">
    </form.text>

    <tinymce 
        element="tinymce"
        label="<t>email_message</t>"
        value="{this.messageBody}"
        (change)="this._text = event; this._messageBody = event"
        required="true">
    </tinymce>

    <b class="attachments-label" if="this.attachments && this.attachments.length > 0">Attachments</b>
    <div for="attachment of this.attachments">
        <a target="_blank" href="/uploads/mail_attachments/{attachment}">{attachment}</a><br/>
    </div>
    <form.file value="{this.currentAttachment}" label="<t>add_attachment</t>" folder="mail_attachments" (change)="this._attachments.push(event);this.update('this.attachments');this.setAttribute('currentAttachment','')"></form.file>

    <loader loading="{this.sending}"></loader>

    <div class="button-container">
        <button 
            class="primary-button" 
            (click)="this.sendEmail()"
            disabled="{this.sending}">
            <span if="!this.sending"><t>send_email</t></span>
        </button>
    </div>
</div>