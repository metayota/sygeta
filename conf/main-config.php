<?php


   $config = [
	'main_language' => 'en',
        'deepl_api_key' => '',
        'deepl_pro' => false,
        'claude_api_key' => '',
	'google_maps_api_key' => '',
	'database_table_editor_link' => '/edit-data/[table]/[id]',
	'metayota_sync_api_key' => '',
	'gemini_api_key' => '',
	'app_secret' => '',
        'jitsi_app_id' => 'sygeta_crm',
	'jitsi_app_secret' => '',
	'metayota_api_key' => ''

   ];
   $GLOBALS['metayota_config'] = $config;
   $GLOBALS['no_referer_check'] = ['/call/payment-webhook','/api/'];
   $GLOBALS['allowed_referers'] = ['http','https://metayota.com','https://default.sygeta.com','https://default.sygeta.com','https://www.default.sygeta.com']; // Auto-generated from Apache config // Please update this
?>
