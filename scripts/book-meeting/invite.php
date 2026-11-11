<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');  
    include('../book-meeting/lib.php');
    include_once('../access-check/access-check.php');
    checkWritePermissions('contact', $data['contact_id']);    
    $meetingType = $db->getByName('meeting_type', $data['meeting_type']);
    $contact = $db->getById('contact',$data['contact_id']);

    $partner = $db->get('partner', ['user_id'=>$user_id]);
    $partnerAvailabilities = $db->getAll('partner_availability', ['partner_id'=>$partner['id']]);
    
    if (empty($partnerAvailabilities)) {
        echo json_encode(['type'=>'error','message'=>translate('fill_out_partner_availability'), 'link'=>'/availability','button_name'=>translate('btn_goto_availability')]);
        die();
    }

    if (!empty($meetingType['meeting_form']) && empty($data['form_data_included'])) {
        echo json_encode(['redirect'=>'/plan-meeting/'.$meetingType['name'].'/'.$contact['id']]);
        die();
    } else {
        $template = !empty($meetingType['custom_mail_template']) ? $meetingType['custom_mail_template'] : 'meeting_invitation';

        $data['meeting_type'] = $meetingType['name'];
        $duration = !empty($data['duration']) ? $data['duration'] : $meetingType['duration'];
        $data['duration'] = translateLanguage($contact['language'], 'minutes_duration', $duration);

        
    // var_dump($meetingType);
        if (!empty($meetingType['meeting_processor'])) {
        //   echo 'meeting processor not empty<br/>';
            $meetingProcessorGetVars = '../'.$meetingType['meeting_processor'].'/get_mail_variables.php';
            if (file_exists($meetingProcessorGetVars)) {
        //     echo 'file exists<br/>';
                $newVars = callWithVariables($meetingProcessorGetVars, ['contact'=>$contact]);
                $data = array_merge($newVars, $data);
            }
        }

        if (isset($data['form_data_included'])) {
            $meetingData = json_encode($data);
        } else {
            $meetingData = null;
        }

        $appSettings = $db->get('app_settings');
        $appLink = $appSettings['app_url'];

        $db->insert('meeting_invitation', ['sent_date'=>date("Y-m-d H:i:s"), 'receiver_contact_id'=>$contact['id'], 'meeting_type_id'=>$meetingType['id'], 'meeting_data'=>$meetingData]);
        
        $meetingInvitationID = $db->lastInsertId();

        $sender = new MailContactSender();
        $sender->addContact($contact['id']);
        $data['book_meeting_link'] = $appLink."book-meeting/".$meetingType['name']."/".$meetingInvitationID."#key=".$contact['access_key'];
        $sender->setTemplate($template,$data);
        $sender->send();




        success('mail_sent_to_customer');
    }
?>