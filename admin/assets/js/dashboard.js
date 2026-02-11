// dashboard.js
document.addEventListener("DOMContentLoaded", async () => {

  /* ========== 1️⃣ Initialize Leaflet Map ========== */
  const map = L.map('map').setView([-26.2041, 28.0473], 11); // Johannesburg center (example)
  
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  // Optional example markers
  const sampleMarkers = [
    { lat: -26.1952, lng: 28.0340, label: "Water Leak - Zone A" },
    { lat: -26.2251, lng: 28.0721, label: "Streetlight - Zone C" },
    { lat: -26.2408, lng: 27.9900, label: "Road Damage - Zone B" }
  ];
  sampleMarkers.forEach(m => L.marker([m.lat, m.lng]).addTo(map).bindPopup(m.label));

  /* ========== 2️⃣ Fetch Recent Requests ========== */
  try {
    const response = await fetch('ajax_recent_requests.php');
    const data = await response.json();
    renderRecentTable(data);
  } catch (err) {
    document.querySelector('#recent-table').innerHTML = 
      `<div class="text-danger text-center py-4">Error loading requests</div>`;
  }
});


/* ========== 3️⃣ Render Recent Requests Table ========== */
function renderRecentTable(requests) {
  if (!requests.length) {
    document.querySelector('#recent-table').innerHTML = 
      `<div class="text-muted text-center py-4">No recent requests found</div>`;
    return;
  }

  let rows = `
    <table class="table table-sm align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>#</th><th>Citizen</th><th>Category</th>
          <th>Area</th><th>Status</th><th>Date</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
  `;

  requests.forEach(r => {
    const badgeClass = r.status === 'NEW' ? 'st-new' :
                       r.status === 'IN_PROGRESS' ? 'st-in' : 'st-res';
    const statusText = r.status.replace('_', ' ');
    rows += `
      <tr>
        <td>${r.id}</td>
        <td>${r.citizen}</td>
        <td>${r.category}</td>
        <td>${r.area}</td>
        <td><span class="status-badge ${badgeClass}">${statusText}</span></td>
        <td>${r.date}</td>
        <td><button class="btn btn-sm btn-primary">View</button></td>
      </tr>
    `;
  });

  rows += '</tbody></table>';
  document.querySelector('#recent-table').innerHTML = rows;
}
