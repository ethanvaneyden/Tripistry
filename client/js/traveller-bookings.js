const API_BASE = 'http://localhost/Tripistry/server/api.php';
const packageSection = document.querySelector('.package-section');

// -----------------------------------------------------------------------
// Auth guard
// -----------------------------------------------------------------------
const user = JSON.parse(sessionStorage.getItem('user') || 'null');
if (!user || user.role !== 'traveller') {
    window.location.href = '../index.html';
}

// -----------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------
function formatDate(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-ZA', {
        day: 'numeric', month: 'short', year: 'numeric'
    });
}

function formatPrice(price) {
    return new Intl.NumberFormat('en-ZA', {
        style: 'currency', currency: 'ZAR', maximumFractionDigits: 0
    }).format(price);
}

function statusBadge(status) {
    const colours = {
        Pending: 'background:rgba(255,180,0,0.2);   color:#ffb400;  border:1px solid rgba(255,180,0,0.4)',
        Confirmed: 'background:rgba(0,200,100,0.2);   color:#00c864;  border:1px solid rgba(0,200,100,0.4)',
        Cancelled: 'background:rgba(220,50,50,0.2);   color:#e05555;  border:1px solid rgba(220,50,50,0.4)',
        Completed: 'background:rgba(100,149,237,0.2); color:#6495ed;  border:1px solid rgba(100,149,237,0.4)',
    };
    const style = colours[status] || colours['Pending'];
    return `<span style="padding:0.2rem 0.6rem; border-radius:999px; font-size:0.75rem; font-weight:600; ${style}">${status}</span>`;
}

// -----------------------------------------------------------------------
// Build a booking card
// -----------------------------------------------------------------------
function createCard(booking) {
    const card = document.createElement('div');
    const nights = booking.Nights || 0;
    const location = [booking.City, booking.Country].filter(Boolean).join(', ') || 'Unknown destination';
    const thumbnail = booking.ThumbnailURL
        || 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600';

    card.classList.add('package-card');
    card.innerHTML = `
        <img
            class="package-image"
            src="${thumbnail}"
            alt="${booking.Title}"
            onerror="this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600'"
        />
        <div class="card-content">
            <h2 class="card-title">${booking.Title}</h2>
            <h3 class="card-destination">${location}</h3>
            <p class="card-description">${booking.Description || ''}</p>
            <ul class="card-meta">
                <li class="card-date">
                    ${formatDate(booking.StartDate)}
                    <small>(${nights} night${nights !== 1 ? 's' : ''})</small>
                </li>
                <li class="card-price">${formatPrice(booking.BookingPrice)}</li>
                <li class="card-agency">Offered by ${booking.AgencyName}</li>
                <li>
                    ${statusBadge(booking.Status)}
                    <small style="margin-left:0.5rem;color:rgba(255,255,255,0.5)">
                        Booked: ${formatDate(booking.BookingDate)}
                    </small>
                </li>
                ${booking.NumberOfPeople > 1
            ? `<li><i class="bi bi-people"></i> ${booking.NumberOfPeople} people</li>`
            : ''}
            </ul>
            <div style="display: flex; gap: 10px;">
                <a href="packagedetails.html?id=${booking.PackageID}" class="btn-outline" style="flex: 1; text-align: center;">
                    View package <i class="bi bi-chevron-double-right"></i>
                </a>
                ${booking.Status === 'Pending'
                    ? `<button class="btn-cancel-booking btn-outline" data-id="${booking.BookingID}" style="flex: 1; border-color: #e05555; color: #e05555;">
                           Cancel <i class="bi bi-x-circle"></i>
                       </button>`
                    : ''}
            </div>
        </div>`;

    return card;
}

// -----------------------------------------------------------------------
// Fetch and render
// -----------------------------------------------------------------------
async function fetchBookings() {
    packageSection.innerHTML = `
        <div class="no-results">
            <i class="bi bi-arrow-repeat" style="font-size:2rem;color:var(--amber)"></i>
            <p>Loading your bookings…</p>
        </div>`;

    try {
        const res = await fetch(`${API_BASE}/api/traveller/bookings`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ traveller_id: user.id }),
        });
        const data = await res.json();

        if (!res.ok) {
            packageSection.innerHTML = `<div class="no-results"><p>${data.error}</p></div>`;
            return;
        }

        packageSection.innerHTML = '';

        if (!data.bookings || data.bookings.length === 0) {
            packageSection.innerHTML = `
                <div class="no-results">
                    <i class="bi bi-suitcase-lg" style="font-size:2rem;color:var(--amber)"></i>
                    <p>You haven't booked any packages yet.
                    </p>
                    <a class="go-book" href="browsepackages.html">Browse packages</a>
                </div>`;
            return;
        }

        data.bookings.forEach(b => packageSection.appendChild(createCard(b)));

    } catch (err) {
        packageSection.innerHTML = `
            <div class="no-results">
                <p>Could not connect to the server. Make sure XAMPP is running.</p>
            </div>`;
    }
}

fetchBookings();

// -----------------------------------------------------------------------
// Handle Cancel Booking
// -----------------------------------------------------------------------
document.addEventListener('click', async (e) => {
    if (e.target.classList.contains('btn-cancel-booking')) {
        if (!confirm("Are you sure you want to cancel this pending booking?")) return;

        const bookingId = e.target.getAttribute('data-id');
        if (!bookingId) return;

        const btn = e.target;
        const originalText = btn.textContent;
        btn.textContent = 'Cancelling...';
        btn.disabled = true;

        try {
            const res = await fetch(`${API_BASE}/api/booking/cancel`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    booking_id: parseInt(bookingId),
                    traveller_id: user.id
                })
            });

            const data = await res.json();

            if (!res.ok) {
                alert(data.error || 'Failed to cancel booking.');
                btn.textContent = originalText;
                btn.disabled = false;
                return;
            }

            // Refresh the bookings list to instantly show the red 'Cancelled' badge
            fetchBookings();

        } catch (err) {
            alert('Error communicating with the server.');
            btn.textContent = originalText;
            btn.disabled = false;
        }
    }
});
