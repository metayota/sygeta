<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><contact-card contact_id="{this.contact?.id}"></contact-card>

<div class="customer-detail">
    <div class="card" if="!!this.customer && !!this.contact">
        <h1 class="main-title"><span>{translate(this.contact.salutation)} {this.contact.firstname} {this.contact.lastname}</span><div class="action-buttons"></div></h1>
        <div class="two-column-form">
            <form.resource label="<?= translate('contact') ?>" readonly="{true}" (submit)="this.saveCustomer(event)"
                resourcetype="contact" value="{this.contact}"></form.resource>
            <form.resource readonly="{true}" (submit)="this.saveCustomer(event)" resourcetype="customer-info"
                value="{this.customer}"></form.resource>
        </div>

        <div if="!this.performingAction">
            <div for="stepAction of this.stepActions" class="step-actions">
                <button (click)="this.performStep(stepAction)"
                    class="form-button">{stepAction.title_translated}</button>
            </div>
        </div>
        
        <loader loading="{this.performingAction}"></loader>
        
    </div>

</div>


<div class="customer-detail">
    <h2><t>partner</t></h2>
    {this.partner?.full_name} - {this.partner?.phone} - {this.partner_contact?.email}
</div>

<div class="customer-detail">
    <h2><t>more_options</t></h2>
    <form.button (click)="router$.goto('/lost-deal/'+this.contact?.id)" label="<?= htmlentities(translate('mark_as_lost')) ?>"></form.button>
</div>