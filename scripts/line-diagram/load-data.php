<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    include_once('../access-check/access-check.php');
    checkReadPermissions('line_diagram', $data['diagram_id']);
try {
    // Validate required diagram_id parameter
    if (!isset($data['diagram_id']) || !is_numeric($data['diagram_id'])) {
        throw new \Exception('Invalid or missing diagram_id parameter');
    }

    $diagramId = (int)$data['diagram_id'];

    // Get diagram details
    $diagram = $db->getById('line_diagram', $diagramId);
    if (!$diagram) {
        throw new \Exception('Diagram not found');
    }

    // Get all items for this diagram, including series_id
    $items = $db->getAllByQuery(
        "SELECT DISTINCT series_id 
         FROM line_diagram_item 
         WHERE diagram_id = :diagram_id",
        ['diagram_id' => $diagramId]
    );

    // Initialize an array to hold the series data
    $seriesData = [];

    // For each series_id, fetch the corresponding series title and data points
    foreach ($items as $item) {
        $seriesId = $item['series_id'];

        // Fetch series title by series_id
        $series = $db->getById('line_diagram_series', $seriesId);
        if (!$series) {
            continue;  // Skip if the series doesn't exist
        }

        // Fetch all points for the current series
        $seriesPoints = $db->getAllByQuery(
            "SELECT x_value, y_value 
             FROM line_diagram_item 
             WHERE diagram_id = :diagram_id AND series_id = :series_id 
             ORDER BY x_value",
            [
                'diagram_id' => $diagramId,
                'series_id' => $seriesId
            ]
        );

        // Initialize the series data structure if it's not already
        $seriesData[] = [
            'name' => translate($series['title']),
            'values' => array_map(function($point) {
                return [
                    'x' => floatval($point['x_value']),
                    'y' => floatval($point['y_value'])
                ];
            }, $seriesPoints)
        ];
    }

    // Format final response
    $response = [
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
    ];

    echo json_encode($response);

} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
