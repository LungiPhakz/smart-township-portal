<?php
session_start();
require_once __DIR__ . '/../config/db_config.php';
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Fetch service request locations
$sql = "SELECT sr.id, sr.request_uuid, sr.status, sr.lat, sr.lng, 
               u.name AS assigned_name
        FROM service_requests sr
        LEFT JOIN staff u ON u.id = sr.assigned_to
       
        WHERE sr.lat IS NOT NULL AND sr.lng IS NOT NULL
        ORDER BY sr.created_at DESC";
$locations = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Service Requests Map - STMP Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<!-- Leaflet & Plugins -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<link rel="stylesheet" href="style.css">

<!-- Custom Sidebar & Theme -->


<style>
  * ===== GLOBAL LAYOUT FIX ===== */
html, body {
    height: 100%;
    overflow: hidden;
}

/* Sidebar */
.sidebar {
    width: 240px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    overflow: hidden;
}

/* Main Content */
.main {
    margin-left: 240px;
    height: 100vh;
    overflow-y: auto;
    padding: 24px;
}


.main h3 { color: #ff6600; margin-bottom: 1rem; margin-left: 250px; }


/* Map container inside card */
.map-card {
    border-radius: 14px;
    padding: 1rem;
    background: rgba(255,255,255,0.08);        /* Glass */
    border: 1px solid rgba(255,255,255,0.12);
    backdrop-filter: blur(6px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.45);
    
}

#map {
  height: 65vh;
  border-radius: 12px;
  margin-top: 1rem;
  box-shadow: inset 0 0 8px rgba(0,0,0,0.05);
}

/* Legend styling */
.legend {
    background: rgba(0,0,0,0.65);
    padding: 12px 16px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.6);
    font-size: 0.9rem;
    color: #fff;
    border: 1px solid rgba(255,255,255,0.15);
}
.legend span {
  display: inline-block;
  width: 14px;
  height: 14px;
  margin-right: 8px;
  border-radius: 50%;
}
/* Leaflet circle markers border fix for dark theme */
.leaflet-interactive {
    stroke: #000 !important;
}

/* Marker clusters dark theme */
.marker-cluster div {
    background: rgba(0,0,0,0.6) !important;
    color: #fff !important;
    border: 1px solid #ff6600 !important;
}

</style>
</head>

<body>
  
<?php include_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="main ">
  <h3><i class="bi bi-geo-alt"></i> Service Requests Map</h3>

  <div class="map-card position-relative">
      <div id="map"></div>

      <!-- Legend -->
      <div class="legend ">
          <div><span style="background:red"></span> New</div>
          <div><span style="background:orange"></span> In Progress</div>
          <div><span style="background:green"></span> Resolved</div>
          <div><span style="background:gray"></span> Other</div>
      </div>
  </div>
</div>

<!-- JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js"></script>

<script>
const map = L.map('map').setView([-26.2, 28.0], 11);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

const markers = L.markerClusterGroup();
const heatPoints = [];
const locations = <?= json_encode($locations) ?>;

locations.forEach(loc => {
  let color;
  switch(loc.status) {
    case 'NEW': color = 'red'; break;
    case 'IN_PROGRESS': color = 'orange'; break;
    case 'RESOLVED': color = 'green'; break;
    default: color = 'gray';
  }

  const marker = L.circleMarker([loc.lat, loc.lng], {
    color, fillColor: color, radius: 8, fillOpacity: 0.85
  });

  const assigned = loc.assigned_name ? loc.assigned_name : '-';
  marker.bindPopup(
    `<b>ID:</b> ${loc.request_uuid}<br>` +
    `<b>Category:</b> ${loc.category}<br>` +
    `<b>Status:</b> ${loc.status}<br>` +
    `<b>Assigned To:</b> ${assigned}`
  );

  markers.addLayer(marker);
  heatPoints.push([loc.lat, loc.lng, 1]);
});

map.addLayer(markers);
L.heatLayer(heatPoints, {
  radius: 25, blur: 15, maxZoom: 17,
  gradient: {0.3:'blue', 0.5:'lime', 0.7:'orange', 1:'red'}
}).addTo(map);
</script>

</body>
</html>
