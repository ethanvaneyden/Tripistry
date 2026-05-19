const packagesection = document.getElementById('package-section');

const packages = [];


packages.push({
  PackageID: 17,
  AgencyName: "Wanderlust SA Ltd",
  Title: "Winelands Wellness & Spa Retreat",
  Description: "Indulge in a 5-day luxury escape in Stellenbosch featuring private vineyard tours and premium spa treatments.",
  StartDate: "2026-08-10",
  EndDate: "2026-08-15",
  MaxParticipants: 6,
  TotalPrice: 18900.00,
  ThumbnailURL: "https://cdn.audleytravel.com/-/-/79/202212062016038075235029202078092005131161128165.jpg",
  Location: 'Cape Town, South Africa'
});

packages.push({
  PackageID: 18,
  AgencyName: "Wanderlust SA Ltd",
  Title: "Gauteng Heritage: Cradle & Soweto",
  Description: "Explore the origins of humanity at Maropeng followed by a cultural immersion in the heart of Soweto.",
  StartDate: "2026-09-05",
  EndDate: "2026-09-08",
  MaxParticipants: 20,
  TotalPrice: 6200.00,
  ThumbnailURL: "https://picsum.photos/id/1074/400/300",
  Location: 'Cradle of Humankind, South Africa'
});

packages.push({
  PackageID: 19,
  AgencyName: "Wanderlust SA Ltd",
  Title: "Drakensberg: The Royal Natal Hike",
  Description: "A 4-night guided expedition to the Tugela Falls and the Amphitheatre for adventure-seeking travellers.",
  StartDate: "2026-10-12",
  EndDate: "2026-10-16",
  MaxParticipants: 10,
  TotalPrice: 11500.00,
  ThumbnailURL: "https://images.unsplash.com/photo-1589182373726-e4f658ab50f0?w=400&q=60",
  Location: 'Drankensberg, South Africa'
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
            <div class="side-by-side-btn">
            <a href="packagedetails.html" class="btn-outline">
              Edit<i class="bi bi-pencil-square"></i>
            </a>
            <a href="packagedetails.html" class="btn-outline">
              Delete<i class="bi bi-trash3"></i>
            </a>
            </div>`;
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

