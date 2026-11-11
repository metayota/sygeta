<?php 
namespace Metayota;
include_once('../metayota/library.php');
include_once('../translation-service/translation-service.php');  

$sql = 'SELECT c.eu_compliance FROM company_settings cs LEFT JOIN country c ON c.country_code=cs.country';
$euCompliance = $db->getColumnByQuery($sql);


?>

<div class="contact-card">
    <div if="this.contact">
        <h1>{this.contact.full_name} <span
                class="additional-info"><span>{this.contact.contact_type?.title_translated}</span><button
                    (click)="router$.goto('/edit-data/contact/'+this.contact.id)" class="form-button">
                    <t>edit</t>
                </button></span></h1>
        <p class="contact-card-shortinfo">
            <span class="badge" if="this.contact.user_id != loggedInUser$.id && this.contact.owner">{translate('contact_of_user', this.contact.owner.username ?? this.contact.owner.full_name)}</span>
            <span if="this.formatAddress(this.contact)">{this.formatAddress(this.contact)}</span>
            <span if="this.contact.website"><a href="{this.contact.website}"
                    target="_blank">{this.contact.website}</a></span>
            <span if="this.contact.email"><a href="mailto:{this.contact.email}"
                    target="_blank">{this.contact.email}</a></span>
            <span if="this.cleanedPhoneNumber(this.contact.phone)">
                <a href="{this.cleanedPhoneNumber(this.contact.phone)}" target="_blank">{this.contact.phone}</a>
            </span>
        </p>
        <div>
            <tabs (change)="this.setAttribute('tab',event)"
                tabs="{ [ {name:'<?= translate('sales_pipeline') ?>',value:'sales_pipeline'}, {name:'<?= translate('customer_activities_title') ?>',value:'customer_activities'}, {name:'<?= translate('sent_mails') ?>',value:'sent_mails'}, {name:'<?= translate('legal_basis') ?>',value:'legal_basis'}] }"
                active="sales_pipeline"></tabs>
        </div>

        <div class="card" if="this.tab == undefined || this.tab == 'sales_pipeline'">
            <steps-progress element="stepsProgress" parameters="{ {contact_id:this.contact?.id} }"></steps-progress>
            <step-actions (refresh)="this.stepsProgress.refresh()" contact_id="{this.contact.id}"></step-actions>
        </div>
        <div class="card" if="this.tab == 'sent_mails'">
            <email-list contact_id="{this.contact?.id}"></email-list>
        </div>
        <div class="card" if="this.tab == 'customer_activities'">
            <customer-activities contact_id="{this.contact?.id}"></customer-activities>
        </div>
        <?php if ($euCompliance): ?>
        <div class="card" if="this.tab == 'legal_basis'">
            <contact-legal-basis contact_id="{this.contact?.id}"></contact-legal-basis>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>