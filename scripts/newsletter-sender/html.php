<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><h1><t>newsletter_sender_title</t></h1>
<p><t>newsletter_sender_text</t></p>


<h2><t>preview</t></h2>

<div class="newsletter-preview" if="this.newsletter">
    <div class="newsletter-preview-subject">{this.newsletter.subject_translated}</div>
    <div class="newsletter-preview-message">
        <innerhtml value="{this.newsletter.message_translated}"></innerhtml>
    </div>
</div>

<h2><t>newsletter_receivers_title</t></h2>

<table class="table colored">
    <thead>
        <tr>
            <th><t>name_or_company</t></th>
            <th><t>email</t></th>
        </tr>
    </thead>
    <tbody for="contact of this.newsletter?.receivers">
        <tr>
            <td colspan="{contact.company_name ? 2 : 1}">{contact.company_name ? contact.company_name : contact.full_name}</td>
            <td>{contact.email}</td>
        </tr>
    </tbody>
</table>

<h2><t>newsletter_send_title</t></h2>
<div if="!this.newsletterSent">
    <form.checkbox (change)="this.setAttribute('confirmed',event)" label="<?= htmlentities(translate('newsletter_send_confirm')) ?>"></form.checkbox>
    <form.button (click)="this.send()" enabled="{this.confirmed}" label="<?= htmlentities(translate('newsletter_send_btn')) ?>"></form.button>
</div>
<p if="this.newsletterSent">
<t>newsletter_sent_success</t>
</p>