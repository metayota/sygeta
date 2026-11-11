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
Resource.appendToAllRequests({'language':'<?= $GLOBALS['language'] ?>','u':'1'})
Tag.registerAndLoad('app')
</script>
<style type="text/css">

@media print {
    @page {
        size: auto; /* Automatically handle page size */
        margin: 0px; /* Adjusted margins for header and footer */
    }
}
</style>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@160..700&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/resource/index.css">
<link rel="apple-touch-icon-precomposed" href="/images/icons/apple-touch-icon.png">
<link rel="icon" href="/images/icons/favicon.ico">
<?php $settings = $db->get('company_settings'); ?>
</head>
<body class="default-print-template">
<div class="print-header"><?= $_GET['page'] != '1' ? $_GET['title'] : ''?></div>

<div class="print-footer">
<?= translate('page_of_pages',['page'=>$_GET['page'],'pages'=>$_GET['count_pages']]) ?>
<span class="page-number"><img style="height:30px" src="/uploads/logos/<?= $settings['logo'] ?>"></span>
</div>
</body>
</html>