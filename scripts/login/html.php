<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="login card">
    <div if="router$.params.subpage == 'confirmed-mail'">
        <h1>
            <?= translate('verification_thank_you_title') ?>
        </h1>

        <p>
            <?= translate('verification_thank_you_text') ?>
        </p>
    </div>

    <div>
        <div if="!loggedInUser$.username" class="form-login">
            <h1 if="!loggedInUser$.username">
                <?= translate('login_title') ?>
            </h1>
            <form.resource value="{{'remember_me':true}}" autocomplete="{false}" element="loginform"
                (submit)="this.login(event)" submitlabel="<?= translate('login_button') ?>" resourcetype="login">
            </form.resource>
        </div>
        <p class="error-message" if="!!this.errorMessage">{this.errorMessage}</p>
        <p if="!!this.errorMessage">
            <form.button (click)="router$.goto('/forgot-password');" label="<?= translate('forgot_password_button') ?>">
            </form.button>
        </p>
        <div if="!!loggedInUser$.username" class="form-login">
            <h1 if="!!loggedInUser$.username">
                <?= translate('welcome_title') ?>
            </h1>
            <p>
                <?= translate('logged_in_message') ?>
            </p>
            <form.button (click)="this.logout()" label="<?= translate('log_out_button') ?>"></form.button>
        </div>
    </div>
    <?= callAction('login-problems','html') ?>
</div>