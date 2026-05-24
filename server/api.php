<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


$db_host = "127.0.0.1";
$db_user = "root";
$db_pass = "";
$db_name = "tripistry";

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection failed: " . $e->getMessage()]);
    exit();
}


function getBody(): array {
    $raw = file_get_contents("php://input");
    return json_decode($raw, true) ?? [];
}

function respond(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload);
    exit();
}

function requireFields(array $data, array $fields): void {
    foreach ($fields as $f) {
        if (empty($data[$f])) {
            respond(400, ["success" => false, "error" => "Missing required field: $f"]);
        }
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


if ($method === 'POST' && strpos($uri, '/api/register/traveller') !== false) {
    $data = getBody();
    requireFields($data, ['name','surname','email','password','phone','nationality','dateofbirth']);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO Traveller (FirstName, Surname, Email, Password, Phone, Nationality, DateOfBirth, JoinDate)
            VALUES (:name, :surname, :email, :password, :phone, :nationality, :dob, CURDATE())
        ");
        $stmt->execute([
            ':name'        => $data['name'],
            ':surname'     => $data['surname'],
            ':email'       => $data['email'],
            ':password'    => password_hash($data['password'], PASSWORD_DEFAULT),
            ':phone'       => $data['phone'],
            ':nationality' => $data['nationality'],
            ':dob'         => $data['dateofbirth']
        ]);
        respond(201, ["success" => true, "travellerID" => (int)$pdo->lastInsertId(), "message" => "Traveller registered successfully"]);
    } catch (PDOException $e) {
        $code = $e->getCode() == 23000 ? 409 : 500;
        $msg  = $e->getCode() == 23000 ? "Email already in use" : "Registration failed: " . $e->getMessage();
        respond($code, ["success" => false, "error" => $msg]);
    }
}


if ($method === 'POST' && strpos($uri, '/api/register/agency') !== false) {
    $data = getBody();
    requireFields($data, ['name','email','password','phone','street','city','country']);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO Agency (Name, Email, Password, Phone, JoinDate, Street, City, Country)
            VALUES (:name, :email, :password, :phone, CURDATE(), :street, :city, :country)
        ");
        $stmt->execute([
            ':name'     => $data['name'],
            ':email'    => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':phone'    => $data['phone'],
            ':street'   => $data['street'],
            ':city'     => $data['city'],
            ':country'  => $data['country']
        ]);
        respond(201, ["success" => true, "agencyID" => (int)$pdo->lastInsertId(), "message" => "Agency registered successfully"]);
    } catch (PDOException $e) {
        $code = $e->getCode() == 23000 ? 409 : 500;
        $msg  = $e->getCode() == 23000 ? "Email already in use" : "Registration failed: " . $e->getMessage();
        respond($code, ["success" => false, "error" => $msg]);
    }
}


if ($method === 'POST' && strpos($uri, '/api/login') !== false) {
    $data = getBody();
    requireFields($data, ['email','password']);

    $email    = $data['email'];
    $password = $data['password'];

   
    $stmt = $pdo->prepare("SELECT TravellerID, FirstName, Surname, Email, Password FROM Traveller WHERE Email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['Password'])) {
        respond(200, [
            "success" => true,
            "role"    => "traveller",
            "user"    => ["id" => $user['TravellerID'], "firstName" => $user['FirstName'], "surname" => $user['Surname'], "email" => $user['Email']]
        ]);
    }

    
    $stmt = $pdo->prepare("SELECT AgencyID, Name, Email, Password FROM Agency WHERE Email = :email");
    $stmt->execute([':email' => $email]);
    $agency = $stmt->fetch();

    if ($agency && password_verify($password, $agency['Password'])) {
        respond(200, [
            "success" => true,
            "role"    => "agency",
            "user"    => ["id" => $agency['AgencyID'], "name" => $agency['Name'], "email" => $agency['Email']]
        ]);
    }

    respond(401, ["success" => false, "error" => "Invalid email or password"]);
}


if ($method === 'GET' && strpos($uri, '/api/travellers/profile') !== false) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) respond(400, ["success" => false, "error" => "Missing traveller id"]);

    $stmt = $pdo->prepare("SELECT TravellerID, FirstName, Surname, Email, Phone, Nationality, JoinDate FROM Traveller WHERE TravellerID = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if (!$row) respond(404, ["success" => false, "error" => "Traveller not found"]);
    respond(200, ["success" => true, "data" => $row]);
}


if ($method === 'GET' && strpos($uri, '/api/agencies/profile') !== false) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) respond(400, ["success" => false, "error" => "Missing agency id"]);

    $stmt = $pdo->prepare("SELECT AgencyID, Name, Email, Phone, Street, City, Country, JoinDate FROM Agency WHERE AgencyID = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if (!$row) respond(404, ["success" => false, "error" => "Agency not found"]);
    respond(200, ["success" => true, "data" => $row]);
}


if ($method === 'GET' && strpos($uri, '/api/packages/list') !== false) {
    $where   = ["1=1"];
    $params  = [];

    if (!empty($_GET['destination'])) {
        $where[]  = "d.City LIKE :destination";
        $params[':destination'] = '%' . $_GET['destination'] . '%';
    }
    if (!empty($_GET['minPrice'])) {
        $where[]  = "p.TotalPrice >= :minPrice";
        $params[':minPrice'] = (float)$_GET['minPrice'];
    }
    if (!empty($_GET['maxPrice'])) {
        $where[]  = "p.TotalPrice <= :maxPrice";
        $params[':maxPrice'] = (float)$_GET['maxPrice'];
    }
    if (!empty($_GET['startDate'])) {
        $where[]  = "p.StartDate >= :startDate";
        $params[':startDate'] = $_GET['startDate'];
    }
    if (!empty($_GET['duration'])) {
        $where[]  = "DATEDIFF(p.EndDate, p.StartDate) = :duration";
        $params[':duration'] = (int)$_GET['duration'];
    }

    $allowed_sort = ['price' => 'p.TotalPrice', 'rating' => 'avgRating', 'startDate' => 'p.StartDate'];
    $sortCol      = $allowed_sort[$_GET['sortBy'] ?? ''] ?? 'p.PackageID';
    $sortDir      = strtoupper($_GET['sortOrder'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

    $limit  = max(1, min(50, (int)($_GET['limit']  ?? 10)));
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $limit;

    $whereStr = implode(" AND ", $where);

    
    $countSql = "
        SELECT COUNT(DISTINCT p.PackageID) as total
        FROM Package p
        LEFT JOIN Agency a ON p.AgencyID = a.AgencyID
        LEFT JOIN PackageDestination pd ON p.PackageID = pd.PackageID
        LEFT JOIN Destination d ON pd.DestinationID = d.DestinationID
        WHERE $whereStr
    ";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $sql = "
        SELECT
            p.PackageID, p.Title, p.Description, p.TotalPrice,
            p.StartDate, p.EndDate, p.MaxParticipants,
            a.Name AS agencyName,
            ROUND(AVG(pr.Rating), 1) AS avgRating,
            COUNT(DISTINCT pr.ReviewID) AS reviewCount
        FROM Package p
        LEFT JOIN Agency a ON p.AgencyID = a.AgencyID
        LEFT JOIN PackageDestination pd ON p.PackageID = pd.PackageID
        LEFT JOIN Destination d ON pd.DestinationID = d.DestinationID
        LEFT JOIN PackageReview pr ON p.PackageID = pr.PackageID
        WHERE $whereStr
        GROUP BY p.PackageID
        ORDER BY $sortCol $sortDir
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    respond(200, ["success" => true, "total" => $total, "page" => $page, "data" => $stmt->fetchAll()]);
}


if ($method === 'GET' && strpos($uri, '/api/packages/get') !== false) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) respond(400, ["success" => false, "error" => "Missing package id"]);

   
    $stmt = $pdo->prepare("
        SELECT p.PackageID, p.Title, p.Description, p.TotalPrice, p.StartDate, p.EndDate, p.MaxParticipants,
               a.AgencyID, a.Name AS agencyName, a.Email AS agencyEmail, a.Phone AS agencyPhone
        FROM Package p
        JOIN Agency a ON p.AgencyID = a.AgencyID
        WHERE p.PackageID = :id
    ");
    $stmt->execute([':id' => $id]);
    $pkg = $stmt->fetch();
    if (!$pkg) respond(404, ["success" => false, "error" => "Package not found"]);

    $agency = [
        "agencyID"    => $pkg['AgencyID'],
        "name"        => $pkg['agencyName'],
        "email"       => $pkg['agencyEmail'],
        "phone"       => $pkg['agencyPhone']
    ];
    unset($pkg['AgencyID'], $pkg['agencyName'], $pkg['agencyEmail'], $pkg['agencyPhone']);

    
    $stmt = $pdo->prepare("
        SELECT d.DestinationID, d.Name, d.City, d.Region, d.Country, d.Description
        FROM PackageDestination pd JOIN Destination d ON pd.DestinationID = d.DestinationID
        WHERE pd.PackageID = :id
    ");
    $stmt->execute([':id' => $id]);
    $destinations = $stmt->fetchAll();

    
    $stmt = $pdo->prepare("
        SELECT a.AccommodationID, a.Name, a.Type, a.StarRating, a.AveragePricePerNight, a.Street, a.City, a.Country
        FROM PackageAccommodation pa JOIN Accommodation a ON pa.AccommodationID = a.AccommodationID
        WHERE pa.PackageID = :id
    ");
    $stmt->execute([':id' => $id]);
    $accommodations = $stmt->fetchAll();

   
    $stmt = $pdo->prepare("
        SELECT f.FlightID, f.FlightNumber, f.Airline, f.DepartureDateTime, f.ArrivalDateTime, f.BaseCost,
               orig.Code AS originCode, orig.Name AS originName, orig.City AS originCity,
               dest.Code AS destCode,   dest.Name AS destName,   dest.City AS destCity
        FROM PackageFlight pf
        JOIN Flight f ON pf.FlightID = f.FlightID
        JOIN Airport orig ON f.OriginAirportID = orig.AirportID
        JOIN Airport dest ON f.DestinationAirportID = dest.AirportID
        WHERE pf.PackageID = :id
    ");
    $stmt->execute([':id' => $id]);
    $rawFlights = $stmt->fetchAll();
    $flights = array_map(function($f) {
        return [
            "flightID"          => $f['FlightID'],
            "flightNumber"      => $f['FlightNumber'],
            "airline"           => $f['Airline'],
            "departureDateTime" => $f['DepartureDateTime'],
            "arrivalDateTime"   => $f['ArrivalDateTime'],
            "baseCost"          => $f['BaseCost'],
            "origin"            => ["code" => $f['originCode'], "name" => $f['originName'], "city" => $f['originCity']],
            "destination"       => ["code" => $f['destCode'],   "name" => $f['destName'],   "city" => $f['destCity']]
        ];
    }, $rawFlights);

    
    $stmt = $pdo->prepare("
        SELECT a.AttractionID, a.Name, a.Description, a.EntranceFee, a.OpeningHours, a.City, a.Country
        FROM PackageAttraction pa JOIN Attraction a ON pa.AttractionID = a.AttractionID
        WHERE pa.PackageID = :id
    ");
    $stmt->execute([':id' => $id]);
    $attractions = $stmt->fetchAll();

    
    $stmt = $pdo->prepare("
        SELECT r.RestaurantID, r.Name, r.Cuisine, r.PriceRange, r.City, r.Country
        FROM PackageRestaurant pr JOIN Restaurant r ON pr.RestaurantID = r.RestaurantID
        WHERE pr.PackageID = :id
    ");
    $stmt->execute([':id' => $id]);
    $restaurants = $stmt->fetchAll();

    
    $stmt = $pdo->prepare("
        SELECT pr.ReviewID, CONCAT(t.FirstName, ' ', LEFT(t.Surname, 1), '.') AS travellerName,
               pr.Rating, pr.Comment, pr.CreatedAt
        FROM PackageReview pr JOIN Traveller t ON pr.TravellerID = t.TravellerID
        WHERE pr.PackageID = :id
        ORDER BY pr.CreatedAt DESC
    ");
    $stmt->execute([':id' => $id]);
    $reviews = $stmt->fetchAll();

    respond(200, ["success" => true, "data" => array_merge($pkg, [
        "agency"         => $agency,
        "destinations"   => $destinations,
        "accommodations" => $accommodations,
        "flights"        => $flights,
        "attractions"    => $attractions,
        "restaurants"    => $restaurants,
        "reviews"        => $reviews
    ])]);
}


if ($method === 'POST' && strpos($uri, '/api/packages/create') !== false) {
    $data = getBody();
    requireFields($data, ['agencyID','title','startDate','endDate','maxParticipants','totalPrice']);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO Package (AgencyID, Title, Description, StartDate, EndDate, MaxParticipants, TotalPrice)
            VALUES (:agencyID, :title, :desc, :startDate, :endDate, :maxP, :price)
        ");
        $stmt->execute([
            ':agencyID'  => $data['agencyID'],
            ':title'     => $data['title'],
            ':desc'      => $data['description'] ?? null,
            ':startDate' => $data['startDate'],
            ':endDate'   => $data['endDate'],
            ':maxP'      => $data['maxParticipants'],
            ':price'     => $data['totalPrice']
        ]);
        $packageID = (int)$pdo->lastInsertId();

        
        $links = [
            'destinationIDs'   => ["PackageDestination",   "DestinationID"],
            'accommodationIDs' => ["PackageAccommodation",  "AccommodationID"],
            'flightIDs'        => ["PackageFlight",         "FlightID"],
            'attractionIDs'    => ["PackageAttraction",     "AttractionID"],
            'restaurantIDs'    => ["PackageRestaurant",     "RestaurantID"]
        ];

        foreach ($links as $key => [$table, $col]) {
            if (!empty($data[$key]) && is_array($data[$key])) {
                $ins = $pdo->prepare("INSERT IGNORE INTO $table (PackageID, $col) VALUES (:pkgID, :itemID)");
                foreach ($data[$key] as $itemID) {
                    $ins->execute([':pkgID' => $packageID, ':itemID' => (int)$itemID]);
                }
            }
        }

        $pdo->commit();
        respond(201, ["success" => true, "packageID" => $packageID, "message" => "Package created successfully"]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        respond(500, ["success" => false, "error" => "Could not create package: " . $e->getMessage()]);
    }
}


if ($method === 'PUT' && strpos($uri, '/api/packages/update') !== false) {
    $data = getBody();
    requireFields($data, ['packageID','agencyID']);

   
    $check = $pdo->prepare("SELECT AgencyID FROM Package WHERE PackageID = :id");
    $check->execute([':id' => $data['packageID']]);
    $row = $check->fetch();
    if (!$row) respond(404, ["success" => false, "error" => "Package not found"]);
    if ((int)$row['AgencyID'] !== (int)$data['agencyID']) respond(403, ["success" => false, "error" => "Unauthorized"]);

    $fields = [];
    $params = [':id' => $data['packageID']];
    $allowed = ['title' => 'Title', 'description' => 'Description', 'startDate' => 'StartDate',
                'endDate' => 'EndDate', 'maxParticipants' => 'MaxParticipants', 'totalPrice' => 'TotalPrice'];

    foreach ($allowed as $key => $col) {
        if (isset($data[$key])) {
            $fields[]        = "$col = :$key";
            $params[":$key"] = $data[$key];
        }
    }

    if (empty($fields)) respond(400, ["success" => false, "error" => "No fields to update"]);

    $pdo->prepare("UPDATE Package SET " . implode(", ", $fields) . " WHERE PackageID = :id")->execute($params);
    respond(200, ["success" => true, "message" => "Package updated successfully"]);
}


if ($method === 'DELETE' && strpos($uri, '/api/packages/delete') !== false) {
    $data = getBody();
    requireFields($data, ['packageID','agencyID']);

    $check = $pdo->prepare("SELECT AgencyID FROM Package WHERE PackageID = :id");
    $check->execute([':id' => $data['packageID']]);
    $row = $check->fetch();
    if (!$row) respond(404, ["success" => false, "error" => "Package not found"]);
    if ((int)$row['AgencyID'] !== (int)$data['agencyID']) respond(403, ["success" => false, "error" => "Unauthorized"]);

    $pdo->prepare("DELETE FROM Package WHERE PackageID = :id")->execute([':id' => $data['packageID']]);
    respond(200, ["success" => true, "message" => "Package deleted successfully"]);
}


if ($method === 'POST' && strpos($uri, '/api/bookings/create') !== false) {
    $data = getBody();
    requireFields($data, ['travellerID','packageID','numberOfPeople','totalPrice']);

    
    $cap = $pdo->prepare("
        SELECT p.MaxParticipants, COALESCE(SUM(b.NumberOfPeople), 0) AS booked
        FROM Package p
        LEFT JOIN Booking b ON p.PackageID = b.PackageID AND b.Status != 'Cancelled'
        WHERE p.PackageID = :id
        GROUP BY p.PackageID
    ");
    $cap->execute([':id' => $data['packageID']]);
    $capRow = $cap->fetch();
    if (!$capRow) respond(404, ["success" => false, "error" => "Package not found"]);
    if (($capRow['booked'] + $data['numberOfPeople']) > $capRow['MaxParticipants']) {
        respond(409, ["success" => false, "error" => "Not enough spots available"]);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO Booking (TravellerID, PackageID, NumberOfPeople, Status, TotalPrice)
            VALUES (:tid, :pid, :num, 'Pending', :price)
        ");
        $stmt->execute([
            ':tid'   => $data['travellerID'],
            ':pid'   => $data['packageID'],
            ':num'   => $data['numberOfPeople'],
            ':price' => $data['totalPrice']
        ]);
        respond(201, ["success" => true, "bookingID" => (int)$pdo->lastInsertId(), "status" => "Pending", "message" => "Booking created successfully"]);
    } catch (PDOException $e) {
        respond(500, ["success" => false, "error" => "Booking failed: " . $e->getMessage()]);
    }
}


if ($method === 'GET' && strpos($uri, '/api/bookings/list') !== false) {
    $tid = isset($_GET['travellerID']) ? (int)$_GET['travellerID'] : 0;
    if (!$tid) respond(400, ["success" => false, "error" => "Missing travellerID"]);

    $stmt = $pdo->prepare("
        SELECT b.BookingID, p.Title AS packageTitle, a.Name AS agencyName,
               b.Date, b.NumberOfPeople, b.Status, b.TotalPrice
        FROM Booking b
        JOIN Package p ON b.PackageID = p.PackageID
        JOIN Agency a ON p.AgencyID = a.AgencyID
        WHERE b.TravellerID = :tid
        ORDER BY b.Date DESC
    ");
    $stmt->execute([':tid' => $tid]);
    respond(200, ["success" => true, "data" => $stmt->fetchAll()]);
}


if ($method === 'POST' && strpos($uri, '/api/reviews/package') !== false) {
    $data = getBody();
    requireFields($data, ['travellerID','packageID','rating']);
    if ($data['rating'] < 1 || $data['rating'] > 5) respond(400, ["success" => false, "error" => "Rating must be between 1 and 5"]);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO PackageReview (TravellerID, PackageID, Rating, Comment)
            VALUES (:tid, :pid, :rating, :comment)
        ");
        $stmt->execute([
            ':tid'     => $data['travellerID'],
            ':pid'     => $data['packageID'],
            ':rating'  => $data['rating'],
            ':comment' => $data['comment'] ?? null
        ]);
        respond(201, ["success" => true, "reviewID" => (int)$pdo->lastInsertId(), "message" => "Review submitted successfully"]);
    } catch (PDOException $e) {
        $code = $e->getCode() == 23000 ? 409 : 500;
        $msg  = $e->getCode() == 23000 ? "You have already reviewed this package" : "Review failed: " . $e->getMessage();
        respond($code, ["success" => false, "error" => $msg]);
    }
}


if ($method === 'POST' && strpos($uri, '/api/reviews/agency') !== false) {
    $data = getBody();
    requireFields($data, ['travellerID','agencyID','rating']);
    if ($data['rating'] < 1 || $data['rating'] > 5) respond(400, ["success" => false, "error" => "Rating must be between 1 and 5"]);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO AgencyReview (TravellerID, AgencyID, Rating, Comment)
            VALUES (:tid, :aid, :rating, :comment)
        ");
        $stmt->execute([
            ':tid'     => $data['travellerID'],
            ':aid'     => $data['agencyID'],
            ':rating'  => $data['rating'],
            ':comment' => $data['comment'] ?? null
        ]);
        respond(201, ["success" => true, "reviewID" => (int)$pdo->lastInsertId(), "message" => "Review submitted successfully"]);
    } catch (PDOException $e) {
        $code = $e->getCode() == 23000 ? 409 : 500;
        $msg  = $e->getCode() == 23000 ? "You have already reviewed this agency" : "Review failed: " . $e->getMessage();
        respond($code, ["success" => false, "error" => $msg]);
    }
}


if ($method === 'POST' && strpos($uri, '/api/grouptrips/create') !== false) {
    $data = getBody();
    requireFields($data, ['agencyID','maxCapacity','startDate','endDate']);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO GroupTrip (AgencyID, MaxCapacity, Status, StartDate, EndDate)
            VALUES (:aid, :cap, 'Open', :startDate, :endDate)
        ");
        $stmt->execute([
            ':aid'       => $data['agencyID'],
            ':cap'       => $data['maxCapacity'],
            ':startDate' => $data['startDate'],
            ':endDate'   => $data['endDate']
        ]);
        respond(201, ["success" => true, "groupTripID" => (int)$pdo->lastInsertId(), "status" => "Open", "message" => "Group trip created successfully"]);
    } catch (PDOException $e) {
        respond(500, ["success" => false, "error" => "Could not create group trip: " . $e->getMessage()]);
    }
}


if ($method === 'GET' && strpos($uri, '/api/grouptrips/list') !== false) {
    $status  = $_GET['status'] ?? null;
    $allowed = ['Open','Full','Completed','Cancelled'];

    $where  = "1=1";
    $params = [];

    if ($status && in_array($status, $allowed)) {
        $where            = "g.Status = :status";
        $params[':status'] = $status;
    }

    $stmt = $pdo->prepare("
        SELECT g.GroupTripID, a.Name AS agencyName, g.MaxCapacity,
               COUNT(m.TravellerID) AS currentMembers, g.Status, g.StartDate, g.EndDate
        FROM GroupTrip g
        JOIN Agency a ON g.AgencyID = a.AgencyID
        LEFT JOIN GroupTripMember m ON g.GroupTripID = m.GroupTripID
        WHERE $where
        GROUP BY g.GroupTripID
        ORDER BY g.StartDate ASC
    ");
    $stmt->execute($params);
    respond(200, ["success" => true, "data" => $stmt->fetchAll()]);
}


if ($method === 'POST' && strpos($uri, '/api/grouptrips/join') !== false) {
    $data = getBody();
    requireFields($data, ['groupTripID','travellerID']);

    
    $cap = $pdo->prepare("
        SELECT g.MaxCapacity, COUNT(m.TravellerID) AS members, g.Status
        FROM GroupTrip g
        LEFT JOIN GroupTripMember m ON g.GroupTripID = m.GroupTripID
        WHERE g.GroupTripID = :id
        GROUP BY g.GroupTripID
    ");
    $cap->execute([':id' => $data['groupTripID']]);
    $capRow = $cap->fetch();
    if (!$capRow) respond(404, ["success" => false, "error" => "Group trip not found"]);
    if ($capRow['Status'] !== 'Open') respond(409, ["success" => false, "error" => "Group trip is not open for joining"]);
    if ($capRow['members'] >= $capRow['MaxCapacity']) respond(409, ["success" => false, "error" => "Group trip is full"]);

    try {
        $pdo->prepare("INSERT INTO GroupTripMember (GroupTripID, TravellerID) VALUES (:gid, :tid)")
            ->execute([':gid' => $data['groupTripID'], ':tid' => $data['travellerID']]);

        
        if (($capRow['members'] + 1) >= $capRow['MaxCapacity']) {
            $pdo->prepare("UPDATE GroupTrip SET Status = 'Full' WHERE GroupTripID = :id")
                ->execute([':id' => $data['groupTripID']]);
        }

        respond(200, ["success" => true, "message" => "Successfully joined group trip"]);
    } catch (PDOException $e) {
        $code = $e->getCode() == 23000 ? 409 : 500;
        $msg  = $e->getCode() == 23000 ? "You have already joined this group trip" : "Could not join: " . $e->getMessage();
        respond($code, ["success" => false, "error" => $msg]);
    }
}


if ($method === 'GET' && strpos($uri, '/api/destinations/list') !== false) {
    $stmt = $pdo->query("SELECT DestinationID, Name, City, Region, Country, Description FROM Destination ORDER BY Country, City");
    respond(200, ["success" => true, "data" => $stmt->fetchAll()]);
}


if ($method === 'GET' && strpos($uri, '/api/accommodations/list') !== false) {
    $stmt = $pdo->query("SELECT AccommodationID, Name, Type, StarRating, AveragePricePerNight, City, Country FROM Accommodation ORDER BY StarRating DESC");
    respond(200, ["success" => true, "data" => $stmt->fetchAll()]);
}

if ($method === 'GET' && strpos($uri, '/api/attractions/list') !== false) {
    $stmt = $pdo->query("SELECT AttractionID, Name, Description, EntranceFee, OpeningHours, City, Country FROM Attraction ORDER BY City");
    respond(200, ["success" => true, "data" => $stmt->fetchAll()]);
}


if ($method === 'GET' && strpos($uri, '/api/restaurants/list') !== false) {
    $stmt = $pdo->query("SELECT RestaurantID, Name, Cuisine, PriceRange, City, Country FROM Restaurant ORDER BY City");
    respond(200, ["success" => true, "data" => $stmt->fetchAll()]);
}


if ($method === 'GET' && strpos($uri, '/api/flights/list') !== false) {
    $stmt = $pdo->query("
        SELECT f.FlightID, f.FlightNumber, f.Airline, f.DepartureDateTime, f.ArrivalDateTime, f.BaseCost,
               orig.Code AS originCode, orig.City AS originCity,
               dest.Code AS destCode,   dest.City AS destCity
        FROM Flight f
        JOIN Airport orig ON f.OriginAirportID = orig.AirportID
        JOIN Airport dest ON f.DestinationAirportID = dest.AirportID
        ORDER BY f.DepartureDateTime
    ");
    respond(200, ["success" => true, "data" => $stmt->fetchAll()]);
}


respond(404, ["success" => false, "error" => "Endpoint not found"]);