const API_BASE = "/api.php";
const packageSection = document.querySelector(".package-section");

// -----------------------------------------------------------------------
// Auth guard
// -----------------------------------------------------------------------
const user = JSON.parse(sessionStorage.getItem("user") || "null");
if (!user || user.role !== "traveller") {
  window.location.href = "../index.html";
}

// -----------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------
function formatDate(dateStr) {
  if (!dateStr) return "";
  return new Date(dateStr).toLocaleDateString("en-ZA", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
}

function formatPrice(price) {
  return new Intl.NumberFormat("en-ZA", {
    style: "currency",
    currency: "ZAR",
    maximumFractionDigits: 0,
  }).format(price);
}

function statusBadge(status) {
    const key = (status || 'Pending').toLowerCase();
    return `<span class="status-badge badge-${key}">${status}</span>`;
}

// -----------------------------------------------------------------------
// Confirmation Modal Helper
// -----------------------------------------------------------------------
function showConfirmation(title, message) {
    return new Promise((resolve) => {
        const modal = document.getElementById('confirmationModal');
        const titleEl = document.getElementById('confirmTitle');
        const messageEl = document.getElementById('confirmMessage');
        const yesBtn = document.getElementById('confirmYes');
        const noBtn = document.getElementById('confirmNo');
        const closeBtn = document.getElementById('confirmClose');

        titleEl.textContent = title;
        messageEl.textContent = message;

        const cleanup = () => {
            yesBtn.removeEventListener('click', handleYes);
            noBtn.removeEventListener('click', handleNo);
            closeBtn.removeEventListener('click', handleNo);
        };

        const handleYes = () => {
            cleanup();
            modal.close();
            resolve(true);
        };

        const handleNo = () => {
            cleanup();
            modal.close();
            resolve(false);
        };

        yesBtn.addEventListener('click', handleYes);
        noBtn.addEventListener('click', handleNo);
        closeBtn.addEventListener('click', handleNo);

        modal.showModal();
    });
}

// -----------------------------------------------------------------------
// Build a booking card
// -----------------------------------------------------------------------
function createCard(booking) {
  const card = document.createElement("div");
  const nights = booking.Nights || 0;
  const location =
    [booking.City, booking.Country].filter(Boolean).join(", ") ||
    "Unknown destination";
  const thumbnail =
    booking.ThumbnailURL ||
    "https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600";

  card.classList.add("package-card");
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
            <p class="card-description">${booking.Description || ""}</p>
            <ul class="card-meta">
                <li class="card-date">
                    ${formatDate(booking.StartDate)}
                    <small>(${nights} night${nights !== 1 ? "s" : ""})</small>
                </li>
                <li class="card-price">${formatPrice(booking.BookingPrice)}</li>
                <li class="card-agency">Offered by ${booking.AgencyName}</li>
                 <li>
                    <small class="booked-date">Booked: ${formatDate(booking.BookingDate)}</small>
                </li>
                <li>
                    ${statusBadge(booking.Status)}
                </li>
               
                ${booking.NumberOfPeople > 1
            ? `<li><i class="bi bi-people"></i> ${booking.NumberOfPeople} people</li>`
            : ''}
            </ul>
            <div class="action-row">
                <a href="packagedetails.html?id=${booking.PackageID}" class="btn-outline btn-stretch">
                    View package <i class="bi bi-chevron-double-right"></i>
                </a>
                ${booking.Status === 'Pending'
                    ? `<button class="btn-cancel-booking btn-outline btn-cancel" data-id="${booking.BookingID}">
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
            <i class="bi bi-arrow-repeat icon-2rem icon-amber"></i>
            <p>Loading your bookings…</p>
        </div>`;

  try {
    const res = await fetch(`${API_BASE}/api/traveller/bookings`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ traveller_id: user.id }),
    });
    const data = await res.json();

    if (!res.ok) {
      packageSection.innerHTML = `<div class="no-results"><p>${data.error}</p></div>`;
      return;
    }

    packageSection.innerHTML = "";

    if (!data.bookings || data.bookings.length === 0) {
      packageSection.innerHTML = `
                <div class="no-results">
                    <i class="bi bi-suitcase-lg icon-2rem icon-amber"></i>
                    <p>You haven't booked any packages yet.
                    </p>
                    <a class="go-book" href="browsepackages.html">Browse packages</a>
                </div>`;
      return;
    }

    data.bookings.forEach((b) => packageSection.appendChild(createCard(b)));
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
        const confirmed = await showConfirmation(
            'Cancel Booking?',
            'Are you sure you want to cancel this pending booking? This action cannot be undone.'
        );
        if (!confirmed) return;

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
                const confirmed = await showConfirmation(
                    'Error',
                    data.error || 'Failed to cancel booking.'
                );
                btn.textContent = originalText;
                btn.disabled = false;
                return;
            }

            // Refresh the bookings list to instantly show the red 'Cancelled' badge
            fetchBookings();

        } catch (err) {
            const confirmed = await showConfirmation(
                'Connection Error',
                'Error communicating with the server.'
            );
            btn.textContent = originalText;
            btn.disabled = false;
        }
    }
});
