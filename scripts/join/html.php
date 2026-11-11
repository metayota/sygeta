<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../invite-functions/invite-functions.php');   include_once('../translation-service/translation-service.php');   include_once('../alterworld-functions/alterworld-functions.php');  ?><?= callAction('title','static',['title'=>translate('join_title'),'h'=>1]) ?>

<p><?= translate('join_description') ?></p>

<?php $eventTypes = ['successful_invitation']; ?>
<?php foreach($eventTypes as $eventType): ?>
    <?php $inviteBonuses = $db->getAllByQuery('SELECT i.*,m.name FROM invite_bonus i LEFT JOIN material m ON m.id = i.material_id WHERE i.event_type=:event_type',['event_type'=>$eventType]); ?>
    <?= callAction('title','static',['title'=>translate('join_welcome_package_title'),'h'=>3]) ?>
    
    <?php 
    $inviteBonusArr = [];
    foreach($inviteBonuses as $inviteBonus) {
        if ($inviteBonus['goldmark']) {
            $inviteBonusArr[] = translate('amount_goldmark', $inviteBonus['goldmark']);
        }
        if ($inviteBonus['material_id']) {
            $inviteBonusArr[] = formatNumber($inviteBonus['material_amount']).' '.translate($inviteBonus['name']);
        }
    } 
    $inviteBonusStr = join(', ', $inviteBonusArr);
    echo $inviteBonusStr;
    ?>
    
<?php endforeach; ?>



<home disable_sign_in="{false}" hide_video="{true}"></home>

<div if="window.allowCookies$ && allowCookies$ == true && this.saveCookie()"></div>