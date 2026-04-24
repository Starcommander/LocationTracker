<?php
header('Content-Type: application/json');

require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $name = trim($input['name'] ?? '');
    $lat = floatval($input['lat'] ?? 0);
    $lon = floatval($input['lon'] ?? 0);

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Name is required']);
        exit;
    }

    if ($lat < -90 || $lat > 90) {
        echo json_encode(['success' => false, 'message' => 'Latitude must be between -90 and 90']);
        exit;
    }

    if ($lon < -180 || $lon > 180) {
        echo json_encode(['success' => false, 'message' => 'Longitude must be between -180 and 180']);
        exit;
    }

    $locations = getLocations();
    $locations[$name] = [
        'lat' => $lat,
        'lon' => $lon,
        'updated' => date('c')
    ];
    saveLocations($locations);

    echo json_encode(['success' => true, 'message' => 'Location saved']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);