<?php 
namespace Metayota; 
include_once('../metayota/library.php');   
include_once('../translation-service/translation-service.php');
include_once('../track-link/lib.php');
include_once('../contacts-lib/contacts-lib.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailContactSender {
    public $receiver;
    private $mail;
    private $mailConfig;
    private $subject;
    private $text;
    private $isHtml = false;
    private $templateName;
    private $userId;
    public $templateVars = [];
    private $contactID;
    private $contact;
    private $language;
    private $appSettings; // Added to store app settings
    private $tempFiles = []; // Added for temp file cleanup
    
    public function __construct() {
        global $db;
        global $user_id;
        
        // Get app settings for image handling
        $this->appSettings = $db->get('app_settings');
        
        if (empty($user_id)) {
            $sql = "SELECT * FROM `email_account` WHERE `user_id` IS NULL";
            $this->mailConfig = $db->getByQuery($sql);
        } else {
            $sql = "SELECT * FROM `email_account` WHERE `user_id` = :user_id OR `user_id` IS NULL ORDER BY `user_id` DESC LIMIT 1;";
            $this->mailConfig = $db->getByQuery($sql, ['user_id'=>$user_id]);
        }
        
        require '../../vendor/autoload.php';  // Update this path if needed
        $this->mail = new PHPMailer(true);
    }
    
    function replaceVariables($text, $parameters = []) {
        if (is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                if (!is_null($value)) {
                    $text = str_replace("{" . $key . "}", $value, $text);
                }
            }
        } else {
            if (!is_null($parameters)) {
                $text = preg_replace("/\{(.*?)\}/", $parameters, $text);
            }
        }
        
        return $text;
    }
    
    /**
    * Process HTML content to embed images as CID attachments
    */
    private function processImagesForEmail($htmlContent) {
        if (!$this->isHtml || empty($htmlContent)) {
            return $htmlContent;
        }
        
        // Create a DOMDocument to parse HTML
        $dom = new \DOMDocument();
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $htmlContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        
        $images = $dom->getElementsByTagName('img');
        $cidCounter = 0;
        $tempFiles = []; // Keep track of temp files for cleanup
        
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            
            // Decode HTML entities in the src attribute
            $src = html_entity_decode($src);
            
            // Check if the image source is a relative path
            if ($this->isRelativeUrl($src)) {
                $imageInfo = $this->getFullImagePath($src);
                
                if ($imageInfo && isset($imageInfo['path']) && file_exists($imageInfo['path'])) {
                    $cidCounter++;
                    $cid = 'image' . $cidCounter;
                    
                    // Add image as inline attachment with proper MIME type
                    $this->mail->addEmbeddedImage(
                    $imageInfo['path'],
                    $cid,
                    basename($imageInfo['path']), // filename
                    'base64', // encoding
                    $imageInfo['mime'] // MIME type
                    );
                    
                    // Keep track of temp files for later cleanup
                    if (strpos($imageInfo['path'], sys_get_temp_dir()) === 0) {
                        $tempFiles[] = $imageInfo['path'];
                    }
                    
                    // Update the src attribute to use CID reference
                    $img->setAttribute('src', 'cid:' . $cid);
                } else {
                    error_log("Image file not found or download failed: " . $src);
                }
            }
        }
        
        // Store temp files for cleanup after sending
        $this->tempFiles = $tempFiles;
        
        // Return the modified HTML
        return $dom->saveHTML();
    }
    
    /**
    * Check if URL is relative
    */
    private function isRelativeUrl($url) {
        // Check if URL is relative (doesn't start with http:// or https:// or //)
        return !preg_match('/^(https?:)?\/\//', $url) && !empty($url);
    }
    
    /**
    * Convert relative path to full file system path or download from URL
    */
    private function getFullImagePath($relativePath) {
        // Remove leading slash if present for processing
        $cleanPath = ltrim($relativePath, '/');
        
        // If it's a direct uploads path, handle it locally
        if (strpos($cleanPath, 'uploads/') === 0) {
            return $this->getLocalImagePath($cleanPath);
        }
        
        // For other relative URLs (like /call/image.resize), download from website
        return $this->downloadImageFromUrl($relativePath);
    }
    
    /**
    * Get local file path for uploads directory
    */
    private function getLocalImagePath($relativePath) {
        // Try different possible paths relative to the current file location
        $possiblePaths = [
            dirname(__FILE__) . '/../../' . $relativePath,
            dirname(__FILE__) . '/../../uploads/' . ltrim($relativePath, 'uploads/'),
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                // For local files, detect MIME type using finfo
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $path);
                finfo_close($finfo);
                
                return [
                    'path' => $path,
                    'mime' => $mimeType
                ];
            }
        }
        
        // Fallback - construct from app_url
        if (!empty($this->appSettings['app_url'])) {
            $parsedUrl = parse_url($this->appSettings['app_url']);
            if (isset($parsedUrl['path']) && $parsedUrl['path'] !== '/') {
                $appPath = dirname(__FILE__) . '/../../' . ltrim($parsedUrl['path'], '/');
                $fullPath = $appPath . $relativePath;
                if (file_exists($fullPath)) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $fullPath);
                    finfo_close($finfo);
                    
                    return [
                        'path' => $fullPath,
                        'mime' => $mimeType
                    ];
                }
            }
        }
        
        return false;
    }
    
    private function injectTrackingLinks(): void
    {
        global $db;
        
        if (empty($this->contactID)) {
            return;
        }
        
        // Alle Tracking-Links laden
        $trackingLinks = $db->getAll('tracking_link');
        
        if (empty($trackingLinks)) {
            return;
        }
        
        foreach ($trackingLinks as $link) {
            if (empty($link['id']) || empty($link['name'])) {
                continue;
            }
            
            // Code erzeugen (tracking_id + contact_id)
            $code = \Metayota\TrackingLink::encode(
            (int)$link['id'],
            (int)$this->contactID
            );
            
            // Vollständige URL
            $fullUrl = rtrim($this->appSettings['app_url'], '/') . '/go/' . $code;
            
            // Template-Variable setzen
            $this->templateVars[$link['name']] = $fullUrl;
        }
    }
    
    
    /**
    * Download image from URL and return temporary file path with MIME type
    */
    private function downloadImageFromUrl($relativePath) {
        // Construct full URL using app_url
        $fullUrl = rtrim($this->appSettings['app_url'], '/') . '/' . ltrim($relativePath, '/');
        
        // Create temp directory if it doesn't exist
        $tempDir = sys_get_temp_dir() . '/mail_images';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Generate unique filename for temp file
        $tempFile = $tempDir . '/img_' . md5($fullUrl) . '_' . time();
        
        try {
            // Use cURL to get both content and headers
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fullUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MailSender/1.0)');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_NOBODY, false);
            
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            
            if ($imageData === false || $httpCode !== 200) {
                error_log("Failed to download image from: " . $fullUrl . " (HTTP: " . $httpCode . ")");
                return false;
            }
            
            // Validate that it's actually an image
            if (!$this->isValidImageContentType($contentType)) {
                error_log("Downloaded content is not a valid image: " . $contentType . " from " . $fullUrl);
                return false;
            }
            
            // Add proper file extension based on content type
            $extension = $this->getExtensionFromContentType($contentType);
            if ($extension) {
                $tempFile .= '.' . $extension;
            }
            
            // Save to temp file
            if (file_put_contents($tempFile, $imageData) === false) {
                error_log("Failed to save temp image file: " . $tempFile);
                return false;
            }
            
            // Return both file path and MIME type
            return [
                'path' => $tempFile,
                'mime' => $contentType
            ];
            
        } catch (Exception $e) {
            error_log("Error downloading image from " . $fullUrl . ": " . $e->getMessage());
            return false;
        }
    }
    
    /**
    * Check if content type is a valid image type
    */
    private function isValidImageContentType($contentType) {
        $validTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'image/bmp',
            'image/tiff',
            'image/avif'
        ];
        
        // Extract main content type (remove charset etc.)
        $mainType = explode(';', $contentType)[0];
        return in_array(strtolower(trim($mainType)), $validTypes);
    }
    
    /**
    * Get file extension from content type
    */
    private function getExtensionFromContentType($contentType) {
        $mainType = explode(';', $contentType)[0];
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'image/bmp' => 'bmp',
            'image/tiff' => 'tiff',
            'image/avif' => 'avif'
        ];
        
        return $extensions[strtolower(trim($mainType))] ?? null;
    }
    
    /**
    * Clean up temporary files after sending email
    */
    private function cleanupTempFiles() {
        if (isset($this->tempFiles)) {
            foreach ($this->tempFiles as $tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }
            $this->tempFiles = [];
        }
    }
    
    public function addReceiver($receiver) {
        $this->receiver = $receiver;
    }
    
    function getAllMailTextsForUser($contactId, $templateName, $language) {
        global $db;
        $sql = "
        SELECT name, translation FROM (
        SELECT mt.name, t.translation,
        ROW_NUMBER() OVER (
        PARTITION BY mt.name
        ORDER BY
        mt.mail_template IS NOT NULL DESC,
        CASE
        WHEN mt.contact_group IS NOT NULL AND cgm.contact_id IS NOT NULL THEN 2
        WHEN mt.contact_group IS NULL THEN 1
        ELSE 0
        END DESC,
        RAND()
        ) as rn
        FROM mail_text mt
        LEFT JOIN contact_group cg ON mt.contact_group = cg.name
        LEFT JOIN contact_group_member cgm ON cg.id = cgm.contact_group_id AND cgm.contact_id = :contactId
        LEFT JOIN translation t ON mt.content = t.translation_key AND t.language = :language
        WHERE (mt.mail_template = :templateName OR mt.mail_template IS NULL)
        AND (
        mt.contact_group IS NULL
        OR cgm.contact_id = :contactId
        )
        ) ranked
        WHERE rn = 1
        ";
        
        $results = $db->getAllByQuery($sql, array(
        'contactId' => $contactId,
        'templateName' => $templateName,
        'language' => $language
        ));
        
        // In Key-Value Array umwandeln
        $mailTexts = array();
        foreach ($results as $row) {
            $mailTexts[$row['name']] = $row['translation'];
        }
        
        return $mailTexts;
    }
    
    public function addContact($contactID) {
        global $db;
        if (is_array($contactID)) {
            $contact = $contactID;
        } else {
            $contact = $db->getById('contact', $contactID);
        }
        $this->contact = $contact;
        if (!empty($contact['partner_id'])) {
            $partner = $db->getById('partner', $contact['partner_id']);
            if (!empty($partner['contact_id'])) {
                $partnerContact = $db->getById('contact', $partner['contact_id']);
                $this->partnerContact = $partnerContact;
            }
        }
        
        $appSettings = $db->get('app_settings');
        $language = $contact['language'];
        
        if ($contact && isset($contact['email'])) {
            $this->receiver = $contact['email'];
            
            if (is_array($contactID)) {

                $this->contactID = $contactID['id'];
            } else {
                $this->contactID = $contactID;
            }

            $this->language = $language;
            
            // Automatically set template variables from user object
            if (!empty($contact['firstname'])) {
                $this->templateVars['firstname'] = $contact['firstname'];
            }
            if (!empty($contact['lastname'])) {
                $this->templateVars['lastname'] = $contact['lastname'];
            }
            if (!empty($contact['company'])) {
                $this->templateVars['company'] = $contact['company'];
            }
            $this->templateVars['salutation'] = getSalutation($contact);
            if (!empty($partnerContact)) {
                $this->templateVars['partner_firstname'] = $partnerContact['firstname'];
                $this->templateVars['partner_lastname'] = $partnerContact['lastname'];
                $this->templateVars['partner_phone'] = $partnerContact['phone'];
                $this->templateVars['partner_full_name'] = $partnerContact['full_name'];
                $this->templateVars['partner_mail'] = $partnerContact['email'];
            }
            $this->templateVars['access_key'] = $contact['access_key'];
            $this->templateVars['app_link'] = $appSettings['app_url'];
            $this->templateVars['app_name'] = translateLanguage($language, $appSettings['app_name']);
            $this->templateVars['year'] = date('Y');
            $this->templateVars['company_name'] = translateLanguage($language, $appSettings['company_name']);
            $companySettings = $db->get('company_settings');
            foreach($companySettings as $companySettingKey => $companySettingValue) {
                $this->templateVars['company_'.$companySettingKey] = $companySettingValue;
            }
            $this->collectTexts();
        } else {
            error_log("Contact is not valid");
            return false;
        }
    }
    
    private function collectTexts() {
        if (!empty($this->contactID) && !empty($this->templateName)) {
            $mailTexts = $this->getAllMailTextsForUser($this->contactID, $this->templateName,$this->language);
            $this->templateVars = array_merge($this->templateVars, $mailTexts);
        }
    }
    
    public function addAttachment($filePath, $newFilename='') {
        $this->mail->addAttachment($filePath, $newFilename);
    }
    
    public function setSubject($subject) {
        $this->subject = $subject;
    }
    
    public function setText($text) {
        $this->text = $text;
    }
    
    public function setHTML($html) {
        $this->text = $html;
        $this->isHtml = true;
    }
    
    public function enableHTML() {
        $this->isHtml = true;
    }
    
    public function setTemplate($templateName, $vars = []) {
        $this->originalVars = $vars;
        $this->templateName = $templateName;
        $this->templateVars = array_merge($this->templateVars, $vars); // Merge with existing vars
        $this->collectTexts();
    }
    
    private function shouldSendMail() {
        global $db;
        if ($this->contactID && $this->templateName) {
            $settings = $db->get('mail_setting', [
                'contact_id' => $this->contactID,
                'type_name' => $this->templateName
            ]);
            
            if ($settings && isset($settings['receive_mail']) && $settings['receive_mail'] == 0) {
                return false;
            }
        }
        return true;
    }
    
    public function checkTemplateVars($template) {
        global $db;
        if ($template) {
            // Decode the existing template_variables JSON
            $existingVars = json_decode($template['template_variables'], true) ?: [];
            
            // Get the keys from $this->templateVars
            $currentVarKeys = array_keys($this->templateVars);
            
            // Check if all keys from $this->templateVars exist in the template_variables
            $missingKeys = array_diff($currentVarKeys, $existingVars);
            
            if (!empty($missingKeys)) {
                // Merge existing variables with new ones (avoiding duplicates)
                $updatedVars = array_unique(array_merge($existingVars, $currentVarKeys));
                
                // Update the template with the new template_variables
                $updateData = array(
                'template_variables' => json_encode(array_values($updatedVars))
                );
                
                $where = array('id' => $template['id']);
                $db->update('mail_template', $updateData, $where);
            }
        }
    }
    
    /**
     * Prepare email content (subject and body) without sending
     * @return array|false Returns array with 'subject', 'body', 'is_html', and 'template' keys, or false on error
     */
    public function prepareEmailContent($processImages=true) {
        global $db;

        if (!$this->shouldSendMail()) {
            msg('Email not sent: Disabled by mail settings for template ' . $this->templateName);
            return false;
        }
        
        if (getConfig('mailer_unsubscribe')) {
            /*$unsubscribed = $db->get('unsubscribed_mail', ['mail' => $this->receiver, 'mail_type' => 'all_mail']);
            if (!empty($unsubscribed)) {
                msg('Email not sent: Recipient ' . $this->receiver . ' has unsubscribed from all mail');
                return false;
            }*/
        }
        
        $contact = $this->contact;
        $code = $contact['access_key'];
        $http_host = $_SERVER['HTTP_HOST'];
        $mailType = $this->templateName ? $this->templateName : 'all_mail';
        $template = null;
        
        // Load template if provided and set translations
        if ($this->templateName) {
            $template = $db->getByQuery('SELECT * FROM mail_template WHERE name=:template_name ORDER BY RAND()', ['template_name'=>$this->templateName]);
            if (!$template) {
                msg('Email not sent: Template ' . $this->templateName . ' not found');
                return false;
            }
            $this->checkTemplateVars($template);
            $baseTemplate = getConfig('mail_template','mail-template');
            $language = $contact['language'] ?? getConfig('main_language');
            $this->subject = translateLanguage($language, $template['subject'], $this->templateVars);
            
            $replacedMsg = translateLanguage($language, $template['message'], $this->templateVars);
            
            $this->injectTrackingLinks();
            $replacedMsg = $this->replaceVariables($replacedMsg, $this->templateVars);
            if (str_contains($replacedMsg,'</p>')) {
                $this->text = $replacedMsg;
            } else {
                $this->text = $this->isHtml ? $replacedMsg : $replacedMsg;
            }
            
            $unsubscribePath = getConfig('mail_unsubscribe_path','/call/unsubscribe');
            if (getConfig('wordpress')) {
                $emailUnsubscribeLink = 'https://' . $http_host . '/wp-content/plugins/metayota/scripts/unsubscribe/unsubscribe.php?contact_id=' . $contact['id'] . '&access_key=' . $code . '&type='.$mailType;
            } else {
                $emailUnsubscribeLink = 'https://' . $http_host . $unsubscribePath.'?contact_id=' . $this->contactID . '&access_key=' . $code . '&type='.$mailType;
            }
            
            $emailUnsubscribeLink = 'https://' . $http_host . '/call/mail-settings/unsubscribe?contact_id=' . $this->contactID . '&access_key=' . $code . '&mail_type='.$template['name'];
            $mailSettingsLink = 'https://' . $http_host . '/mail-settings#access_key=' . $code;
            $this->templateVars['unsubscribe_link'] = $emailUnsubscribeLink;
            $this->templateVars['mail_settings_link'] = $mailSettingsLink;
            
            if (!empty($baseTemplate)) {
                $this->templateVars['language'] = $language;
                $this->templateVars['content'] = $this->text;
                if (isset($this->partnerContact)) {
                    $this->templateVars['partnerContact'] = $this->partnerContact;
                }
                $baseTemplate = callAction($baseTemplate,'html',$this->templateVars);
                $this->text = $baseTemplate;
            }
            $this->isHtml = true;
        }
        
        // Process images for email embedding
        if ($processImages) {
            $processedText = $this->processImagesForEmail($this->text);
        } else {
            $processedText = $this->text;
        }
        
        return [
            'subject' => $this->subject,
            'body' => $processedText,
            'is_html' => $this->isHtml,
            'template' => $template
        ];
    }
    
    public function send() {
        global $db;
        
        // Prepare email content using the new method
        $emailContent = $this->prepareEmailContent();
        
        if ($emailContent === false) {
            return false;
        }
        
        try {
            // Server settings
            $this->mail->isSMTP();
            if (!empty($debugMode)) {
                $this->mail->SMTPDebug = 2;
            }
            $this->mail->Host       = $this->mailConfig['smtp_host'];
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $this->mailConfig['imap_username'];
            $this->mail->Password   = $this->mailConfig['imap_password'];
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port       = $this->mailConfig['smtp_port'];
            $this->mail->CharSet    = 'UTF-8';
            
            // Recipients
            $this->mail->setFrom($this->mailConfig['email'], $this->mailConfig['from_name']);
            $this->mail->addAddress($this->receiver);
            $this->mail->addReplyTo($this->mailConfig['reply_to_email'], $this->mailConfig['reply_to_name']);
            
            $contact = $db->getById('contact', $this->contactID);
            $code = $contact['access_key'];
            $http_host = $_SERVER['HTTP_HOST'];
            $mailType = $this->templateName ? $this->templateName : 'all_mail';
            
            $unsubscribePath = getConfig('mail_unsubscribe_path','/call/unsubscribe');
            if (getConfig('wordpress')) {
                $emailUnsubscribeLink = 'https://' . $http_host . '/wp-content/plugins/metayota/scripts/unsubscribe/unsubscribe.php?contact_id=' . $contact['id'] . '&access_key=' . $code . '&type='.$mailType;
            } else {
                $emailUnsubscribeLink = 'https://' . $http_host . $unsubscribePath.'?contact_id=' . $this->contactID . '&access_key=' . $code . '&type='.$mailType;
            }
            
            // Add List-Unsubscribe header if template is being used
            if ($this->templateName && isset($emailContent['template'])) {
                $template = $emailContent['template'];
                $emailUnsubscribeLink = 'https://' . $http_host . '/call/mail-settings/unsubscribe?contact_id=' . $this->contactID . '&access_key=' . $code . '&mail_type='.$template['name'];
                $this->mail->addCustomHeader('List-Unsubscribe', '<' . $emailUnsubscribeLink . '>');
            }
            
            // Content - use prepared content
            $this->mail->isHTML($emailContent['is_html']);
            $this->mail->Subject = $emailContent['subject'];
            $this->mail->Body    = $emailContent['body'];
            
            // Insert mail message record
            $data = [
                'receiver_contact_id' => $this->contactID,
                'receiver_mail' => $this->receiver,
                'subject' => $emailContent['subject'],
                'message' => $emailContent['is_html'] ? strip_tags($emailContent['body']) : $emailContent['body'],
                'message_html' => $emailContent['is_html'] ? $emailContent['body'] : null,
                'receiver_customer_id' => null,
                'mail_template_id' => $emailContent['template']['id'] ?? null,
                'mail_template_name' => $this->templateName
            ];
            
            if (!empty($this->originalVars)) {
                $data['template_variables'] = json_encode($this->originalVars);
            }
            
            $db->insert('mail_message', $data);
            
            $this->mail->send();
            
            // Clean up temporary files after successful send
            $this->cleanupTempFiles();
            
            return true;
        } catch (Exception $e) {
            // Clean up temporary files even on failure
            $this->cleanupTempFiles();
            
            msg('Email sending failed: ' . $this->mail->ErrorInfo);
            return "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }
}
?>