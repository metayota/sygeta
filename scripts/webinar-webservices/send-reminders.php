<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-helper/mail-helper.php');   include_once('../translation-service/translation-service.php');  
try {
    echo "Starting reminder process...<br/>";

    // Get upcoming events within next 48 hours
    echo "Fetching upcoming events...<br/>";
    $upcomingEvents = $db->getAllByQuery(
        "SELECT wd.*, w.title, w.description 
         FROM event_date wd
         JOIN event w ON w.id = wd.event_id
         WHERE wd.start_date BETWEEN NOW() 
         AND DATE_ADD(NOW(), INTERVAL 48 HOUR)",
        []
    );

    if (empty($upcomingEvents)) {
        echo "No upcoming events found.<br/>";
    }

    foreach ($upcomingEvents as $event) {
        echo "Processing event: " . $event['title'] . "<br/>";
        
        $hoursUntilStart = (strtotime($event['start_date']) - time()) / 3600;
        echo "Hours until start: " . $hoursUntilStart . "<br/>";

        // First reminder: 24 hours before (reminder = 0 or NULL)
        if ($hoursUntilStart <= 24 && $hoursUntilStart > 12) {
            echo "Sending first reminder...<br/>";
            $participants = $db->getAllByQuery(
                "SELECT wp.*, c.* 
                 FROM event_participant wp
                 JOIN contact c ON c.id = wp.contact_id
                 WHERE wp.event_id = :event_id 
                 AND (wp.reminder IS NULL OR wp.reminder = 0)
                 AND wp.attended IS NULL",
                ['event_id' => $event['event_id']]
            );

            if (empty($participants)) {
                echo "No participants for first reminder.<br/>";
            }

            foreach ($participants as $participant) {
                echo "Sending to participant: " . $participant['full_name'] . "<br/>";
                $vars = [
                    'event_title' => translate($event['title']),
                    'event_description' => $event['description'],
                    'event_date' => $event['start_date'],
                    'event_link' => $event['event_link'],
                    'participant_name' => $participant['full_name']
                ];

                sendMailHelper($participant['contact_id'], 'event_first_reminder', $vars);
                echo "First reminder sent successfully.<br/>";
                $db->update('event_participant',
                    ['reminder' => 1],
                    [
                        'contact_id' => $participant['contact_id'],
                        'event_id' => $event['event_id']
                    ]
                );


            }
        }

        // Final reminder: 12 hours before (reminder = 1)
        if ($hoursUntilStart <= 12 && $hoursUntilStart > 0) {
            echo "Sending final reminder...<br/>";
            $participants = $db->getAllByQuery(
                "SELECT wp.*, c.* 
                 FROM event_participant wp
                 JOIN contact c ON c.id = wp.contact_id
                 WHERE wp.event_id = :event_id 
                 AND wp.reminder = 1
                 AND wp.attended IS NULL",
                ['event_id' => $event['event_id']]
            );

            if (empty($participants)) {
                echo "No participants for final reminder.<br/>";
            }

            $phpDatetimeFormat = translate('php_datetime_format');
            $eventDateFormatted = date($phpDatetimeFormat, strtotime($event['start_date']));

            foreach ($participants as $participant) {
                echo "Sending to participant: " . $participant['full_name'] . "<br/>";
                $vars = [
                    'event_title' => translate($event['title']),
                    'event_description' => $event['description'],
                    'event_date' => $eventDateFormatted,
                    'event_link' => $event['event_link'],
                    'participant_name' => $participant['full_name']
                ];

            sendMailHelper($participant['contact_id'], 'event_final_reminder', $vars);
                echo "Final reminder sent successfully.<br/>";
                $db->update('event_participant',
                    ['reminder' => 2],
                    [
                        'contact_id' => $participant['contact_id'],
                        'event_id' => $event['event_id']
                    ]
                );
                echo 'Final reminder sent';
    
            }
        }
    }

    echo "All reminders processed.<br/>";
    echo json_encode([
        'status' => 'success',
        'message' => 'Reminders processed successfully'
    ]);
    echo "<br/>";

} catch (\Exception $e) {
    echo "Error caught: " . $e->getMessage() . "<br/>";
    echo 'Error sending reminders';
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to process reminders: ' . $e->getMessage()
    ]);
    echo "<br/>";
}
?>