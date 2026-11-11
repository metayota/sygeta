<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../user-lib/user-lib.php');  ?><div class="page reset-password">
    <h1><t>set_password_for_user_title</t></h1>
    <p><t>set_password_for_user_description</t></p>


    <section class="content-row">
        <form.resource submitlabel="<?= translate('set_new_password') ?>" (submit)="this.resetPw(event)" resourcetype="set-password"></form.resource>
    </section>
</div>