<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  

header('Content-Type: application/json');

// Partnerdaten aus der Datenbank abrufen
$partners = $db->getAll('partner');

// Indexieren nach ID zur schnelleren Verarbeitung
$indexed = [];
foreach ($partners as $partner) {
    $indexed[$partner['id']] = [
        'id' => $partner['id'],
        'supervisor_partner_id' => $partner['supervisor_partner_id'],
        'name' => $partner['full_name'],
        'title' => translate($partner['role_name']),
        'subtitle' => number_format($partner['commission'], 2) . ' %',
        'children' => []
    ];
}

// Baumstruktur aufbauen
$tree = [];
foreach ($indexed as $id => &$node) {
    if ($node['supervisor_partner_id'] !== null && isset($indexed[$node['supervisor_partner_id']])) {
        $indexed[$node['supervisor_partner_id']]['children'][] = &$node;
    } else {
        $tree[] = &$node;
    }
}

// Ergebnis vorbereiten
$orgChart = [
    'status' => 'success',
    'data' => [
        'tree' => $tree[0] ?? null // wenn mehrere Wurzeln existieren, kann das angepasst werden
    ]
];

echo json_encode($orgChart, JSON_PRETTY_PRINT);
