<?php
session_start();
require_once __DIR__ . '/../config/db_config.php';

if (!isset($_SESSION['citizen_logged_in'])) {
    header("Location: login.php");
    exit;
}

$errors = [];
$success = '';

// Fetch categories
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $lat = $_POST['lat'] ?? null;
    $lng = $_POST['lng'] ?? null;
    $citizen_id = $_SESSION['citizen_id'];

    if (!$category_id || !$description || !$lat || !$lng) {
        $errors[] = "Please enter the description and select a valid location.";
    }

    if (empty($errors)) {
        $request_uuid = "SR-" . date("Ymd") . "-" . rand(1000, 9999);
        $stmt = $pdo->prepare("
            INSERT INTO service_requests 
            (request_uuid, citizen_id, category_id, description, lat, lng, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'NEW', NOW())
        ");
        $stmt->execute([$request_uuid, $citizen_id, $category_id, $description, $lat, $lng]);
        $request_id = $pdo->lastInsertId();

        // Handle photos upload
        if (!empty($_FILES['photos']['name'][0])) {
            $allowed_types = ['image/jpeg','image/png','image/jpg','image/gif'];
            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                if (in_array($_FILES['photos']['type'][$key], $allowed_types)) {
                    $ext = pathinfo($_FILES['photos']['name'][$key], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $ext;
                    $upload_path = __DIR__ . '/../uploads/' . $filename;
                    move_uploaded_file($tmp_name, $upload_path);

                    $pdo->prepare("
                        INSERT INTO service_request_photos
                        (request_id, uploaded_by_type, photo_type, photo_url, created_at)
                        VALUES (?, 'CITIZEN', 'REPORT', ?, NOW())
                    ")->execute([$request_id, $filename]);
                }
            }
        }

        $success = "Report submitted successfully!";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Report an Issue - STMP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<style>
:root {
    --accent:#ff6600;
}
body {
  margin:0;
  font-family:"Poppins",sans-serif;
  background:url('assets/cover2.png') no-repeat center center fixed;
  background-size:cover;
  color:#fff;
}
.overlay {
  position:fixed;top:0;left:0;width:100%;height:100%;
  background:rgba(0,0,0,0.65);
  z-index:0;
}
.top-nav {
  position:fixed;
  top:18px; left:22px;
  z-index:3;
}
.top-nav a {
  color:#fff; font-weight:600; text-decoration:none; padding:8px 12px;
  background:rgba(0,0,0,0.45); border-radius:6px;
}
.top-nav a:hover { background:rgba(255,255,255,0.15); }

.container-wrapper {
  position:relative;
  z-index:1;
  display:flex;
  justify-content:center;
  padding-top:80px;
}
.card-box {
  background:rgba(0,0,0,0.82);
  width:680px;
  border-radius:14px;
  padding:24px 26px;
  box-shadow:0 6px 18px rgba(0,0,0,0.5);
  backdrop-filter:blur(5px);
}
.card-box h2 {
  text-align:center;
  font-size:20px;
  margin-bottom:14px;
  color:var(--accent);
  letter-spacing:0.5px;
}
input, textarea, select {
  width:100%; margin-top:10px; padding:10px;
  border:none; border-bottom:2px solid #666;
  background:transparent; color:#fff; outline:none;
}
input:focus, textarea:focus, select:focus {
  border-bottom-color: var(--accent);
}
.button {
  width:100%;
  margin-top:14px;
  padding:10px;
  background:var(--accent);
  border:none;
  color:white;
  border-radius:8px;
  cursor:pointer;
  font-weight:600;
}
.button:hover { background:#ff8533; }
#map {
  height:220px;
  border-radius:10px;
  margin-top:8px;
  border:2px solid #555;
}
.action-row {
  display:flex; gap:10px; margin-top:10px;
}
.action-row button {
  flex:1;
  padding:8px;
  border:none;
  background:#444;
  color:white;
  border-radius:6px;
  cursor:pointer;
}
.action-row button:hover { background:#666; }
.alert {
  background:rgba(255,255,255,0.15);
  border:1px solid var(--accent);
  font-size:13px;
  padding:8px;
}
</style>
</head>
<body>
<div class="overlay"></div>

<div class="top-nav"><a href="dashboard.php">&larr; Back</a></div>

<div class="container-wrapper">
  <div class="card-box">
    <h2>Submit Service Report</h2>

    <?php if ($errors): ?>
      <div class="alert alert-danger"><?php foreach($errors as $e) echo "<div>$e</div>"; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <select name="category_id" required>
        <option value="">Select Issue Category</option>
        <?php foreach($categories as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <textarea name="description" rows="3" placeholder="Describe the issue..." required></textarea>

      <input type="text" id="searchBox" placeholder="Search address / landmark...">

      <div class="action-row">
        <button type="button" id="searchBtn">Search</button>
        <button type="button" id="locateBtn">Use Current Location</button>
      </div>

      <div id="map"></div>

      <input type="hidden" name="lat" id="lat">
      <input type="hidden" name="lng" id="lng">

      <label style="margin-top:10px;">Upload Photos (max 3)</label>
      <input type="file" name="photos[]" multiple accept="image/*">

      <button class="button" type="submit">Submit Report</button>
      <p style="text-align:center;margin-top:10px;">
        <a href="track_status.php" style="color:var(--accent);">Track Status</a>
      </p>
    </form>
  </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
// Init Map
var map = L.map('map').setView([-26.2041, 28.0473], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
var marker;

// Click to place marker
map.on('click', function(e){
  placeMarker(e.latlng.lat, e.latlng.lng);
});

// Search
document.getElementById('searchBtn').onclick = function(){
  var q = document.getElementById('searchBox').value.trim();
  if(!q) return;
  fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}`)
    .then(res=>res.json())
    .then(data=>{
      if(data.length>0){
        var lat = parseFloat(data[0].lat), lng = parseFloat(data[0].lon);
        map.setView([lat,lng],17);
        placeMarker(lat,lng);
      } else alert("Location not found.");
    });
};

// GPS locate
document.getElementById('locateBtn').onclick = function(){
  navigator.geolocation.getCurrentPosition(pos=>{
    let lat=pos.coords.latitude, lng=pos.coords.longitude;
    map.setView([lat,lng],17);
    placeMarker(lat,lng);
  }, ()=>alert("Unable to get your location."));
};

function placeMarker(lat,lng){
  if(marker) marker.setLatLng([lat,lng]);
  else marker=L.marker([lat,lng]).addTo(map);
  document.getElementById('lat').value=lat;
  document.getElementById('lng').value=lng;
}
</script>
</body>
</html>
