<?php namespace Metayota; include_once('../metayota/library.php');  ?><h1><t>terms_and_conditions_title</t></h1>
<?php $companySettings = $db->get('company_settings'); ?>
<?= translate($companySettings['terms_and_conditions']) ?>