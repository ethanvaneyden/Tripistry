const API_BASE = "http://localhost/Tripistry/server/api.php";

const user = JSON.parse(sessionStorage.getItem("user") || "null");
if (!user || user.role !== "agency") {
  window.location.href = "../index.html";
}

const params = new URLSearchParams(window.location.search);
const packageId = params.get("id");

if (!packageId) {
  window.location.href = "grouptrips.html";
}

let packageData = null;

document.addEventListener("DOMContentLoaded", async () => {
  await loadGroupManifest();
  document.querySelector(".btn-save-changes").addEventListener("click", saveCapacityChanges);
});

async function loadGroupManifest() {
  try {
    const res = await fetch(`${API_BASE}/api/packages/details`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ package_id: parseInt(packageId) })
    });
    const data = await res.json();

    if (!res.ok || !data.package) {
      showToast("Error parsing group trip specifications.", 'error');
      return;
    }

    packageData = data.package;
    populateUI();

  } catch (err) {
    showToast("Could not load passenger manifest metrics.", 'error');
  }
}

function populateUI() {
  const p = packageData;
  const bookingsList = p.BookingsList || []; 
  const totalBookedSeats = bookingsList.reduce((sum, bk) => sum + parseInt(bk.SeatsClaimed || 0), 0);

 
  document.querySelector(".status-value").textContent = `GRP-${p.PackageID}`;
  
  const packageTitleSpan = document.querySelector(".info-row:nth-child(2) .status-value");
  if (packageTitleSpan) packageTitleSpan.textContent = `PKG-${p.PackageID} — ${p.Title}`;
  
  const datesSpan = document.querySelector(".info-row:nth-child(4) .status-value");
  if (datesSpan && p.StartDate && p.EndDate) {
    const start = new Date(p.StartDate).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
    const end = new Date(p.EndDate).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    datesSpan.textContent = `${start} – ${end}`;
  }

 
  const seatsCounterSpan = document.getElementById("live-seats-counter");
  if (seatsCounterSpan) {
    seatsCounterSpan.textContent = `${totalBookedSeats} / ${p.MaxParticipants}`;
  }

  // Sync capacity entry inputs
  document.getElementById("maxParticipants").value = p.MaxParticipants;

  // Render Active Passenger Manifest Table Grid
  const tableBody = document.querySelector(".manifest-table tbody");
  tableBody.innerHTML = "";

  if (bookingsList.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="4" class="text-center-muted">No active manifest passenger entries recorded yet.</td></tr>`;
    return;
  }

  bookingsList.forEach((bk) => {
    const row = document.createElement("tr");
    const statusClass = bk.FinancialStatus.toLowerCase() === 'confirmed' || bk.FinancialStatus.toLowerCase() === 'approved' 
      ? 'badge-paid' 
      : 'badge-deposit';

    row.innerHTML = `
      <td><strong>BKG-${bk.BookingID}</strong></td>
      <td>${bk.PassengerName}</td>
      <td>${bk.SeatsClaimed} ${parseInt(bk.SeatsClaimed) === 1 ? 'Seat' : 'Seats'}</td>
      <td><span class="badge ${statusClass}">${bk.FinancialStatus}</span></td>
    `;
    tableBody.appendChild(row);
  });
}

async function saveCapacityChanges() {
  const maxParticipantsInput = document.getElementById("maxParticipants").value;
  const saveBtn = document.querySelector(".btn-save-changes");

  if (!maxParticipantsInput || parseInt(maxParticipantsInput) < 1) {
    showToast("Capacity limits must reflect at least 1 seat allotment entry.", 'info');
    return;
  }

  saveBtn.disabled = true;
  saveBtn.textContent = "Saving...";

  try {
    const res = await fetch(`${API_BASE}/api/agency/packages/update`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        package_id: parseInt(packageId),
        agency_id: user.id,
        title: packageData.Title,
        description: packageData.Description,
        start_date: packageData.StartDate.substring(0, 10),
        end_date: packageData.EndDate.substring(0, 10),
        total_price: parseFloat(packageData.TotalPrice),
        max_participants: parseInt(maxParticipantsInput)
      })
    });

    if (!res.ok) {
      const errData = await res.json();
      showToast("Failed to adjust specifications.", 'error');
      saveBtn.disabled = false;
      saveBtn.innerHTML = '<i class="bi bi-check-circle"></i> Save Changes';
      return;
    }

   
    window.location.href = "grouptrips.html";

  } catch (err) {
    showToast("Could not commit updates to database context.", 'error');
    saveBtn.disabled = false;
    saveBtn.innerHTML = '<i class="bi bi-check-circle"></i> Save Changes';
  }
}

function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    let iconClass = 'bi-info-circle';
    if (type === 'success') iconClass = 'bi-check-circle';
    if (type === 'error') iconClass = 'bi bi-exclamation-circle';

    const toast = document.createElement('div');
    toast.className = `custom-toast ${type}`;
    toast.innerHTML = `
        <i class="bi ${iconClass}" style="font-size: 1.2rem;"></i>
        <div class="toast-message" style="flex-grow: 1;">${message}</div>
    `;

    container.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 50);

    setTimeout(() => {
        toast.classList.remove('show');
        toast.addEventListener('transitionend', () => toast.remove());
    }, 4000);
}