const API_BASE = "http://localhost/Tripistry/server/api.php";

// -----------------------------------------------------------------------
// Auth guard
// -----------------------------------------------------------------------
const user = JSON.parse(sessionStorage.getItem("user") || "null");
if (!user || user.role !== "agency") {
  window.location.href = "../index.html";
}

// -----------------------------------------------------------------------
// State
// -----------------------------------------------------------------------
let currentPackageId = null;   
let draftPending     = false;  

const publishBtn = document.getElementById("publishPackage");
const linked = { flight: [], accommodation: [], destination: [], restaurant: [] };

// -----------------------------------------------------------------------
// Initialization, Edit Mode & Draft Recovery
// -----------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", async () => {
  const isGroupTrip = document.getElementById("isGroupTrip");
  const container = document.getElementById("groupFieldsContainer");
  const maxParticipants = document.getElementById("maxParticipantsInput");

  function toggleFields() {
    if (isGroupTrip.checked) {
      container.classList.add("show-fields");
      maxParticipants.required = true;
      if (maxParticipants.value === "1") maxParticipants.value = "";
    } else {
      container.classList.remove("show-fields");
      maxParticipants.required = false;
      maxParticipants.value = "1"; 
    }
  }

  isGroupTrip.addEventListener("change", toggleFields);

  // Check URL for explicit Edit mode, OR check SessionStorage for an abandoned draft
  const params = new URLSearchParams(window.location.search);
  const editId = params.get("id") || params.get("PackageID") || params.get("package_id") || params.get("edit");
  const recoveredDraftId = sessionStorage.getItem("draftPackageId");

  if (editId) {
    currentPackageId = editId;
    publishBtn.textContent = "Update package";
    document.querySelector('.concept-current').textContent = "Edit Package";
    await loadPackageDetails(editId, toggleFields);
  } else if (recoveredDraftId) {
    currentPackageId = recoveredDraftId;
    document.querySelector('.concept-current').innerHTML = `<span style="color:#ffb400">Recovered Draft</span>`;
    await loadPackageDetails(recoveredDraftId, toggleFields);
  } else {
    toggleFields(); 
  }
});

async function loadPackageDetails(id, toggleCallback) {
  try {
    const res = await fetch(`${API_BASE}/api/packages/details`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ package_id: parseInt(id) })
    });
    const data = await res.json();
    
    if (data.package) {
      const p = data.package;
      
      // Strip out default draft text so it doesn't overwrite the user's intended input
      document.getElementById("titleInput").value = p.Title === "Draft Package" ? "" : (p.Title || "");
      document.getElementById("descriptionInput").value = p.Description === "Draft Description" ? "" : (p.Description || "");
      
      document.getElementById("startDateInput").value = p.StartDate ? p.StartDate.substring(0, 10) : "";
      document.getElementById("endDateInput").value = p.EndDate ? p.EndDate.substring(0, 10) : "";
      document.getElementById("priceInput").value = p.TotalPrice || "";
      
      const isGroupTrip = document.getElementById("isGroupTrip");
      const maxParticipantsInput = document.getElementById("maxParticipantsInput");
      
      if (p.MaxParticipants > 1) {
        isGroupTrip.checked = true;
        if (toggleCallback) toggleCallback(); 
        maxParticipantsInput.value = p.MaxParticipants;
      } else {
        isGroupTrip.checked = false;
        if (toggleCallback) toggleCallback();
        maxParticipantsInput.value = "1";
      }

      if (p.Images && p.Images.length > 0) {
        document.getElementById("imageURLInput").value = p.Images[0];
      }

      linked.flight = (p.Flights || []).map(f => ({ id: f.FlightID, label: `${f.Airline} · ${f.FlightNumber}` }));
      linked.destination = (p.Destinations || []).map(d => ({ id: d.DestinationID, label: d.City }));
      linked.accommodation = (p.Accommodations || []).map(a => ({ id: a.AccommodationID, label: a.Name }));
      linked.restaurant = (p.Restaurants || []).map(r => ({ id: r.RestaurantID, label: r.Name }));

      ['flight', 'destination', 'accommodation', 'restaurant'].forEach(updateResourceButton);
    }
  } catch (err) {
    showError("Could not load existing package details.");
  }
}

// -----------------------------------------------------------------------
// Inject modal HTML
// -----------------------------------------------------------------------
const modalHTML = `
<dialog id="resource-modal" class="resource-modal">
    <div class="modal-header">
        <h2><i class="bi bi-link-45deg"></i> <span id="modal-title-text">Link Resource</span></h2>
        <button class="close-btn" id="modal-close">×</button>
    </div>
    <hr class="modal-divider" />
    <div class="modal-body-column">
        <div class="search-bar">
            <input type="text" id="modal-search" placeholder="Search..." />
            <i class="bi bi-search"></i>
        </div>
        <div class="results-list" id="modal-results">
            <p class="sub-line">Type to search or leave blank to browse all.</p>
        </div>
        <div id="modal-linked-title">Already Linked</div>
        <div id="modal-linked"></div>
    </div>
</dialog>`;
document.body.insertAdjacentHTML("beforeend", modalHTML);

const modal = document.getElementById("resource-modal");
const modalSearch = document.getElementById("modal-search");
const modalResults = document.getElementById("modal-results");
const modalLinked = document.getElementById("modal-linked");
const modalLinkedTitle = document.getElementById("modal-linked-title");

document.getElementById("modal-close").addEventListener("click", closeModal);
modal.addEventListener("click", (e) => { if (e.target === modal) closeModal(); });

// -----------------------------------------------------------------------
// Ensure a draft package exists before opening any modal
// -----------------------------------------------------------------------
async function ensurePackageExists() {
    if (currentPackageId) return true;
    if (draftPending) return false;
    
    draftPending = true;
    try {
        const res = await fetch(`${API_BASE}/api/agency/packages/draft`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ agency_id: user.id })
        });
        const data = await res.json();
        if (!res.ok) { showError(data.error || 'Could not create draft package.'); return false; }
        
        currentPackageId = data.package_id;
        
        // Save to session storage so if they refresh, the script recovers it!
        sessionStorage.setItem("draftPackageId", currentPackageId);
        
        document.querySelector('.concept-current').innerHTML = `<span style="color:#ffb400">Draft Package</span>`;
        return true;
    } catch (err) {
        showError('Could not connect to server.');
        return false;
    } finally {
        draftPending = false;
    }
}

// -----------------------------------------------------------------------
// Resource modal helpers
// -----------------------------------------------------------------------
let activeType = null;
let searchTimer = null;

async function openModal(type) {
  const hasPackage = await ensurePackageExists();
  if (!hasPackage) return; 

  activeType = type;
  const labels = {
    flight: "Add Flight",
    accommodation: "Add Accommodation",
    destination: "Add Destination",
    restaurant: "Add Restaurant",
  };
  
  if(document.getElementById("modal-title-text")) {
      document.getElementById("modal-title-text").textContent = labels[type];
  }
  
  modalSearch.value = "";
  modalResults.innerHTML = '<p style="color:rgba(255,255,255,0.4);font-size:0.85rem">Type to search or leave blank to browse all.</p>';
  renderLinkedChips();
  modal.showModal();
  setTimeout(() => modalSearch.focus(), 50);
}

function closeModal() { modal.close(); }

function renderLinkedChips() {
  const items = linked[activeType];
  if (items.length === 0) {
    modalLinkedTitle.style.display = "none";
    modalLinked.innerHTML = "";
    return;
  }
  modalLinkedTitle.style.display = "block";
  modalLinked.innerHTML = items
    .map(
      (item) => `
        <span style="display:inline-flex; align-items:center; gap:0.3rem; background:rgba(255,180,0,0.15); border:1px solid rgba(255,180,0,0.3); color:#ffb400; padding:0.2rem 0.6rem; border-radius:999px; font-size:0.75rem">
            ${item.label}
            <button data-id="${item.id}" class="unlink-chip" style="background:none; border:none; color:#ffb400; cursor:pointer; font-size:0.9rem; padding:0; line-height:1">&times;</button>
        </span>`
    ).join("");

  modalLinked.querySelectorAll(".unlink-chip").forEach((btn) => {
    btn.addEventListener("click", () => unlinkResource(parseInt(btn.dataset.id)));
  });
}

// -----------------------------------------------------------------------
// Resource search
// -----------------------------------------------------------------------
modalSearch.addEventListener("input", () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => searchResources(modalSearch.value.trim()), 300);
});

async function searchResources(query) {
  modalResults.innerHTML = '<p style="color:rgba(255,255,255,0.4);font-size:0.85rem">Searching…</p>';
  try {
    const res = await fetch(`${API_BASE}/api/resources/search`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ type: activeType, query }),
    });
    const data = await res.json();

    if (!res.ok || !data.results) {
      modalResults.innerHTML = `<p style="color:#e05555;font-size:0.85rem">${data.error || "Search failed."}</p>`;
      return;
    }
    if (data.results.length === 0) {
      modalResults.innerHTML = '<p style="color:rgba(255,255,255,0.4);font-size:0.85rem">No results found.</p>';
      return;
    }

    modalResults.innerHTML = "";
    data.results.forEach((item) => {
      const alreadyLinked = linked[activeType].some((l) => l.id === item.id);
      const row = document.createElement("div");
      row.style.cssText = `display:flex; justify-content:space-between; align-items:center; padding:0.6rem 0.8rem; border-radius:8px; cursor:pointer; border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.04); transition:background 0.15s`;
      row.innerHTML = `
                <div style="color:white; font-size:0.85rem">
                    <div style="font-weight:600">${buildLabel(item)}</div>
                    <div style="color:rgba(255,255,255,0.45); font-size:0.75rem">${buildSublabel(item)}</div>
                </div>
                <button style="padding:0.3rem 0.8rem; border-radius:6px; font-size:0.8rem; cursor:pointer; border:1px solid ${alreadyLinked ? "rgba(255,255,255,0.2)" : "rgba(255,180,0,0.5)"}; background:${alreadyLinked ? "rgba(255,255,255,0.05)" : "rgba(255,180,0,0.15)"}; color:${alreadyLinked ? "rgba(255,255,255,0.3)" : "#ffb400"}">
                    ${alreadyLinked ? "Linked" : "+ Add"}
                </button>`;
      if (!alreadyLinked) row.querySelector("button").addEventListener("click", () => linkResource(item));
      modalResults.appendChild(row);
    });
  } catch (err) {
    modalResults.innerHTML = '<p style="color:#e05555;font-size:0.85rem">Could not connect to server.</p>';
  }
}

function buildLabel(item) {
  if (activeType === "flight") return `${item.Airline} · ${item.FlightNumber}`;
  return item.Name || item.name || "";
}
function buildSublabel(item) {
  if (activeType === "flight") return `${item.OriginCode} → ${item.DestCode} · ${item.OriginCity} → ${item.DestCity}`;
  if (activeType === "accommodation") return `${item.Type || ""} · ${item.City}, ${item.Country} · R${item.AveragePricePerNight}/night`;
  if (activeType === "destination") return `${item.City}, ${item.Country}`;
  if (activeType === "restaurant") return `${item.Cuisine || ""} · ${item.City}, ${item.Country}`;
  return "";
}

// -----------------------------------------------------------------------
// Link / unlink
// -----------------------------------------------------------------------
async function linkResource(item) {
  try {
    const res = await fetch(`${API_BASE}/api/resources/link`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ agency_id: user.id, package_id: currentPackageId, type: activeType, resource_id: item.id }),
    });
    const data = await res.json();
    if (!res.ok) { alert(data.error || "Failed to link."); return; }

    linked[activeType].push({ id: item.id, label: buildLabel(item) });
    renderLinkedChips();
    updateResourceButton(activeType);
    searchResources(modalSearch.value.trim());
  } catch (err) { alert("Could not connect to server."); }
}

async function unlinkResource(resourceId) {
  try {
    const res = await fetch(`${API_BASE}/api/resources/unlink`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ agency_id: user.id, package_id: currentPackageId, type: activeType, resource_id: resourceId }),
    });
    const data = await res.json();
    if (!res.ok) { alert(data.error || "Failed to unlink."); return; }

    linked[activeType] = linked[activeType].filter((l) => l.id !== resourceId);
    renderLinkedChips();
    updateResourceButton(activeType);
    searchResources(modalSearch.value.trim());
  } catch (err) { alert("Could not connect to server."); }
}

// -----------------------------------------------------------------------
// Update resource button counts
// -----------------------------------------------------------------------
function updateResourceButton(type) {
  const idMap = { flight: "addFlight", accommodation: "addAccomodation", destination: "addDestination", restaurant: "addRestuarant" };
  const btn = document.getElementById(idMap[type]);
  const count = linked[type].length;
  const p = btn.querySelector("p");
  const labels = { flight: "Flight", accommodation: "Accommodation", destination: "Destination", restaurant: "Restaurant" };
  p.textContent = count > 0 ? `${labels[type]} (${count})` : `Add ${labels[type]}`;
  btn.style.borderColor = count > 0 ? "rgba(255,180,0,0.5)" : "";
  btn.style.color = count > 0 ? "#ffb400" : "";
}

// -----------------------------------------------------------------------
// Open generic search modals
// -----------------------------------------------------------------------
document.getElementById("addFlight").addEventListener("click", () => openModal("flight"));
document.getElementById("addDestination").addEventListener("click", () => openModal("destination"));
document.getElementById("addAccomodation").addEventListener("click", () => openModal("accommodation"));
document.getElementById("addRestuarant").addEventListener("click", () => openModal("restaurant"));

// -----------------------------------------------------------------------
// Error / success display
// -----------------------------------------------------------------------
function showError(message, success = false) {
  let el = document.getElementById("form-error");
  if (!el) {
    el = document.createElement("div");
    el.id = "form-error";
    publishBtn.parentNode.insertBefore(el, publishBtn);
  }
  el.className = "error-container error-visible";
  el.style.background = success ? "rgba(0,180,100,0.15)" : "";
  el.style.borderColor = success ? "rgba(0,200,100,0.4)" : "";
  el.innerHTML = `<i class="bi bi-${success ? "check-circle" : "exclamation-circle"}-fill"></i> <span>${message}</span>`;
  el.scrollIntoView({ behavior: "smooth", block: "center" });
}

// -----------------------------------------------------------------------
// Publish / Update
// -----------------------------------------------------------------------
publishBtn.addEventListener("click", async () => {
  const title = document.getElementById("titleInput").value.trim();
  const description = document.getElementById("descriptionInput").value.trim();
  const startDate = document.getElementById("startDateInput").value;
  const endDate = document.getElementById("endDateInput").value;
  const maxParticipants = document.getElementById("maxParticipantsInput").value;
  const totalPrice = document.getElementById("priceInput").value;

  if (!title) { showError("Package title is required."); return; }
  if (!description) { showError("Package description is required."); return; }
  if (!startDate) { showError("Start date is required."); return; }
  if (!endDate) { showError("End date is required."); return; }
  if (endDate <= startDate) { showError("End date must be after start date."); return; }
  if (!maxParticipants || maxParticipants < 1) { showError("Max participants must be at least 1."); return; }
  if (!totalPrice || totalPrice <= 0) { showError("Total price must be greater than 0."); return; }

  publishBtn.disabled = true;
  publishBtn.textContent = "Saving…";

  const endpoint = currentPackageId 
      ? `${API_BASE}/api/agency/packages/update` 
      : `${API_BASE}/api/agency/packages/create`;

  const payload = {
    agency_id: user.id,
    title,
    description,
    start_date: startDate,
    end_date: endDate,
    max_participants: parseInt(maxParticipants),
    total_price: parseFloat(totalPrice),
  };

  if (currentPackageId) payload.package_id = parseInt(currentPackageId);

  try {
    const res = await fetch(endpoint, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const data = await res.json();

    if (!res.ok) {
      showError(data.error || "Failed to save package.");
      return;
    }

    if (!currentPackageId) currentPackageId = data.package_id;

    // The package is saved! Clear the draft memory so they can start a fresh one later.
    sessionStorage.removeItem("draftPackageId");

    showError(
      `Package "<strong>${title}</strong>" saved successfully! <a href="grouptrips.html">View active trips</a>.`,
      true
    );

    publishBtn.textContent = "Update package";
    document.querySelector('.concept-current').textContent = "Edit Package";
  } catch (err) {
    showError("Could not connect to the server.");
  } finally {
    publishBtn.disabled = false;
    if (publishBtn.textContent === "Saving…") {
        publishBtn.textContent = currentPackageId ? "Update package" : "Publish package";
    }
  }
});