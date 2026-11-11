<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../invite-functions/invite-functions.php');  ?><div>
     <div id="g_id_onload"
        data-client_id="<?= getConfig('google_oauth_client_id') ?>"
        data-context="signin"
        data-ux_mode="popup"
        data-callback="signInCallback"
        data-auto_select="false"
        data-itp_support="true"
         >
    </div>

    <div element="btn" class="g_id_signin"
        data-type="standard"
        data-shape="rectangular"
        data-theme="outline"
        data-text="signin_with"
        data-size="large"
        data-logo_alignment="left"
        data-locale="<?= str_replace('-','_',translate('language_code_long')) ?>">
    </div>
</div>