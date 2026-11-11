<?php namespace Metayota; include_once('../metayota/library.php');  ?><div>
    <h1><t>sales_overview_title</t></h1>
    <div class="four-cards">
        <div class="color-card color-card-blue">
            <img class="icon" src="/images/icons/48px/white/48-cheque-2.svg">
            <h3><?= translate('balance') ?></h3>
            <h2>€ {formatNumber(this.account_balance)}</h2>
            <p>
                <a link="/transaction-list"><?= translate('balance_description') ?></a>
            </p>
        </div>
        <div class="color-card color-card-color-2">
            <img class="icon" src="/images/icons/48px/white/48-user-key.svg">
            <h3><?= translate('customers') ?></h3>
            <h2>{this.customer_count}</h2>
            <p>
                <?= translate('customers_description') ?>
            </p>
        </div>
        <div class="color-card color-card-color-3">
            <img class="icon" src="/images/icons/48px/white/48-envelope-content.svg">
            <h3><?= translate('invitations') ?></h3>
            <h2>{this.invitation_count}</h2>
            <p>
                <?= translate('event_invitations_description') ?>
            </p>
        </div>
        <div class="color-card color-card-color-4">
            <img class="icon" src="/images/icons/48px/white/48-coins-plus.svg">
            <h3><?= translate('revenue') ?></h3>
            <h2>€ {formatNumber(this.total_income)}</h2>
            <p>
                <?= translate('revenue_description') ?>
            </p>
        </div>
        <div class="color-card color-card-color-5">
            <img class="icon" src="/images/icons/48px/white/48-paper-plane.svg">
            <h3><?= translate('sales_overview_mails') ?></h3>
            <h2>{formatNumber(this.mails_sent)}</h2>
            <p>
                <?= translate('sales_overview_mails_description') ?>
            </p>
        </div>
        <div class="color-card color-card-color-6">
            <img class="icon" src="/images/icons/48px/white/48-contacts.svg">
            <h3><?= translate('sales_overview_contacts') ?></h3>
            <h2>{formatNumber(this.contact_count)}</h2>
            <p>
                <?= translate('sales_overview_contacts_description') ?>
            </p>
        </div>
        <div class="color-card color-card-color-7">
            <img class="icon" src="/images/icons/48px/white/48-list-favs.svg">
            <h3><?= translate('sales_overview_activities') ?></h3>
            <h2>{formatNumber(this.sales_activity_count)}</h2>
            <p>
                <a link="/sales-activity"><?= translate('sales_overview_activities_description') ?></a>
            </p>
        </div>
        <div class="color-card color-card-color-8">
            <img class="icon" src="/images/icons/48px/white/48-users-chat.svg">
            <h3><?= translate('sales_overview_meetings') ?></h3>
            <h2>{formatNumber(this.meetings_count)}</h2>
            <p>
                <?= translate('sales_overview_meetings_description') ?>
            </p>
        </div>
    </div>
</div>