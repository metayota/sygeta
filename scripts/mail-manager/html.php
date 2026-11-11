<?php namespace Metayota; include_once('../metayota/library.php');  ?><h1><?= translate('mail_manager_title') ?></h1>
<div for="mail_account of this.mail_accounts">
    <h2>{mail_account.username}@{mail_account.domain}</h2>
    <table if="mail_account.aliases.length > 0" class="table">
        <thead>
            <tr>
                <th>
                    <?= translate('mail_alias') ?>
                </th>   
                <th>
                    <?= translate('actions') ?>
                </th>   
            </tr>
        </thead>
        <tbody for="alias of mail_account.aliases">
            <tr>
                <td>
                    {alias.source}
                </td>
                <td>
                    <form.button label="Delete" (click)="this.deleteAlias(alias.id)"></form.button>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="button-box">
        <form.button label="<?= htmlentities(translate('add_mail_alias')) ?>" (click)="this.addAlias(mail_account.id)"></form.button>
        <form.button label="<?= htmlentities(translate('change_password')) ?>" (click)="this.changePassword(mail_account.id)"></form.button>
    </div>
</div>
<hr/>
<form.button label="<?= htmlentities(translate('add_mail_account')) ?>" (click)="this.addMailAccount()"></form.button>