<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailSender {
    private $mail;
    private $mailConfig;
    private $receiver;
    private $subject;
    private $text;
    private $isHtml = false;
    private $templateName;
    private $userId;
    private $templateVars = [];

    public function __construct() {
        global $db;
        global $user_id;
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

    public function addReceiver($receiver) {
        $this->receiver = $receiver;
    }

    public function addUser($userId) {
        global $db;
        $user = $db->getById('user', $userId);
        if ($user && isset($user['email'])) {
            $this->receiver = $user['email'];
            $this->userId = $userId;

            // Automatically set template variables from user object
            if (!empty($user['username'])) {
                $this->templateVars['username'] = $user['username'];
            }
            if (!empty($user['firstname'])) {
                $this->templateVars['firstname'] = $user['firstname'];
            }
            if (!empty($user['lastname'])) {
                $this->templateVars['lastname'] = $user['lastname'];
            }
        }
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
        $this->templateName = $templateName;
        $this->templateVars = array_merge($this->templateVars, $vars); // Merge with existing vars
    }

    private function shouldSendMail() {
        global $db;
        if ($this->userId && $this->templateName) {
            $settings = $db->get('mail_settings', [
                'user_id' => $this->userId,
                'type_name' => $this->templateName
            ]);
            if ($settings && isset($settings['receive_mail']) && $settings['receive_mail'] == 0) {
                return false;
            }
        }
        return true;
    }

    public function send() {
        global $db;

        if (!$this->shouldSendMail()) {
            return; // Skip sending email based on mail settings
        }

        if (getConfig('mailer_unsubscribe')) {
            $unsubscribed = $db->get('unsubscribed_mail', ['mail' => $this->receiver, 'mail_type' => 'all_mail']);
            if (!empty($unsubscribed)) {
                return;
            }
        }

        try {
            // Server settings
            $this->mail->isSMTP();
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

            if (getConfig('mailer_unsubscribe')) {
                $hash = md5($this->receiver . '_unsubscribe_secret_key_2ECMENQ9340');
                $http_host = $_SERVER['HTTP_HOST'];
                if (getConfig('wordpress')) {
                    $emailUnsubscribeLink = 'https://' . $http_host . '/wp-content/plugins/metayota/scripts/unsubscribe/unsubscribe.php?email=' . urlencode($this->receiver) . '&hash=' . $hash . '&type=all_mail';
                } else {
                    $emailUnsubscribeLink = 'https://' . $http_host . '/call/unsubscribe?email=' . urlencode($this->receiver) . '&hash=' . $hash . '&type=all_mail';
                }

                
            }



            // Load template if provided and set translations
            if ($this->templateName) {
                $template = $db->getByQuery('SELECT * FROM mail_template WHERE name=:template_name ORDER BY RAND()', ['template_name'=>$this->templateName]);
                if ($template) {
                    $baseTemplate = getConfig('mail_template');
                    $user = $db->getById('user', $this->userId);
                    $language = $user['language'] ?? getConfig('main_language'); // Default to 'en' if language is not set
                    $this->subject = translateLanguage($language, $template['mail_subject'], $this->templateVars);
                    $this->text = $this->isHtml ? nl2br(translateLanguage($language, $template['mail_message'], $this->templateVars)) : nl2br(translateLanguage($language, $template['mail_message'], $this->templateVars));
                    $userID = $user['id'];
                    $appSecret = getConfig('APPLICATION_SECRET','DFASI903324DS');
                    $code = md5($userID.$appSecret);
                    $http_host = $_SERVER['HTTP_HOST'];
                    $emailUnsubscribeLink = 'https://' . $http_host . '/call/mail-settings/unsubscribe?user_id=' . $userID . '&code=' . $code . '&mail_type='.$template['name'];
                    $mailSettingsLink = 'https://' . $http_host . '/mail-settings#access_key=' . $code;
                    $this->templateVars['unsubscribe_link'] = $emailUnsubscribeLink;
                    $this->templateVars['mail_settings_link'] = $mailSettingsLink;
                    if (!empty($baseTemplate)) {
                        $this->templateVars['language'] = $language;
                        $this->templateVars['content'] = $this->text;
                        $baseTemplate = callAction($baseTemplate,'html',$this->templateVars);
                        $this->text = $baseTemplate;
                    }
                    $this->isHtml = true;
                    $this->mail->addCustomHeader('List-Unsubscribe', '<' . $emailUnsubscribeLink . '>');
                } 
            }

            // Content
            
            $this->mail->isHTML($this->isHtml);
            $this->mail->Subject = $this->subject;
            $this->mail->Body    = $this->text;

            @$this->mail->send();
            return true;
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }
}
?>