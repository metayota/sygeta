<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><h1><?= translate('mail_settings_title') ?></h1>
<p><?= translate('mail_settings_description') ?></p>

<div for="mail_setting of this.mail_settings">
    <form.checkbox (change)="this.changedSetting(mail_setting.type_name,event)" value="{mail_setting.receive_mail}" label="{mail_setting.title_translated}"></form.checkbox>
</div>