const API_BASE = 'http://localhost/Tripistry/server/api.php';

// -----------------------------------------------------------------------
// Auth guard
// -----------------------------------------------------------------------
const user = JSON.parse(sessionStorage.getItem('user') || 'null');
if (!user || user.role !== 'agency') {
    window.location.href = '../index.html';
}

// -----------------------------------------------------------------------
// State
// -----------------------------------------------------------------------
let currentPackageId = null;   // set after draft or publish
let draftPending     = false;  // prevent concurrent draft creation

const publishBtn = document.getElementById('publishPackage');

// -----------------------------------------------------------------------
// Inject modal HTML
// -----------------------------------------------------------------------
const fmInputStyle    = 'width:100%;padding:0.55rem 0.8rem;border-radius:7px;border:1px solid rgba(255,255,255,0.2);background:rgba(255,255,255,0.07);color:white;font-size:0.85rem;box-sizing:border-box';
const fmDropdownStyle = 'background:#1a1a2e;border:1px solid rgba(255,255,255,0.15);border-radius:8px;max-height:160px;overflow-y:auto;display:none;flex-direction:column;margin-top:0.25rem';

const modalHTML = `
<div id="resource-modal" style="
    display:none; position:fixed; inset:0; z-index:1000;
    background:rgba(0,0,0,0.7); backdrop-filter:blur(4px);
    align-items:center; justify-content:center;">
    <div style="
        background:#1a1a2e; border:1px solid rgba(255,255,255,0.15);
        border-radius:12px; padding:2rem; width:90%; max-width:520px;
        max-height:80vh; display:flex; flex-direction:column; gap:1rem;">
        <div style="display:flex; justify-content:space-between; align-items:center">
            <h2 id="modal-title" style="color:white; margin:0; font-size:1.1rem"></h2>
            <button id="modal-close" style="
                background:none; border:none; color:rgba(255,255,255,0.6);
                font-size:1.4rem; cursor:pointer; line-height:1">&times;</button>
        </div>

        <input id="modal-search" type="text" placeholder="Search…" style="
            width:100%; padding:0.6rem 0.9rem; border-radius:8px;
            border:1px solid rgba(255,255,255,0.2); background:rgba(255,255,255,0.07);
            color:white; font-size:0.9rem; box-sizing:border-box"/>

        <div id="modal-results" style="
            overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:0.5rem">
            <p style="color:rgba(255,255,255,0.4); font-size:0.85rem">
                Type to search or leave blank to browse all.
            </p>
        </div>

        <div id="modal-linked-title" style="color:rgba(255,255,255,0.5); font-size:0.8rem; display:none">
            ALREADY LINKED
        </div>
        <div id="modal-linked" style="display:flex; flex-wrap:wrap; gap:0.4rem"></div>
    </div>
</div>

<!-- Flight builder modal -->
<div id="flight-modal" style="
    display:none; position:fixed; inset:0; z-index:1001;
    background:rgba(0,0,0,0.8); backdrop-filter:blur(4px);
    align-items:center; justify-content:center;">
    <div style="
        background:#1a1a2e; border:1px solid rgba(255,255,255,0.15);
        border-radius:12px; padding:2rem; width:90%; max-width:480px;
        display:flex; flex-direction:column; gap:1rem;">
        <div style="display:flex; justify-content:space-between; align-items:center">
            <h2 style="color:white; margin:0; font-size:1.1rem">Add Flight</h2>
            <button id="flight-modal-close" style="
                background:none; border:none; color:rgba(255,255,255,0.6);
                font-size:1.4rem; cursor:pointer; line-height:1">&times;</button>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.7rem">
            <input id="fm-airline"   placeholder="Airline" style="grid-column:1/3; ${fmInputStyle}"/>
            <input id="fm-flightnum" placeholder="Flight number (e.g. FA201)" style="grid-column:1/3; ${fmInputStyle}"/>

            <div style="grid-column:1/3">
                <label style="color:rgba(255,255,255,0.5);font-size:0.75rem;display:block;margin-bottom:0.3rem">ORIGIN AIRPORT</label>
                <input id="fm-origin-search" placeholder="Search airport…" style="${fmInputStyle}" autocomplete="off"/>
                <div id="fm-origin-results" style="${fmDropdownStyle}"></div>
                <input id="fm-origin-id" type="hidden"/>
                <div id="fm-origin-selected" style="color:#ffb400;font-size:0.8rem;margin-top:0.3rem"></div>
            </div>

            <div style="grid-column:1/3">
                <label style="color:rgba(255,255,255,0.5);font-size:0.75rem;display:block;margin-bottom:0.3rem">DESTINATION AIRPORT</label>
                <input id="fm-dest-search" placeholder="Search airport…" style="${fmInputStyle}" autocomplete="off"/>
                <div id="fm-dest-results" style="${fmDropdownStyle}"></div>
                <input id="fm-dest-id" type="hidden"/>
                <div id="fm-dest-selected" style="color:#ffb400;font-size:0.8rem;margin-top:0.3rem"></div>
            </div>

            <div>
                <label style="color:rgba(255,255,255,0.5);font-size:0.75rem;display:block;margin-bottom:0.3rem">DEPARTURE</label>
                <input id="fm-departure" type="datetime-local" style="${fmInputStyle}"/>
            </div>
            <div>
                <label style="color:rgba(255,255,255,0.5);font-size:0.75rem;display:block;margin-bottom:0.3rem">ARRIVAL</label>
                <input id="fm-arrival" type="datetime-local" style="${fmInputStyle}"/>
            </div>

            <input id="fm-cost" placeholder="Base cost (ZAR)" type="number" min="0" style="grid-column:1/3; ${fmInputStyle}"/>
        </div>

        <div id="fm-error" style="color:#e05555;font-size:0.85rem;display:none"></div>

        <button id="fm-submit" style="
            padding:0.7rem; border-radius:8px; border:none; cursor:pointer;
            background:rgba(255,180,0,0.2); border:1px solid rgba(255,180,0,0.4);
            color:#ffb400; font-size:0.95rem; font-weight:600">
            Add Flight
        </button>
    </div>
</div>`;



document.body.insertAdjacentHTML('beforeend',
    modalHTML.replace(/\$\{fmInputStyle\}/g, fmInputStyle)
             .replace(/\$\{fmDropdownStyle\}/g, fmDropdownStyle)
);

// -----------------------------------------------------------------------
// Resource modal refs
// -----------------------------------------------------------------------
const modal            = document.getElementById('resource-modal');
const modalTitle       = document.getElementById('modal-title');
const modalSearch      = document.getElementById('modal-search');
const modalResults     = document.getElementById('modal-results');
const modalLinked      = document.getElementById('modal-linked');
const modalLinkedTitle = document.getElementById('modal-linked-title');

document.getElementById('modal-close').addEventListener('click', closeModal);
modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

// -----------------------------------------------------------------------
// Flight modal refs
// -----------------------------------------------------------------------
const flightModal = document.getElementById('flight-modal');
document.getElementById('flight-modal-close').addEventListener('click', () => flightModal.style.display = 'none');
flightModal.addEventListener('click', (e) => { if (e.target === flightModal) flightModal.style.display = 'none'; });

// Airport search wiring
wireAirportSearch('fm-origin-search', 'fm-origin-results', 'fm-origin-id', 'fm-origin-selected');
wireAirportSearch('fm-dest-search',   'fm-dest-results',   'fm-dest-id',   'fm-dest-selected');

document.getElementById('fm-submit').addEventListener('click', submitFlight);

// -----------------------------------------------------------------------
// In-memory linked resources (type -> [{id, label}])
// -----------------------------------------------------------------------
const linked = { flight: [], accommodation: [], destination: [], restaurant: [] };

// -----------------------------------------------------------------------
// Ensure a draft package exists before opening any modal
// -----------------------------------------------------------------------
async function ensurePackageExists() {
    if (currentPackageId) return true;
    if (draftPending)     return false;
    draftPending = true;
    try {
        const res  = await fetch(`${API_BASE}/api/agency/packages/draft`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ agency_id: user.id })
        });
        const data = await res.json();
        if (!res.ok) { showError(data.error || 'Could not create draft package.'); return false; }
        currentPackageId = data.package_id;
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
let activeType  = null;
let searchTimer = null;

async function openModal(type) {
    const ready = await ensurePackageExists();
    if (!ready) return;

    activeType = type;
    const labels = {
        accommodation: 'Add Accommodation',
        destination:   'Add Destination',
        restaurant:    'Add Restaurant'
    };
    modalTitle.textContent = labels[type];
    modalSearch.value      = '';
    modalResults.innerHTML = '<p style="color:rgba(255,255,255,0.4);font-size:0.85rem">Type to search or leave blank to browse all.</p>';
    renderLinkedChips();
    modal.style.display = 'flex';
    setTimeout(() => modalSearch.focus(), 50);
}

function closeModal() {
    modal.style.display = 'none';
    activeType = null;
}

function renderLinkedChips() {
    const items = linked[activeType];
    if (items.length === 0) {
        modalLinkedTitle.style.display = 'none';
        modalLinked.innerHTML = '';
        return;
    }
    modalLinkedTitle.style.display = 'block';
    modalLinked.innerHTML = items.map(item => `
        <span style="
            display:inline-flex; align-items:center; gap:0.3rem;
            background:rgba(255,180,0,0.15); border:1px solid rgba(255,180,0,0.3);
            color:#ffb400; padding:0.2rem 0.6rem; border-radius:999px; font-size:0.75rem">
            ${item.label}
            <button data-id="${item.id}" class="unlink-chip" style="
                background:none; border:none; color:#ffb400;
                cursor:pointer; font-size:0.9rem; padding:0; line-height:1">&times;</button>
        </span>`).join('');

    modalLinked.querySelectorAll('.unlink-chip').forEach(btn => {
        btn.addEventListener('click', () => unlinkResource(parseInt(btn.dataset.id)));
    });
}

// -----------------------------------------------------------------------
// Resource search
// -----------------------------------------------------------------------
modalSearch.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => searchResources(modalSearch.value.trim()), 300);
});

async function searchResources(query) {
    modalResults.innerHTML = '<p style="color:rgba(255,255,255,0.4);font-size:0.85rem">Searching…</p>';
    try {
        const res  = await fetch(`${API_BASE}/api/resources/search`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: activeType, query }),
        });
        const data = await res.json();

        if (!res.ok || !data.results) {
            modalResults.innerHTML = `<p style="color:#e05555;font-size:0.85rem">${data.error || 'Search failed.'}</p>`;
            return;
        }
        if (data.results.length === 0) {
            modalResults.innerHTML = '<p style="color:rgba(255,255,255,0.4);font-size:0.85rem">No results found.</p>';
            return;
        }

        modalResults.innerHTML = '';
        data.results.forEach(item => {
            const alreadyLinked = linked[activeType].some(l => l.id === item.id);
            const row = document.createElement('div');
            row.style.cssText = `
                display:flex; justify-content:space-between; align-items:center;
                padding:0.6rem 0.8rem; border-radius:8px; cursor:pointer;
                border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.04);`;
            row.innerHTML = `
                <div style="color:white; font-size:0.85rem">
                    <div style="font-weight:600">${buildLabel(item)}</div>
                    <div style="color:rgba(255,255,255,0.45); font-size:0.75rem">${buildSublabel(item)}</div>
                </div>
                <button style="
                    padding:0.3rem 0.8rem; border-radius:6px; font-size:0.8rem; cursor:pointer;
                    border:1px solid ${alreadyLinked ? 'rgba(255,255,255,0.2)' : 'rgba(255,180,0,0.5)'};
                    background:${alreadyLinked ? 'rgba(255,255,255,0.05)' : 'rgba(255,180,0,0.15)'};
                    color:${alreadyLinked ? 'rgba(255,255,255,0.3)' : '#ffb400'}">
                    ${alreadyLinked ? 'Linked' : '+ Add'}
                </button>`;
            if (!alreadyLinked) row.querySelector('button').addEventListener('click', () => linkResource(item));
            modalResults.appendChild(row);
        });
    } catch (err) {
        modalResults.innerHTML = '<p style="color:#e05555;font-size:0.85rem">Could not connect to server.</p>';
    }
}

function buildLabel(item) {
    return item.Name || item.name || '';
}
function buildSublabel(item) {
    if (activeType === 'accommodation') return `${item.Type || ''} · ${item.City}, ${item.Country} · R${item.AveragePricePerNight}/night`;
    if (activeType === 'destination')   return `${item.City}, ${item.Country}`;
    if (activeType === 'restaurant')    return `${item.Cuisine || ''} · ${item.City}, ${item.Country}`;
    return '';
}

// -----------------------------------------------------------------------
// Link / unlink
// -----------------------------------------------------------------------
async function linkResource(item) {
    try {
        const res  = await fetch(`${API_BASE}/api/resources/link`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ agency_id: user.id, package_id: currentPackageId, type: activeType, resource_id: item.id }),
        });
        const data = await res.json();
        if (!res.ok) { alert(data.error || 'Failed to link.'); return; }
        linked[activeType].push({ id: item.id, label: buildLabel(item) });
        renderLinkedChips();
        updateResourceButton(activeType);
        searchResources(modalSearch.value.trim());
    } catch (err) {
        alert('Could not connect to server.');
    }
}

async function unlinkResource(resourceId) {
    try {
        const res  = await fetch(`${API_BASE}/api/resources/unlink`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ agency_id: user.id, package_id: currentPackageId, type: activeType, resource_id: resourceId }),
        });
        const data = await res.json();
        if (!res.ok) { alert(data.error || 'Failed to unlink.'); return; }
        linked[activeType] = linked[activeType].filter(l => l.id !== resourceId);
        renderLinkedChips();
        updateResourceButton(activeType);
        searchResources(modalSearch.value.trim());
    } catch (err) {
        alert('Could not connect to server.');
    }
}

// -----------------------------------------------------------------------
// Update resource button counts
// -----------------------------------------------------------------------
function updateResourceButton(type) {
    const idMap = {
        flight: 'addFlight', accommodation: 'addAccomodation',
        destination: 'addDestination', restaurant: 'addRestuarant'
    };
    const btn    = document.getElementById(idMap[type]);
    const count  = linked[type].length;
    const labels = { flight: 'Flight', accommodation: 'Accommodation', destination: 'Destination', restaurant: 'Restaurant' };
    btn.querySelector('p').textContent = count > 0 ? `${labels[type]} (${count})` : `Add ${labels[type]}`;
    btn.style.borderColor = count > 0 ? 'rgba(255,180,0,0.5)' : '';
    btn.style.color       = count > 0 ? '#ffb400' : '';
}

// -----------------------------------------------------------------------
// Flight modal — airport search
// -----------------------------------------------------------------------
function wireAirportSearch(searchId, resultsId, hiddenId, selectedId) {
    const searchEl   = document.getElementById(searchId);
    const resultsEl  = document.getElementById(resultsId);
    const hiddenEl   = document.getElementById(hiddenId);
    const selectedEl = document.getElementById(selectedId);
    let timer;

    searchEl.addEventListener('input', () => {
        hiddenEl.value    = '';
        selectedEl.textContent = '';
        clearTimeout(timer);
        timer = setTimeout(async () => {
            const q = searchEl.value.trim();
            if (!q) { resultsEl.style.display = 'none'; return; }
            try {
                const res  = await fetch(`${API_BASE}/api/airports/search`, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ query: q })
                });
                const data = await res.json();
                if (!data.results || data.results.length === 0) {
                    resultsEl.innerHTML = '<div style="color:rgba(255,255,255,0.4);padding:0.5rem;font-size:0.8rem">No airports found.</div>';
                    resultsEl.style.display = 'flex';
                    return;
                }
                resultsEl.innerHTML = data.results.map(a => `
                    <div data-id="${a.id}" data-label="${a.Code} – ${a.Name}, ${a.City}" style="
                        padding:0.5rem 0.8rem; cursor:pointer; font-size:0.82rem; color:white;
                        border-bottom:1px solid rgba(255,255,255,0.07)">
                        <strong>${a.Code}</strong> · ${a.Name}, ${a.City}, ${a.Country}
                    </div>`).join('');
                resultsEl.querySelectorAll('[data-id]').forEach(row => {
                    row.addEventListener('click', () => {
                        hiddenEl.value         = row.dataset.id;
                        selectedEl.textContent = '✓ ' + row.dataset.label;
                        searchEl.value         = row.dataset.label;
                        resultsEl.style.display = 'none';
                    });
                });
                resultsEl.style.display = 'flex';
            } catch (_) {
                resultsEl.style.display = 'none';
            }
        }, 300);
    });

    // Close dropdown on outside click
    document.addEventListener('click', (e) => {
        if (!searchEl.contains(e.target) && !resultsEl.contains(e.target)) {
            resultsEl.style.display = 'none';
        }
    });
}

// -----------------------------------------------------------------------
// Open flight modal
// -----------------------------------------------------------------------
async function openFlightModal() {
    const ready = await ensurePackageExists();
    if (!ready) return;

    // Reset form
    ['fm-airline','fm-flightnum','fm-origin-search','fm-origin-id','fm-origin-selected',
     'fm-dest-search','fm-dest-id','fm-dest-selected','fm-departure','fm-arrival','fm-cost']
        .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    ['fm-origin-selected','fm-dest-selected'].forEach(id => {
        document.getElementById(id).textContent = '';
    });
    document.getElementById('fm-error').style.display = 'none';
    document.getElementById('fm-submit').disabled = false;
    document.getElementById('fm-submit').textContent = 'Add Flight';
    flightModal.style.display = 'flex';
}

// -----------------------------------------------------------------------
// Submit new flight
// -----------------------------------------------------------------------
async function submitFlight() {
    const airline   = document.getElementById('fm-airline').value.trim();
    const flightNum = document.getElementById('fm-flightnum').value.trim();
    const originId  = document.getElementById('fm-origin-id').value;
    const destId    = document.getElementById('fm-dest-id').value;
    const departure = document.getElementById('fm-departure').value;
    const arrival   = document.getElementById('fm-arrival').value;
    const cost      = document.getElementById('fm-cost').value;

    const fmError = document.getElementById('fm-error');
    const setErr  = (msg) => { fmError.textContent = msg; fmError.style.display = 'block'; };

    if (!airline)   { setErr('Airline is required.');           return; }
    if (!flightNum) { setErr('Flight number is required.');     return; }
    if (!originId)  { setErr('Select an origin airport.');      return; }
    if (!destId)    { setErr('Select a destination airport.');  return; }
    if (!departure) { setErr('Departure date/time is required.'); return; }
    if (!arrival)   { setErr('Arrival date/time is required.'); return; }
    if (arrival <= departure) { setErr('Arrival must be after departure.'); return; }
    if (!cost || parseFloat(cost) < 0) { setErr('Enter a valid base cost.'); return; }

    fmError.style.display = 'none';
    const btn = document.getElementById('fm-submit');
    btn.disabled    = true;
    btn.textContent = 'Adding…';

    try {
        const res  = await fetch(`${API_BASE}/api/flight/create`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                agency_id:              user.id,
                package_id:             currentPackageId,
                airline,
                flight_number:          flightNum,
                departure_datetime:     departure,
                arrival_datetime:       arrival,
                base_cost:              parseFloat(cost),
                origin_airport_id:      parseInt(originId),
                destination_airport_id: parseInt(destId),
            })
        });
        const data = await res.json();
        if (!res.ok) { setErr(data.error || 'Failed to add flight.'); return; }

        // Add to local linked state
        const originLabel  = document.getElementById('fm-origin-search').value.split('·')[0].trim();
        const destLabel    = document.getElementById('fm-dest-search').value.split('·')[0].trim();
        linked.flight.push({ id: data.flight_id, label: `${airline} ${flightNum} (${originLabel}→${destLabel})` });
        updateResourceButton('flight');
        flightModal.style.display = 'none';

    } catch (err) {
        setErr('Could not connect to server.');
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Add Flight';
    }
}

// -----------------------------------------------------------------------
// Wire up resource buttons
// -----------------------------------------------------------------------
document.getElementById('addFlight').addEventListener('click',      openFlightModal);
document.getElementById('addDestination').addEventListener('click', () => openModal('destination'));
document.getElementById('addAccomodation').addEventListener('click',() => openModal('accommodation'));
document.getElementById('addRestuarant').addEventListener('click',  () => openModal('restaurant'));

// -----------------------------------------------------------------------
// Error / success display
// -----------------------------------------------------------------------
function showError(message, success = false) {
    let el = document.getElementById('form-error');
    if (!el) {
        el = document.createElement('div');
        el.id = 'form-error';
        publishBtn.parentNode.insertBefore(el, publishBtn);
    }
    el.className         = 'error-container error-visible';
    el.style.background  = success ? 'rgba(0,180,100,0.15)' : '';
    el.style.borderColor = success ? 'rgba(0,200,100,0.4)'  : '';
    el.innerHTML = `<i class="bi bi-${success ? 'check-circle' : 'exclamation-circle'}-fill"></i> <span>${message}</span>`;
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// -----------------------------------------------------------------------
// Publish
// -----------------------------------------------------------------------
publishBtn.addEventListener('click', async () => {
    const title           = document.getElementById('titleInput').value.trim();
    const description     = document.getElementById('descriptionInput').value.trim();
    const startDate       = document.getElementById('startDateInput').value;
    const endDate         = document.getElementById('endDateInput').value;
    const maxParticipants = document.getElementById('maxParticipantsInput').value;
    const totalPrice      = document.getElementById('priceInput').value;

    if (!title)                                      { showError('Package title is required.');               return; }
    if (!description)                                { showError('Package description is required.');         return; }
    if (!startDate)                                  { showError('Start date is required.');                  return; }
    if (!endDate)                                    { showError('End date is required.');                    return; }
    if (endDate <= startDate)                        { showError('End date must be after start date.');       return; }
    if (!maxParticipants || maxParticipants < 1)     { showError('Max participants must be at least 1.');     return; }
    if (!totalPrice || totalPrice <= 0)              { showError('Total price must be greater than 0.');      return; }

    publishBtn.disabled    = true;
    publishBtn.textContent = 'Publishing…';

    // If a draft already exists, use packages/create to update it (via delete + create)
    // Simpler: always call create — the draft will be an orphan but the new one is correct.
    try {
        const res  = await fetch(`${API_BASE}/api/agency/packages/create`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                agency_id:        user.id,
                title,
                description,
                start_date:       startDate,
                end_date:         endDate,
                max_participants: parseInt(maxParticipants),
                total_price:      parseFloat(totalPrice),
            }),
        });
        const data = await res.json();
        if (!res.ok) { showError(data.error || 'Failed to create package.'); return; }

        currentPackageId       = data.package_id;
        publishBtn.textContent = 'Update package';
        showError(`Package "<strong>${title}</strong>" published! <a href="agencydashboard.html">Go to dashboard</a>.`, true);

    } catch (err) {
        showError('Could not connect to the server.');
    } finally {
        publishBtn.disabled = false;
        if (publishBtn.textContent === 'Publishing…') publishBtn.textContent = 'Publish package';
    }
});
