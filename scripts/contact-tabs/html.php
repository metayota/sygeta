<?php namespace Metayota; include_once('../metayota/library.php');  ?><div>
    <tabs (change)="this.setAttribute('tab',event)"
        tabs="{ [{name:'<?= translate('sales_pipeline') ?>',value:'sales_pipeline'}, {name:'<?= translate('customer_activities_title') ?>',value:'customer_activities'}, {name:'<?= translate('sent_mails') ?>',value:'sent_mails'}] }"
        active="sales_pipeline"></tabs>
    <div class="card" if="this.tab == 'sales_pipeline'">
        <steps-progress parameters="{ {customer_id:this.customer?.id} }"></steps-progress>
    </div>
    <div class="card" if="this.tab == 'sent_mails'">
        <email-list contact_id="{this.contact?.id}"></email-list>
    </div>
    <div class="card" if="this.tab == 'customer_activities'">
        <customer-activities customer_id="{this.customer?.id}"></customer-activities>
    </div>
</div>