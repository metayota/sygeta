<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
sendCacheHeaders();

$GLOBALS['language'] = !empty($_GET['language']) ? $_GET['language'] : 'de';
if (strlen($GLOBALS['language']) > 2) {
   die('unsecure request'); 
}

?><!doctype html>
<html lang="en">
<head>
<title>Sygeta CRM - Print Template</title>
<base href="/">
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Description for your website!" />
<meta name="keywords" content="website,keywords,..." />
<meta name="robots" content="index, follow" />
<meta name="revisit-after" content="1 week" />
<script type="text/javascript" src="/resource/framework.javascript"></script>
<script type="text/javascript">
//Resource.appendToAllRequests({'language':'<?= $GLOBALS['language'] ?>','u':'1'})
//Tag.registerAndLoad('app')
</script>
<style type="text/css">
@media print {
   @page {
       size: auto; /* Automatically handle page size */
       margin: 0px 0px 0px 0px; /* Adjusted margins for header and footer */
   }
}
</style>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@160..700&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/resource/print-template-bill/layout.css">
<link rel="apple-touch-icon-precomposed" href="/images/icons/apple-touch-icon.png">
<link rel="icon" href="/images/icons/favicon.ico">
</head>
<body class="default-print-template layout-<?= isset($_GET['layout_name']) ? $_GET['layout_name'] : '' ?>">
<?php $settings = $db->get('company_settings'); ?>
<div class="print-header">
    <div ></div>
   <div style="float:right"><img src="/uploads/logos/<?= $settings['logo'] ?>" style="height:40px"></div>
</div>


<div class="print-footer">
    <div>
        <?= $settings['name'] ?><br/>
        <?php if(!empty($settings['vat_number'])): ?>
        <?= translate('print_template_vat')?>: <?= $settings['vat_number'] ?><br/>
        <?php endif; ?>
        <?php if (!empty($settings['executive_director'])): ?>
        <?= translate('print_template_executive_director')?>: <?= $settings['executive_director'] ?><br/>
        <?php endif; ?>
        <?php if (!empty($settings['hr_number'])): ?>
        <?= $settings['hr_number'] ?><br/>
        <?php endif; ?>
    </div>

<div class="center-footer">
    <?php if (!isset($_GET['account_information']) || $_GET['account_information'] == 'show'): ?>
        <div class="grid-layout">
            <?php $bankSettingsRow = $db->get('company_payment_method'); 
                $bankSettings = !empty($bankSettingsRow['payment_method_data']) ? json_decode($bankSettingsRow['payment_method_data'],true) : [];
            ?>
            <?php if (!empty($bankSettings['bank_name'])): ?> 
            <div class="label"><?= translate('print_template_bank')?>:</div>
            <div class="value"><?= $bankSettings['bank_name'] ?></div>
            <?php endif; ?>
            
            <?php if (!empty($bankSettings['account_holder'])): ?> 
            <div class="label"><?= translate('print_template_account_holder')?>:</div>
            <div class="value"><?= $bankSettings['account_holder'] ?></div>
            <?php endif; ?>

            <?php if (!empty($bankSettings['bank_address'])): ?> 
            <div class="label"><?= translate('print_template_bank_address')?>:</div>
            <div class="value"><?= $bankSettings['bank_address'] ?></div>
            <?php endif; ?>

            <?php if (!empty($bankSettings['iban'])): ?> 
            <div class="label"><?= translate('print_template_iban')?>:</div>
            <div class="value"><?= $bankSettings['iban'] ?></div>
            <?php endif; ?>

            <?php if (!empty($bankSettings['swift_bic'])): ?> 
            <div class="label"><?= translate('print_template_bic')?>:</div>
            <div class="value"><?= $bankSettings['swift_bic'] ?></div>
            <?php endif; ?>

            <?php if (!empty($bankSettings['account_number'])): ?> 
            <div class="label"><?= translate('print_template_account_number')?>:</div>
            <div class="value"><?= $bankSettings['account_number'] ?></div>
            <?php endif; ?>
        </div>
  <?php endif; ?>
</div>

<div class="right-footer">
  
    <div class="value"><?= $settings['website'] ?></div>
    <div class="value"><?= $settings['email'] ?></div>
    <div class="value"><?= $settings['phone'] ?></div>
  
</div>

   <div class="page-number"><?= translate('page_of_pages',['page'=>$_GET['page'],'pages'=>$_GET['count_pages']]) ?></div>
</div>
</body>
</html>