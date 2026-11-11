<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
try {
    if (!isset($user_id)) {
        throw new \Exception('Missing user_id');
    }

    // Hole Partner-ID für diesen User
    $partner = $db->get('partner', ['user_id' => $user_id]);
    if (!$partner) {
        throw new \Exception('Partner not found');
    }
    $partnerID = $partner['id'];
    

    // Hole Titel und Beschreibung aus einer statischen Konfiguration oder per ID
    $diagram = [
        'title' => translate('sales_activity_per_day_title'),
        'description' => translate('sales_activity_per_day_description'),
    ];

    // Hole aggregierte Aktivitäten pro Tag für diesen Partner
    $results = $db->getAllByQuery("
        SELECT activity_date AS x_value, COUNT(*) AS y_value
        FROM contact_activity
        WHERE partner_id = :partner_id
        GROUP BY activity_date
        ORDER BY activity_date
    ", ['partner_id' => $partnerID]);

    $seriesData = [[
        'name' => 'Aktivitäten',
        'values' => array_map(function($row) {
            return [
                'x' => strtotime($row['x_value']) * 1000, // JS erwartet meist Millisekunden
                'y' => (int)$row['y_value']
            ];
        }, $results)
    ]];

    echo json_encode([
        'status' => 'success',
        'data' => [
            'diagram' => [
                'title' => $diagram['title'],
                'description' => $diagram['description'],
                'title_translated' => translate($diagram['title']),
                'description_translated' => translate($diagram['description'])
            ],
            'series' => $seriesData
        ]
    ]);

} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
