<?php 

function notFound($name) {
    // Setze HTTP-Statuscode
    http_response_code(404);

    // Baue Antwort als Array
    $response = [
        'error' => 'not_found',
        'message' => $name . ' not found',
        'status' => 404
    ];

    // Header für JSON
    header('Content-Type: application/json; charset=utf-8');

    // JSON ausgeben und beenden
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    exit; // API execution ends here
}

?>