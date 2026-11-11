<?php
namespace Metayota;
include('../metayota/library.php');

$dataURL = $data['signature_image'];

// Prüfen, ob das Base64-Format korrekt ist
if (!preg_match('/^data:image\/png;base64,/', $dataURL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature format']);
    exit;
}

// Base64-Teil extrahieren
$base64 = str_replace('data:image/png;base64,', '', $dataURL);
$base64 = str_replace(' ', '+', $base64);
$binaryData = base64_decode($base64);

if ($binaryData === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Base64 decode failed']);
    exit;
}

// Sicheren Dateinamen generieren
$filename = 'signature_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.png';

// === KORREKTE ART DEN ORDNER ZU ERSTELLEN ===
$uploadDir = __DIR__ . '/../../uploads/signatures';

// Falls Ordner nicht existiert: erstellen
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);  // rekursiv + mit Rechten
}

$savePath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

// Datei speichern
$result = file_put_contents($savePath, $binaryData);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save signature']);
    exit;
}

echo json_encode([
    'success' => true,
    'filename' => $filename,
    'url' => '/uploads/signatures/' . $filename
]);
?>