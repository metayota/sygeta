<?php
    namespace Metayota;
    include_once('../metayota/library.php');
    include_once('../mail-contact-sender/mail-contact-sender.php');
    include_once('../translation-service/translation-service.php');

    $parameters = json_decode($data['step_action']['parameters'], true);
    $meetingType = $db->getByName('meeting_type', $parameters['meeting_type']);

?>
    <div class="visualize-step-action visualize-meeting-type">
        <u><?= translate($meetingType['title']) ?></u>
        <table>
            <tr>
                <th>
                    <?= translate('description') ?>
                </th>
                <td>
                    <?= translate($meetingType['description']) ?>
                </td> 
            </tr>
            <tr>
                <th>
                    <?= translate('resource_meeting_type_title_duration') ?>
                </th>
                <td>
                    <?= $meetingType['duration'] ?> <?= translate('minutes') ?>
                </td> 
            </tr>
            <tr>
                <th>
                    <?= translate('resource_meeting_type_title_preparation_time') ?>
                </th>
                <td>
                    <?= $meetingType['preparation_time'] ?> <?= translate('minutes') ?>
                </td> 
            </tr>
            <tr>
                <th>
                    <?= translate('resource_meeting_type_title_post_buffer_time') ?>
                </th>
                <td>
                    <?= $meetingType['post_buffer_time'] ?> <?= translate('minutes') ?>
                </td> 
            </tr>
            
        </table> 
    </div>