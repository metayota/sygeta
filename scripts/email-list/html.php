<?php namespace Metayota; include_once('../metayota/library.php');  ?><div for="mail of this.mails">
    <div class="mail-message">
        <div>
            <a link="/mail-reader/{mail.id}">{mail.subject}</a>
        </div>
        <div>
            {this.formatDate(mail.message_time)}
        </div>
    </div>
</div>
<p if="this.mails && this.mails.length == 0">
    <?= translate('no_mails_sent') ?>
</p>