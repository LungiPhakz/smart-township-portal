<?php
// get_requests.php
require_once __DIR__ . '/../db_config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'map';

if ($action === 'map') {
    // Return GeoJSON features for leaflet
    $sql = "SELECT id, request_uuid, category, status, lat, lng, description, created_at
            FROM service_requests
            WHERE lat IS NOT NULL AND lng IS NOT NULL
            AND status IN ('NEW','ASSIGNED','IN_PROGRESS')";
    $stmt = $pdo->query($sql);
    $features = [];
    while ($row = $stmt->fetch()) {
        $features[] = [
            'type' => 'Feature',
            'properties' => [
                'id' => (int)$row['id'],
                'request_uuid' => $row['request_uuid'],
                'category' => $row['category'],
                'status' => $row['status'],
                'description' => $row['description'],
                'created_at' => $row['created_at'],
            ],
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [ (float)$row['lng'], (float)$row['lat'] ]
            ]
        ];
    }

    echo json_encode([
        'type' => 'FeatureCollection',
        'features' => $features
    ]);
    exit;
}

// default: recent table rows
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$sql = "SELECT sr.request_uuid, sr.category, sr.status, sr.created_at, u.name AS citizen_name, d.name AS department_name
        FROM service_requests sr
        LEFT JOIN users u ON u.id = sr.citizen_id
        LEFT JOIN departments d ON d.id = sr.department_id
        ORDER BY sr.created_at DESC
        LIMIT :limit";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

echo json_encode($rows);
