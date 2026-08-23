const API_BASE = "/server/api.php";

// Auth guard — must be logged in as an agency
const user = JSON.parse(sessionStorage.getItem("user") || "null");
if (!user || user.role !== "agency") {
  window.location.href = "../index.html";
}

let allGroupPackages = [];

document.addEventListener("DOMContentLoaded", async () => {
  await fetchGroupTrips();

  // Wire up search bar and status select filtering tools
  const searchInput = document.querySelector(".search input");
  const statusSelect = document.querySelector(".filter-toolbar select");

  if (searchInput) searchInput.addEventListener("input", filterTrips);
  if (statusSelect) statusSelect.addEventListener("change", filterTrips);
});

async function fetchGroupTrips() {
  const container = document.querySelector(".trips-section");
  if (!container) return;

  try {
    const res = await fetch(`${API_BASE}/api/agency/packages`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ agency_id: user.id })
    });
    const data = await res.json();

    if (!res.ok) {
      container.innerHTML = `<h2 class="section-title-left">Active Group Expeditions</h2>
                             <p class="text-error">${data.error || "Failed to load expeditions."}</p>`;
      return;
    }

    // Isolate group packages strictly based on max allotment rules (MaxParticipants > 1)
    allGroupPackages = (data.packages || []).filter(p => parseInt(p.MaxParticipants) > 1);
    renderTrips(allGroupPackages);

  } catch (err) {
    container.innerHTML = `<h2 class="section-title-left">Active Group Expeditions</h2>
                           <p class="text-error">Could not connect to server.</p>`;
  }
}

function renderTrips(packages) {
  const container = document.querySelector(".trips-section");
  if (!container) return;

  // Re-render heading frame layout cleanly
  container.innerHTML = '<h2 class="section-title-left">Active Group Expeditions</h2>';

  if (packages.length === 0) {
    container.innerHTML += '<p class="muted-padded">No active group expeditions matched your filter criteria.</p>';
    return;
  }

  packages.forEach(pkg => {
    const totalSpots = parseInt(pkg.MaxParticipants || 1);
    const seatsFilled = parseInt(pkg.SeatsFilled || 0);
    const percentFilled = Math.min(Math.round((seatsFilled / totalSpots) * 100), 100);

    // Parse the date display cleanly
    const dateObj = pkg.StartDate ? new Date(pkg.StartDate) : new Date();
    const dateFormatted = dateObj.toLocaleDateString('en-ZA', { month: 'long', year: 'numeric' });

    const card = document.createElement("div");
    card.className = "group-trip-card";
    const nearest10 = Math.min(100, Math.max(0, Math.round(percentFilled / 10) * 10));
    const widthClass = `progress-fill--p${nearest10}`;
    card.innerHTML = `
        <div class="group-info">
          <h3>${pkg.Title} — ${dateFormatted}</h3>
          <span class="group-id">ID: GRP-${pkg.PackageID}</span>
        </div>

        <div class="capacity-tracker">
          <div class="tracker-labels">
            <span>Seats Filled</span>
            <span>${seatsFilled} / ${totalSpots}</span>
          </div>
          <div class="progress-bar-bg">
            <div class="progress-bar-fill ${widthClass}"></div>
          </div>
        </div>

        <a href="managegrouptrip.html?id=${pkg.PackageID}" class="btn-outline">Manage Group</a>
    `;
    container.appendChild(card);
  });
}

function filterTrips() {
  const query = document.querySelector(".search input")?.value.toLowerCase().trim() || "";
  const statusFilter = document.querySelector(".filter-toolbar select")?.value || "";

  const filtered = allGroupPackages.filter(pkg => {
    const matchesId = `grp-${pkg.PackageID}`.includes(query) || pkg.PackageID.toString().includes(query) || pkg.Title.toLowerCase().includes(query);
    const seatsFilled = parseInt(pkg.SeatsFilled || 0);
    const totalSpots = parseInt(pkg.MaxParticipants || 1);

    let matchesStatus = true;
    if (statusFilter === "Fully Booked") {
      matchesStatus = (seatsFilled >= totalSpots);
    } else if (statusFilter === "Upcoming") {
      matchesStatus = (seatsFilled < totalSpots);
    }

    return matchesId && matchesStatus;
  });

  renderTrips(filtered);
}