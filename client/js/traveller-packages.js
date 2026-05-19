const packagesection = document.getElementById('package-section');

const packages = [];


packages.push({
    PackageID: 1,
    AgencyName: "Wanderlust SA Ltd",
    Title: "Ultimate Cape Town Coastal Escape",
    Description: "A beautiful getaway featuring coastal sights, wine tasting, and premium dining.",
    StartDate: "2026-06-15",
    EndDate: "2026-06-22",
    MaxParticipants: 12,
    TotalPrice: 14500.00,
    ThumbnailURL: "https://newplacestogo.com/wp-content/uploads/2023/02/bartinney-2-min-scaled.jpg",
    Location: 'Cape Town, South Africa'
});

packages.push({
    PackageID: 2,
    AgencyName: "Safari Specialists",
    Title: "Wild Kruger Luxury Safari",
    Description: "Experience the Big Five with luxury lodge stays and guided game drives.",
    StartDate: "2026-07-01",
    EndDate: "2026-07-06",
    MaxParticipants: 8,
    TotalPrice: 22000.00,
    ThumbnailURL: "https://images.unsplash.com/photo-1516426122078-c23e76319801",
    Location: 'Kruger National Park, South Africa'
});

// 3. Paris City Break
packages.push({
    PackageID: 3,
    AgencyName: "EuroQuest Travels",
    Title: "Parisian Romance & Culture",
    Description: "A curated tour of the Louvre, Eiffel Tower, and hidden bistros in Montmartre.",
    StartDate: "2026-09-10",
    EndDate: "2026-09-15",
    MaxParticipants: 10,
    TotalPrice: 35000.00,
    ThumbnailURL: "https://images.unsplash.com/photo-1502602898657-3e91760cbb34",
    Location: 'Paris, France'

});

packages.push({
    PackageID: 4,
    AgencyName: "Pacific Dream Tours",
    Title: "Ubud Zen & Wellness Escape",
    Description: "Rejuvenate with daily yoga, traditional spa treatments, and rice terrace tours.",
    StartDate: "2026-08-20",
    EndDate: "2026-08-28",
    MaxParticipants: 15,
    TotalPrice: 18500.00,
    ThumbnailURL: "https://images.unsplash.com/photo-1537996194471-e657df975ab4",
    Location: 'Bali, Indonesia'
});

function createCard(imageURL, title, location, description, dateString, price, agencyName) {
    const card = document.createElement('div');
    card.classList.add('package-card');
    card.innerHTML = `
          <img
            class="package-image"
            src="${imageURL}"
            alt="Package picture"

          />
          <div class="card-content">
            <h2 class="card-title">${title}</h2>
            <h3 class="card-destination">${location}</h3>
            <p class="card-description">
              ${description}
            </p>

            <ul class="card-meta">
              <li class="card-date">${dateString} <small>(7 nights)</small></li>
              <li class="card-price">R${price} <small>/ person</small></li>
              <li class="card-agency">Offered by ${agencyName}</li>
              <li class="rating">
                <span class="stars">★★★★☆</span>
                <span>4.7</span>
              </li>
            </ul>
            <a href="packagedetails.html" class="btn-outline">
              View package<i class="bi bi-chevron-double-right"></i>
            </a>`;
    return card;
}

async function renderPackages(packages) {
    packages.forEach(package => {
        const card = createCard(package.ThumbnailURL,
            package.Title,
            package.Location,
            package.Description,
            '15-22 June',
            package.TotalPrice,
            package.AgencyName)
        packagesection.appendChild(card);

    });
}

renderPackages(packages);

