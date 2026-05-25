const API_BASE = 'http://localhost/Tripistry/server/api.php';

// -----------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------
function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-ZA', { day: 'numeric', month: 'long', year: 'numeric' });
}

function formatTime(datetimeStr) {
    if (!datetimeStr) return '';
    const d = new Date(datetimeStr);
    return d.toLocaleTimeString('en-ZA', { hour: '2-digit', minute: '2-digit' });
}

function formatPrice(price) {
    return new Intl.NumberFormat('en-ZA', {
        style: 'currency', currency: 'ZAR', maximumFractionDigits: 2
    }).format(price);
}

function renderStarIcons(rating) {
    let html = '';
    for (let i = 1; i <= 5; i++) {
        html += `<i class="bi bi-star${i <= rating ? '-fill' : ''}"></i>`;
    }
    return html;
}

function renderStarText(rating) {
    let s = '';
    for (let i = 1; i <= 5; i++) s += i <= Math.round(rating) ? '★' : '☆';
    return s;
}

// -----------------------------------------------------------------------
// Section renderers
// -----------------------------------------------------------------------
function renderHero(pkg) {
    const img = pkg.Images && pkg.Images.length > 0
        ? pkg.Images[0]
        : 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1200';

    const destinations = pkg.Destinations && pkg.Destinations.length > 0
        ? pkg.Destinations.map(d => `${d.City}, ${d.Country}`).join(' · ')
        : '';

    document.querySelector('.hero-image').src  = img;
    document.querySelector('.hero-image').alt  = pkg.Title;
    document.querySelector('.package-title').textContent   = pkg.Title;
    document.querySelector('.package-location').innerHTML  =
        `<i class="bi bi-geo-alt-fill"></i> ${destinations}`;
    document.title = `Tripistry — ${pkg.Title}`;
}

function renderAbout(pkg) {
    document.querySelector('.info-card .card-description').textContent = pkg.Description || '';
}

function renderFlights(flights) {
    const card = document.querySelector('.split-grid .info-card:first-child');
    if (!flights || flights.length === 0) {
        card.innerHTML = `
            <h2 class="section-title"><i class="bi bi-airplane"></i> Flight Details</h2>
            <p class="sub-line">No flights included in this package.</p>`;
        return;
    }

    const rows = flights.map(f => `
        <div class="data-row">
            <span class="main-line">
                ${f.Airline} <small class="flight-number">(${f.FlightNumber})</small>
            </span>
            <span class="sub-line">${f.OriginCode} &rarr; ${f.DestinationCode}</span>
            <span class="sub-line">
                <i class="bi bi-clock"></i>
                Departs: ${formatTime(f.DepartureDateTime)} &nbsp;|&nbsp;
                Arrives: ${formatTime(f.ArrivalDateTime)}
            </span>
            <span class="sub-line">
                <i class="bi bi-calendar3"></i> ${formatDate(f.DepartureDateTime)}
            </span>
        </div>`).join('<hr class="hr-muted">');

    card.innerHTML = `
        <h2 class="section-title"><i class="bi bi-airplane"></i> Flight Details</h2>
        ${rows}`;
}

function renderAccommodations(accommodations) {
    const card = document.querySelector('.split-grid .info-card:last-child');
    if (!accommodations || accommodations.length === 0) {
        card.innerHTML = `
            <h2 class="section-title"><i class="bi bi-building-check"></i> Accommodation</h2>
            <p class="sub-line">No accommodation included in this package.</p>`;
        return;
    }

    const rows = accommodations.map(ac => `
        <div class="data-row">
            <span class="main-line">${ac.Name}</span>
            <div class="hotel-stars">${renderStarIcons(ac.StarRating || 0)}</div>
            <span class="sub-line">
                <i class="bi bi-tag"></i> ${ac.Type || ''}
                &nbsp;·&nbsp; ${formatPrice(ac.AveragePricePerNight)} / night
            </span>
            <span class="sub-line">
                <i class="bi bi-map"></i>
                ${[ac.Street, ac.City, ac.Country].filter(Boolean).join(', ')}
            </span>
        </div>`).join('<hr class="hr-muted">');

        card.innerHTML = `
        <h2 class="section-title"><i class="bi bi-building-check"></i> Accommodation</h2>
        ${rows}`;
}

function renderAttractions(attractions) {
    const card = document.getElementById('attractions-card');
    if (!attractions || attractions.length === 0) {
        card.classList.add('hidden');
        return;
    }

    const items = attractions.map(at => `
        <div class="highlight-item">
            <i class="bi bi-ticket-perforated"></i>
            <div class="highlight-text">
                <strong>${at.Name}</strong>
                <small>
                    ${at.OpeningHours ? `Hours: ${at.OpeningHours}` : ''}
                    ${at.OpeningHours && at.EntranceFee > 0 ? ' | ' : ''}
                    ${at.EntranceFee > 0 ? `Fee: ${formatPrice(at.EntranceFee)}` : (at.OpeningHours ? '' : 'Free entry')}
                </small>
                ${at.Description ? `<span class="muted small">${at.Description}</span>` : ''}
            </div>
        </div>`).join('');

    card.innerHTML = `
        <h2 class="section-title"><i class="bi bi-compass"></i> Included Highlights</h2>
        <div class="highlights-list">${items}</div>`;
}

function renderRestaurants(restaurants) {
    const card = document.getElementById('restaurants-card');
    if (!restaurants || restaurants.length === 0) {
        card.classList.add('hidden');
        return;
    }

    const items = restaurants.map(r => `
        <div class="highlight-item">
            <i class="bi bi-egg-fried"></i>
            <div class="highlight-text">
                <strong>${r.Name}</strong>
                <small>
                    ${r.Cuisine ? `Cuisine: ${r.Cuisine}` : ''}
                    ${r.Cuisine && r.PriceRange ? ' | ' : ''}
                    ${r.PriceRange ? `Price Range: ${r.PriceRange}` : ''}
                </small>
                ${r.City ? `<span class="muted small">
                    <i class="bi bi-map"></i>
                    ${[r.Street, r.City, r.Country].filter(Boolean).join(', ')}
                </span>` : ''}
            </div>
        </div>`).join('');

    card.innerHTML = `
        <h2 class="section-title"><i class="bi bi-egg-fill"></i> Included Restaurants</h2>
        <div class="highlights-list">${items}</div>`;
}

function renderReviews(reviews, avgRating, reviewCount) {
    const card = document.getElementById('review-card');

    if (!reviews || reviews.length === 0) {
        card.innerHTML = `
            <h2 class="section-title"><i class="bi bi-chat-left-text"></i> Traveller Reviews</h2>
            <p class="sub-line muted">
                No reviews yet. Be the first to review this package!
            </p>`;
        return;
    }

    const summary = avgRating > 0
        ? `<div class="rating-summary">
               <span class="big-rating">${avgRating.toFixed(1)}</span>
               <div>
                   <div class="stars stars-small">${renderStarText(avgRating)}</div>
                   <small class="muted">${reviewCount} review${reviewCount !== 1 ? 's' : ''}</small>
               </div>
           </div>`
        : '';

    const blocks = reviews.map(rv => `
        <div class="review-block">
            <div class="review-header">
                <strong>${rv.TravellerName}</strong>
                <span class="stars">${renderStarText(rv.Rating)}</span>
            </div>
            <p>${rv.Comment || ''}</p>
            <small class="small-muted">
                ${new Date(rv.CreatedAt).toLocaleDateString('en-ZA', { day: 'numeric', month: 'long', year: 'numeric' })}
            </small>
        </div>`).join('');

    card.innerHTML = `
        <h2 class="section-title"><i class="bi bi-chat-left-text"></i> Traveller Reviews</h2>
        ${summary}
        ${blocks}`;
}

function renderBookingWidget(pkg) {
    const nights    = pkg.Nights || 0;
    const dateRange = `${formatDate(pkg.StartDate)} – ${formatDate(pkg.EndDate)}`;

    document.querySelector('.widget-price').textContent = formatPrice(pkg.TotalPrice);
    document.querySelector('.widget-meta').innerHTML = `
        <li><i class="bi bi-calendar3"></i> ${dateRange} (${nights} night${nights !== 1 ? 's' : ''})</li>
        <li><i class="bi bi-building"></i> ${pkg.Agency.Name}</li>
        <li><i class="bi bi-envelope"></i> ${pkg.Agency.Email}</li>
        ${pkg.Agency.Phone
            ? `<li><i class="bi bi-telephone"></i> ${pkg.Agency.Phone}</li>`
            : ''}
        ${parseInt(pkg.MaxParticipants) > 1
            ? `<li><i class="bi bi-people"></i> Max ${pkg.MaxParticipants} participants</li>`
            : ''}`;

    // Initialise the booking modal with live package data
    initBookingModal({
        packageId:       pkg.PackageID,
        title:           pkg.Title,
        startDate:       pkg.StartDate,
        endDate:         pkg.EndDate,
        nights:          pkg.Nights,
        pricePerPerson:  pkg.TotalPrice,
        maxParticipants: pkg.MaxParticipants || null
    });

    // Disable button if traveller already has an active booking
    const user = JSON.parse(sessionStorage.getItem('user') || 'null');
    if (user && user.role === 'traveller') {
        checkAlreadyBooked(pkg.PackageID, user.id);
    }
}

// -----------------------------------------------------------------------
// Show a full-page error
// -----------------------------------------------------------------------
function showPageError(message) {
    document.querySelector('main').innerHTML = `
        <div class="page-error">
            <i class="bi bi-exclamation-circle error-icon"></i>
            <h2>${message}</h2>
            <a href="browsepackages.html" class="btn-outline btn-inline">
                <i class="bi bi-arrow-left"></i> Back to packages
            </a>
        </div>`;
}

// -----------------------------------------------------------------------
// Main: read ?id= param and fetch
// -----------------------------------------------------------------------
async function init() {
    const params     = new URLSearchParams(window.location.search);
    const packageId  = params.get('id');

    if (!packageId || isNaN(parseInt(packageId))) {
        showPageError('No package specified.');
        return;
    }

    try {
        const res  = await fetch(`${API_BASE}/api/packages/details`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ package_id: parseInt(packageId) }),
        });
        const data = await res.json();

        if (!res.ok) {
            showPageError(data.error || 'Package not found.');
            return;
        }

        const pkg = data.package;

        renderHero(pkg);
        renderAbout(pkg);
        renderFlights(pkg.Flights);
        renderAccommodations(pkg.Accommodations);
        renderAttractions(pkg.Attractions);
        renderRestaurants(pkg.Restaurants);
        renderReviews(pkg.Reviews, pkg.AvgRating, pkg.ReviewCount);
        renderBookingWidget(pkg);

    } catch (err) {
        showPageError('Could not connect to the server.');
        console.error(err.message);
    }
}

init();

// -----------------------------------------------------------------------
// Check if logged-in traveller already booked this package
// -----------------------------------------------------------------------
async function checkAlreadyBooked(packageId, travellerId) {
    try {
        const res  = await fetch(`${API_BASE}/api/booking/check`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ traveller_id: travellerId, package_id: packageId })
        });
        const data = await res.json();
        if (res.ok && data.already_booked) {
            const btn = document.querySelector('.btn-reserve');
            btn.disabled   = true;
            btn.innerHTML  = `<i class="bi bi-check-circle-fill"></i> Already Booked`;
            btn.classList.add('disabled');
        }
    } catch (_) {
        // silently ignore — non-critical
    }
}
