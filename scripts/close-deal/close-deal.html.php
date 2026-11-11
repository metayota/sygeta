<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-contact-sender/mail-contact-sender.php');  ?><div if="!this.commissionOverview">
    <h1><?= translate('close_deal_title') ?></h1>
    <form.resource submitlabel="save" value="{this.deal}" resource_name="closed_deal" (submit)="this.closeDeal(event)"></form.resource>
</div>

<div class="commission-overview" if="this.commissionOverview">

    <h1><?= translate('commission_booking') ?></h1>
    <p><?= translate('commission_review_text') ?></p>
    <div class="deal-summary">
        <div class="deal-metrics">
            <div class="metric">
                <label><?= translate('deal_title') ?></label>
                <span>{this.commissionOverview.deal.title_translated}</span>
            </div>
            <div class="metric">
                <label><?= translate('customer') ?></label>
                <span>{this.commissionOverview.customer.full_name}</span>
            </div>
            <div class="metric">
                <label><?= translate('deal_amount') ?></label>
                <span>{this.commissionOverview.deal_amount_formatted} {this.commissionOverview.currency}</span>
            </div>
            <div class="metric">
                <label><?= translate('total_commission') ?></label>
                <span>{this.commissionOverview.total_commission_formatted} {this.commissionOverview.currency} ({this.commissionOverview.total_commission_percentage}&thinsp;%)</span>
            </div>
        </div>
    </div>

        <h3><?= translate('commission_distribution') ?></h3>
    <div class="commission-hierarchy">
        <div class="levels">
            <div for="commission of this.commissionOverview.commissions">
                <div class="level-item" (click)="this.selectPartner(commission)">
                    <div class="level-badge">L{commission.level}</div>
                    <div class="partner-info">
                        <div>{commission.partner_name}<br/>{commission.email}</div>
                    </div>
                    <div class="commission-info">
                        <div>{commission.commission_rate}&thinsp;%</div>
                        <div>{commission.commission_amount_formatted} {this.commissionOverview.currency}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div if="this.commissionOverview.deal != undefined && this.commissionOverview.deal.booked != 1" class="deal-btns">
        <form.button (click)="this.setAttribute('commissionOverview', undefined)" label="<?= translate('edit') ?>"></form.button>
        <form.checkbox (change)="this.setAttribute('submitEnabled',event)" label="<?= translate('booking_verified') ?>"></form.checkbox>
        <form.button enabled="{this.submitEnabled}" (click)="this.bookDeal()" label="<?= translate('confirm_booking') ?>"></form.button>
    </div>
</div>