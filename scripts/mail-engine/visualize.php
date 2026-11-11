<?php
    namespace Metayota;
    include_once('../metayota/library.php');
    include_once('../mail-contact-sender/mail-contact-sender.php');
    include_once('../translation-service/translation-service.php');

    $parameters = json_decode($data['step_action']['parameters'], true);
    $mailTemplate = $db->getByName('mail_template', $parameters['mail_template_name']);

    global $language;

    $mailer = new MailContactSender();
    $sampleContact = $db->getById('contact', $loggedInUser['contact_id']);
    $sampleContact['language'] = $language;
    $sampleContact['firstname'] = translate('sample_firstname');
    $sampleContact['lastname'] = translate('sample_lastname');
    $mailer->addContact($sampleContact);
    $mailer->setTemplate($parameters['mail_template_name']);
    $content = $mailer->prepareEmailContent(false);


    $dom = new \DOMDocument();

    // HTML laden (Fehler unterdrücken wegen evtl. ungültigem HTML)
    @$dom->loadHTML($content['body']);

    $body = $dom->getElementsByTagName('body')->item(0);

    $bodyInnerHTML = '';
    foreach ($body->childNodes as $child) {
        $bodyInnerHTML .= $dom->saveHTML($child);
    }
    echo '<p>'.translate('visualize_following_email_sent').'</p>';
    echo '<u class="visualize-mail-subject">'.$content['subject']."</u>";
    echo '<div class="visualize-mail-content">'.$bodyInnerHTML."</div>";

?>