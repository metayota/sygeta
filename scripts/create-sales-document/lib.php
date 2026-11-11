<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../slugify/slugify.php');  

    function createSalesDocument($document_type, $contact_id, $title = '') {
        global $db, $user_id;
        
        requireAccess('db.table.edit.owned','sales_document');

        $companySettings = $db->get('company_settings');
        $appSettings = $db->get('app_settings');
        $documentType = $db->getByName('sales_document_type', $document_type);
        $contact = $db->getById('contact', $contact_id);
        $lang = $contact['language'];

        // === Generate document number ===
        $template = $documentType['document_number_template']; // e.g. "BILL-{year}-####"
        $currentNumber = (int)$documentType['current_document_number']; // already the next number to use

        // Detect and count hash symbols to determine padding
        if (preg_match('/(#+)/', $template, $matches)) {
            $hashCount = strlen($matches[1]); // e.g. 4 for "####"
            $paddedNumber = str_pad($currentNumber, $hashCount, '0', STR_PAD_LEFT); // e.g. "0024"
            $documentNumber = preg_replace('/#+/', $paddedNumber, $template);
        } else {
            $documentNumber = $template;
        }

        // Replace dynamic placeholders
        $documentNumber = str_replace('{year}', date('Y'), $documentNumber);
        $documentNumber = str_replace('{month}', date('m'), $documentNumber);
        $documentNumber = str_replace('{day}', date('d'), $documentNumber);

        $translationKeyPrefix = 'document_'.slugifyWithUnderscore($documentNumber).'_';

        $currency = $db->get('currency', ['currency_code'=>$appSettings['default_currency']]);

        $companySettings['company_name'] = $companySettings['name_short'];
        $translationVars = array_merge($companySettings, $contact);

        $data = [];
        $data['intro_text'] = copyTranslations($documentType['intro_text_template'], $translationKeyPrefix.'intro_text', $translationVars);
        $data['bottom_text'] = copyTranslations($documentType['bottom_text_template'], $translationKeyPrefix.'bottom_text', $translationVars);
        $data['payment_terms'] = copyTranslations($documentType['payment_terms_template'], $translationKeyPrefix.'payment_terms', $translationVars);
        if (empty($title)) {
            $data['title'] = copyTranslations($documentType['title'], $translationKeyPrefix.'title', $translationVars);
        } else {
            $data['title'] = $title;
        }
        $data['user_id'] = $user_id;
        $data['date'] = date('Y-m-d'); 
        $data['status'] = 'draft';
        $data['currency'] = $currency['currency_code'];
        $data['document_type'] = $document_type;
        $data['contact_id'] = $contact_id;

        if (!empty($documentType['due_date_days'])) {
            $days = $documentType['due_date_days'];
            $data['due_date'] = date('Y-m-d', strtotime("+$days days"));
        }

        $data['document_number'] = $documentNumber;

        // Insert the document
        $db->insert('sales_document', $data);
        $documentID = $db->lastInsertId();

        // Now increment current_document_number AFTER usage
        $db->query(
            "UPDATE sales_document_type SET current_document_number = current_document_number + 1 WHERE name = :name",
            ['name' => $document_type]
        );

        return $documentID;
    }

    function generateCode($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $code;
    }
    function copyTranslations($translation_key,$new_translation_key) {
        global $db;
        global $user_id;
        $translations = $db->getAll('translation', ['translation_key'=>$translation_key]);
        $new_translation_key.= generateCode();
        foreach($translations as $translation) {
            unset($translation['id']);
            $translation['user_id'] = $user_id;
            $translation['translation_key'] = $new_translation_key;
       //     $translation['translation'] = translateLanguage($translation['language'], $translation_key, $vars);
            $db->insert('translation', $translation);
        }
        return $new_translation_key;
    }
?>