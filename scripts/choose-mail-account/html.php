<?php namespace Metayota; include_once('../metayota/library.php');  ?><div class="email-accounts-grid" if="this.emailAccounts?.count > 1">
    <loader loading="{this.loading}"></loader>
    <div if="!this.loading">
        <div class="account-cards" for="account of this.emailAccounts">
            <div class="account-card" (click)="this.openEmailClient(account.id)">
                <div class="account-name">{account.from_name}</div>
                <div class="account-email">{account.email}</div>
            </div>
            
        </div>
        <div if="this.emailAccounts.length === 0" class="no-accounts">
            <t>no_email_accounts</t>
        </div>
    </div>
</div>