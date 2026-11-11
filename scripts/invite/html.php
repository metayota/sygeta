<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../format-text/format-text.php');   include_once('../invite-functions/invite-functions.php');   include_once('../translation-service/translation-service.php');   include_once('../alterworld-functions/alterworld-functions.php');  ?>
<?= callAction('title','static',['title'=>translate('invite_title'),'h'=>1]) ?>

<p><?= formatText(translate('invite_description')) ?></p>

<p><b><?= translate('invite_only_known_contacts') ?></b></p>

<?= callAction('title','static',['title'=>translate('invite_options'),'h'=>2]) ?>

<form.radio value="{this.view}" (change)="this.setView(event)" options="{ [ {'name':'<?= translate('invite_link') ?>',value:'link'}, {'name':'<?= translate('invite_email') ?>',value:'email'}, {'name':'<?= translate('invite_addressbook') ?>',value:'addressbook'}, {'name':'<?= translate('social_media') ?>',value:'social_media'}  ] }"></form.radio>
<div style="display:none"><form.text></form.text><!-- css--></div>
<div if="this.view == 'link'" class="invitation-links">
    <p><?= translate('invitation_link_description') ?></p>
    <div for="invite_link of this.invite_links">
        <div class="form-text"> 
        	<input value="{invite_link}" class="input-text form-element" spellcheck="false" readonly="readonly" onfocus="this.select()" onmouseup="return false" onselect="this.select()">
        </div>
    </div>
    <form.button (click)="this.generateInviteLinks()" label="<?= translate('btn_generate_invite_links') ?>"></form.button>
</div>

<div if="this.view == 'addressbook'" class="invitation-addressbook">
 
    <?= callAction('title','static',['title'=>translate('import_contacts'),'h'=>3]) ?>
    <dropdown label="<?= translate('external_addressbook') ?>" (change)="this.setSubview(event)" options="{ [{'value':'csv', name:'<?= translate('upload_addressbook') ?>'}, {'value':'google_addressbook', name:'<?= translate('google_contacts') ?>'}] }"></dropdown>

    <div if="this.subview == 'google_addressbook'">
        <p><?= formatText(translate('import_google_description')) ?></p>
        <google-addressbook></google-addressbook>
    </div>
    <div if="this.subview == 'csv'">
        <p><?= translate('import_addressbook_description') ?></p>
        <upload-addressbook (change)="this.loadContacts()"></upload-addressbook>
    </div>
    <p if="this.contacts && this.contacts.length == 0">
        <?= translate('no_contacts_yet') ?>
    </p>

    <?= callAction('title','static',['title'=>translate('your_contacts'),'h'=>3]) ?>
   <table if="this.contacts && this.contacts.length > 0" class="table colored">
        <thead>
            <tr>
                <th><?= translate('invite') ?></th>
                <th><?= translate('firstname') ?></th>
                <th><?= translate('lastname') ?></th>
                <th><?= translate('company') ?></th>
                <th><?= translate('mail_address') ?></th>
            </tr>
        </thead>
        <tbody for="contact of this.contacts">
            <tr class="{contact.last_invite != undefined ? 'already_invited' : ''}">
                <td><div if="contact.last_invite == undefined"><form.checkbox value="{this.selectedContacts[contact.id]}" (change)="this.checkedContact(contact.id, event)"></form.checkbox></div>
                    <div if="contact.last_invite != undefined">{translate('invited_on_date',formatDate(contact.last_invite))}</div>
                </td>
                <td>{contact.firstname}</td>
                <td>{contact.lastname}</td>
                <td>{contact.company}</td>
                <td>{contact.mail_address}</td>
                <td><a (click)="this.editContact(contact)">✏️</a><a (click)="this.deleteContact(contact)">&#x1F5D1;</a></td>
            </tr>
        </tbody>
    </table>
    <form.button label="<?= translate('add_contact') ?>" (click)="this.addContact(event)"></form.button>
</div>
<div if="this.view == 'social_media'" class="social-media">
    <?= callAction('title','static',['title'=>translate('share_us_on'),'h'=>3]) ?>
    <br/>
    <?php $social_medias = ['xcom','whatsapp','telegram','instagram','instagram_messenger','tiktok','snapchat','skype','reddit','pinterest','linkedin','facebook']; ?><!-- ,'spotify','soundcloud''youtube','tiktok',-->
    <a (click)="this.otherShareText()"><?= translate('other_share_text') ?></a> <a (click)="this.copyToClipboard()" style="margin-left:20px;"><span if="!!this.copied_to_clipboard">✔ </span><?= translate('copy_to_clipboard') ?></a>
    <form.textarea value="{this.share_text}" (change)="this.updateShareText(event)"></form.textarea>
    <div class="share-buttons">
    <?php foreach($social_medias as $social_media): ?>
        <img (click)="this.shareLink('<?= $social_media ?>')" src="/icon-m/images/social_media_circle/<?= $social_media ?>.png"/>
    <?php endforeach; ?>
    </div>
    <?= callAction('title','static',['title'=>translate('our_social_media_pages'),'h'=>3]) ?>
    <div style="display:flex;margin-top:20px;">
        <div class="find-us-on">
            <p>/alterworld.de</p>
            <a target="_blank" href="https://facebook.com/alterworld.de"><img src="/images/social_media/facebook.png"/></a>
        </div>
        <div class="find-us-on">
            <p>/alterworld_de</p>
            <a target="_blank" href="https://x.com/alterworld_de"><img src="/images/social_media/xcom.png"/></a>
        </div>
        <div class="find-us-on">
            <p>/@alterworld_de</p>
            <a target="_blank" href="https://www.youtube.com/@alterworld_de"><img src="/images/social_media/youtube.png"/></a>
        </div>
    </div>
</div>
<div if="this.view == 'email' || this.view == 'addressbook'" class="invitation-mail">
    <div if="this.view != 'addressbook'">
        <p><?= translate('invite_email_description') ?></p>
        <form.textarea value="{this.mail_addresses}" element="mail_addresses_element" (change)="this.mailsUpdated(event)" label="<?= translate('email_addresses') ?>"></form.textarea>
    </div>
    <form.text readonly="{true}" value="{this.mail_subject}" label="<?= translate('invite_mail_subject') ?>"></form.text>
    <form.textarea (change)="this.updatePreview(event)" class="personal-message" label="<?= translate('personal_message') ?>"></form.textarea>
    <form.radio value="{this.mail_version}" (change)="this.setMailVersion(event)" options="{ [ {'name':'<?= translate('mail_version_1') ?>',value:'1'}, {'name':'<?= translate('mail_version_2') ?>',value:'2'}, {'name':'<?= translate('mail_version_3') ?>',value:'3'} ] }"></form.radio>
    <form.textarea class="message-preview" readonly="{true}" value="{this.mail_text}" label="<?= translate('invite_mail_text') ?>"></form.textarea>
    <form.button (click)="this.submit()" label="<?= translate('invite_submit_btn') ?>"></form.button>
</div>

<?= callAction('title','static',['title'=>translate('invite_current_bonus'),'h'=>2]) ?>

<?php $eventTypes = ['link_click', 'successful_invitation', 'goldmark_purchase']; ?>
<?php foreach($eventTypes as $eventType): ?>
    <?php $inviteBonuses = $db->getAllByQuery('SELECT i.*,m.name,m.icon FROM invite_bonus i LEFT JOIN material m ON m.id = i.material_id WHERE i.event_type=:event_type',['event_type'=>$eventType]); ?>
    <?= callAction('title','static',['title'=>translate('invite_'.$eventType.'_title'),'h'=>3]) ?>
    <div class="invitation-bonus">
    <?php foreach($inviteBonuses as $inviteBonus): ?>
        <?php if (!empty($inviteBonus['purchase_percentage'])): ?>
            <div style="width:auto;">
                <p style="font-family:'GT Spectra E';font-size:40px;font-weight:bold;"><?= $inviteBonus['purchase_percentage']?>%</p>
                <p><?= translate('amount_revenue_sharing_first_purchase', $inviteBonus['purchase_percentage']) ?></p>
            </div>
        <?php endif; ?>
        <?php if ($inviteBonus['material_id']): ?>
            <div>
                <img src="/resize-material/uploads/resources/<?= $inviteBonus['icon'] ?>" />
                <p style="white-space:nowrap;"><?= formatNumber($inviteBonus['material_amount']) ?> <?= translate($inviteBonus['name']) ?></p>
            </div>
        <?php endif; ?>
        <?php if ($inviteBonus['goldmark']): ?>
            <div>
                <p style="font-family:'GT Spectra E';font-size:40px;font-weight:bold;"><?= $inviteBonus['goldmark']?>.-</p>
                <p><?= translate('goldmark') ?></p>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<p><?= translate('maximum_registration_bonus') ?></p>