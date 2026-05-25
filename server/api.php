<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Authorization");
require_once 'config.php';

$db_host = DB_HOST;
$db_user = DB_USER;
$db_pass = DB_PASS;
$db_name = DB_NAME;

define('LOG_FILE', __DIR__ . '/../../logs/tripistry_audit.log');
 
function write_log(string $level, string $event, array $context = []): void {
    $dir = dirname(LOG_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }
    $ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $uri     = $_SERVER['REQUEST_URI']  ?? '';
    $ts      = date('Y-m-d H:i:s');
    $ctx     = empty($context) ? '' : ' ' . json_encode($context);
    $line    = "[$ts] [$level] [$ip] $event$ctx URI=$uri" . PHP_EOL;
    file_put_contents(LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); 
    exit;
}

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    write_log('ERROR', 'DB_CONNECTION_FAILED', ['msg' => $e->getMessage()]);
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit();
}
define('LOGIN_MAX_ATTEMPTS',    5);
define('LOGIN_WINDOW_SECONDS',  600);   // 10-minute sliding window
define('LOGIN_LOCKOUT_SECONDS', 900);

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed. Use POST."]);
    exit();
}

$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input, true);

if (strpos($request_uri, '/api/register/traveller') !== false) {

    if (
        empty($data['name']) ||
        empty($data['surname']) ||
        empty($data['email']) ||
        empty($data['password']) ||
        empty($data['phone']) ||
        empty($data['nationality']) ||
        empty($data['dateofbirth'])
    ) {
        http_response_code(400);
        echo json_encode(["error" => "All traveller fields are required."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO traveller 
            (FirstName, Surname, Email, Password, Phone, Nationality, DateOfBirth, JoinDate) 
            VALUES 
            (:name, :surname, :email, :password, :phone, :nationality, :dob, CURDATE())
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

        http_response_code(201);
        echo json_encode(["message" => "Traveller registered successfully!"]);

    } catch (PDOException $e) {

        http_response_code(400);

        if ($e->getCode() == 23000) {
            echo json_encode(["error" => "Email is already registered as a traveller."]);
        } else {
            echo json_encode(["error" => "Registration failed: " . $e->getMessage()]);
        }
    }

    exit();
}

if (strpos($request_uri, '/api/register/agency') !== false) {

    if (
        empty($data['name']) ||
        empty($data['email']) ||
        empty($data['password']) ||
        empty($data['phone']) ||
        empty($data['street']) ||
        empty($data['city']) ||
        empty($data['country'])
    ) {
        http_response_code(400);
        echo json_encode(["error" => "All agency fields are required."]);
        exit();
    }

    try {

        $stmt = $pdo->prepare("
            INSERT INTO agency 
            (Name, Email, Password, Phone, JoinDate, Street, City, Country) 
            VALUES 
            (:name, :email, :password, :phone, CURDATE(), :street, :city, :country)
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

        http_response_code(201);
        echo json_encode(["message" => "Agency registered successfully!"]);

    } catch (PDOException $e) {

        http_response_code(400);

        if ($e->getCode() == 23000) {
            echo json_encode(["error" => "Email is already registered as an agency."]);
        } else {
            echo json_encode(["error" => "Registration failed: " . $e->getMessage()]);
        }
    }

    exit();
}

if (strpos($request_uri, '/api/login') !== false) {

    if (empty($data['email']) || empty($data['password'])) {
        http_response_code(400);
        echo json_encode(["error" => "Email and password are required."]);
        exit();
    }

    $email = $data['email'];
    $password = $data['password'];

    $stmt = $pdo->prepare("
        SELECT TravellerID, FirstName as Name, Email, Password 
        FROM traveller 
        WHERE Email = :email
    ");

    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['Password'])) {

        http_response_code(200);

        echo json_encode([
            "message" => "Login successful!",
            "user" => [
                "id"   => (int)$user['TravellerID'],
                "name" => $user['Name'],
                "email" => $user['Email'],
                "role" => "traveller"
            ]
        ]);

        exit();
    }

    $stmt = $pdo->prepare("
        SELECT AgencyID, Name, Email, Password 
        FROM agency 
        WHERE Email = :email
    ");

    $stmt->execute([':email' => $email]);
    $agency = $stmt->fetch();

    if ($agency && password_verify($password, $agency['Password'])) {

        http_response_code(200);

        echo json_encode([
            "message" => "Login successful!",
            "user" => [
                "id"    => (int)$agency['AgencyID'],
                "name"  => $agency['Name'],
                "email" => $agency['Email'],
                "role"  => "agency"
            ]
        ]);

        exit();
    }

    http_response_code(401);
    echo json_encode(["error" => "Invalid email or password."]);
    exit();
}

// -----------------------------------------------------------------------
// SEARCH RESOURCES (flights, accommodations, destinations, restaurants)
// POST /api/resources/search
// Body: { type: 'flight'|'accommodation'|'destination'|'restaurant', query? }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/resources/search') !== false) {

    $type  = isset($data['type'])  ? trim($data['type'])  : '';
    $query = isset($data['query']) ? trim($data['query']) : '';

    $allowed_types = ['flight', 'accommodation', 'destination', 'restaurant'];
    if (!in_array($type, $allowed_types)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid resource type."]);
        exit();
    }

    $like = '%' . $query . '%';

    try {
        switch ($type) {
            case 'flight':
                $stmt = $pdo->prepare("
                    SELECT
                        f.FlightID AS id,
                        f.Airline,
                        f.FlightNumber,
                        f.DepartureDateTime,
                        f.ArrivalDateTime,
                        f.BaseCost,
                        o.Code AS OriginCode,
                        o.City AS OriginCity,
                        d.Code AS DestCode,
                        d.City AS DestCity
                    FROM flight f
                    JOIN airport o ON f.OriginAirportID      = o.AirportID
                    JOIN airport d ON f.DestinationAirportID = d.AirportID
                    WHERE f.Airline LIKE :q1 OR f.FlightNumber LIKE :q2
                       OR o.Code LIKE :q3    OR d.Code LIKE :q4
                    LIMIT 20
                ");
                $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like]);
                break;

            case 'accommodation':
                $stmt = $pdo->prepare("
                    SELECT AccommodationID AS id, Name, Type, StarRating, AveragePricePerNight, City, Country
                    FROM accommodation
                    WHERE Name LIKE :q1 OR City LIKE :q2
                    LIMIT 20
                ");
                $stmt->execute([':q1' => $like, ':q2' => $like]);
                break;

            case 'destination':
                $stmt = $pdo->prepare("
                    SELECT DestinationID AS id, Name, City, Region, Country, Description
                    FROM destination
                    WHERE Name LIKE :q1 OR City LIKE :q2 OR Country LIKE :q3
                    LIMIT 20
                ");
                $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
                break;

            case 'restaurant':
                $stmt = $pdo->prepare("
                    SELECT RestaurantID AS id, Name, Cuisine, PriceRange, City, Country
                    FROM restaurant
                    WHERE Name LIKE :q1 OR City LIKE :q2 OR Cuisine LIKE :q3
                    LIMIT 20
                ");
                $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
                break;
        }

        http_response_code(200);
        echo json_encode(["results" => $stmt->fetchAll()]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Search failed: " . $e->getMessage()]);
    }

    exit();
}

// -----------------------------------------------------------------------
// LINK RESOURCE TO PACKAGE
// POST /api/resources/link
// Body: { agency_id, package_id, type, resource_id }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/resources/link') !== false &&
    strpos($request_uri, '/api/resources/unlink') === false) {

    foreach (['agency_id', 'package_id', 'type', 'resource_id'] as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(["error" => "Field '$field' is required."]);
            exit();
        }
    }

    $agency_id   = (int)$data['agency_id'];
    $package_id  = (int)$data['package_id'];
    $type        = trim($data['type']);
    $resource_id = (int)$data['resource_id'];

    $table_map = [
        'flight'        => ['packageflight',       'FlightID'],
        'accommodation' => ['packageaccommodation', 'AccommodationID'],
        'destination'   => ['packagedestination',   'DestinationID'],
        'restaurant'    => ['packagerestaurant',    'RestaurantID'],
    ];

    if (!isset($table_map[$type])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid resource type."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT AgencyID FROM package WHERE PackageID = :pid");
        $stmt->execute([':pid' => $package_id]);
        $pkg  = $stmt->fetch();

        if (!$pkg || (int)$pkg['AgencyID'] !== $agency_id) {
            http_response_code(403);
            echo json_encode(["error" => "Package not found or access denied."]);
            exit();
        }

        [$table, $col] = $table_map[$type];
        $stmt = $pdo->prepare("INSERT IGNORE INTO `$table` (PackageID, `$col`) VALUES (:pid, :rid)");
        $stmt->execute([':pid' => $package_id, ':rid' => $resource_id]);

        http_response_code(200);
        echo json_encode(["message" => ucfirst($type) . " linked successfully."]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to link: " . $e->getMessage()]);
    }

    exit();
}

// -----------------------------------------------------------------------
// UNLINK RESOURCE FROM PACKAGE
// POST /api/resources/unlink
// Body: { agency_id, package_id, type, resource_id }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/resources/unlink') !== false) {

    foreach (['agency_id', 'package_id', 'type', 'resource_id'] as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(["error" => "Field '$field' is required."]);
            exit();
        }
    }

    $agency_id   = (int)$data['agency_id'];
    $package_id  = (int)$data['package_id'];
    $type        = trim($data['type']);
    $resource_id = (int)$data['resource_id'];

    $table_map = [
        'flight'        => ['packageflight',       'FlightID'],
        'accommodation' => ['packageaccommodation', 'AccommodationID'],
        'destination'   => ['packagedestination',   'DestinationID'],
        'restaurant'    => ['packagerestaurant',    'RestaurantID'],
    ];

    if (!isset($table_map[$type])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid resource type."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT AgencyID FROM package WHERE PackageID = :pid");
        $stmt->execute([':pid' => $package_id]);
        $pkg  = $stmt->fetch();

        if (!$pkg || (int)$pkg['AgencyID'] !== $agency_id) {
            http_response_code(403);
            echo json_encode(["error" => "Package not found or access denied."]);
            exit();
        }

        [$table, $col] = $table_map[$type];
        $stmt = $pdo->prepare("DELETE FROM `$table` WHERE PackageID = :pid AND `$col` = :rid");
        $stmt->execute([':pid' => $package_id, ':rid' => $resource_id]);

        http_response_code(200);
        echo json_encode(["message" => ucfirst($type) . " unlinked successfully."]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to unlink: " . $e->getMessage()]);
    }

    exit();
}

// -----------------------------------------------------------------------
// GET AGENCY'S OWN PACKAGES
// POST /api/agency/packages
// Body: { agency_id }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/agency/packages/create') === false &&
    strpos($request_uri, '/api/agency/packages/delete') === false &&
    strpos($request_uri, '/api/agency/packages') !== false) {

    if (empty($data['agency_id']) || !is_numeric($data['agency_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "A valid agency_id is required."]);
        exit();
    }

    $agency_id = (int)$data['agency_id'];

    try {
        $stmt = $pdo->prepare("
            SELECT
                p.PackageID,
                p.Title,
                p.Description,
                p.StartDate,
                p.EndDate,
                DATEDIFF(p.EndDate, p.StartDate) AS Nights,
                p.MaxParticipants,
                p.TotalPrice,
                MIN(pi.ImageURL)                       AS ThumbnailURL,
                MIN(d.City)                            AS City,
                MIN(d.Country)                         AS Country,
                ROUND(COALESCE(AVG_R.avg_rating, 0), 1) AS AvgRating,
                COALESCE(AVG_R.review_count, 0)        AS ReviewCount,
                COALESCE(BK.booking_count, 0)          AS BookingCount
            FROM package p
            LEFT JOIN packageimage pi ON p.PackageID = pi.PackageID
            LEFT JOIN packagedestination pd ON p.PackageID = pd.PackageID
            LEFT JOIN destination d ON pd.DestinationID = d.DestinationID
            LEFT JOIN (
                SELECT PackageID, AVG(Rating) AS avg_rating, COUNT(*) AS review_count
                FROM packagereview GROUP BY PackageID
            ) AS AVG_R ON p.PackageID = AVG_R.PackageID
            LEFT JOIN (
                SELECT PackageID, COUNT(*) AS booking_count
                FROM booking GROUP BY PackageID
            ) AS BK ON p.PackageID = BK.PackageID
            WHERE p.AgencyID = :agency_id
            GROUP BY
                p.PackageID, p.Title, p.Description, p.StartDate, p.EndDate,
                p.MaxParticipants, p.TotalPrice,
                AVG_R.avg_rating, AVG_R.review_count, BK.booking_count
            ORDER BY p.StartDate ASC
        ");
        $stmt->execute([':agency_id' => $agency_id]);
        $packages = $stmt->fetchAll();

        $total_packages = count($packages);
        $ratings        = array_filter(array_column($packages, 'AvgRating'));
        $avg_rating     = count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 1) : 0;

        http_response_code(200);
        echo json_encode([
            "packages"       => $packages,
            "total_packages" => $total_packages,
            "avg_rating"     => $avg_rating,
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to fetch agency packages: " . $e->getMessage()]);
    }

    exit();
}

// -----------------------------------------------------------------------
// CREATE PACKAGE
// POST /api/agency/packages/create
// Body: { agency_id, title, description, start_date, end_date, max_participants, total_price }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/agency/packages/create') !== false) {

    $required = ['agency_id', 'title', 'description', 'start_date', 'end_date', 'max_participants', 'total_price'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            http_response_code(400);
            echo json_encode(["error" => "Field '$field' is required."]);
            exit();
        }
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO package (AgencyID, Title, Description, StartDate, EndDate, MaxParticipants, TotalPrice)
            VALUES (:agency_id, :title, :description, :start_date, :end_date, :max_participants, :total_price)
        ");
        $stmt->execute([
            ':agency_id'        => (int)$data['agency_id'],
            ':title'            => trim($data['title']),
            ':description'      => trim($data['description']),
            ':start_date'       => $data['start_date'],
            ':end_date'         => $data['end_date'],
            ':max_participants' => (int)$data['max_participants'],
            ':total_price'      => (float)$data['total_price'],
        ]);

        http_response_code(201);
        echo json_encode([
            "message"    => "Package created successfully.",
            "package_id" => (int)$pdo->lastInsertId(),
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to create package: " . $e->getMessage()]);
    }

    exit();
}

// -----------------------------------------------------------------------
// DELETE PACKAGE
// POST /api/agency/packages/delete
// Body: { agency_id, package_id }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/agency/packages/delete') !== false) {

    if (empty($data['agency_id']) || empty($data['package_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "agency_id and package_id are required."]);
        exit();
    }

    $agency_id  = (int)$data['agency_id'];
    $package_id = (int)$data['package_id'];

    try {
        $stmt = $pdo->prepare("SELECT AgencyID FROM package WHERE PackageID = :pid");
        $stmt->execute([':pid' => $package_id]);
        $pkg = $stmt->fetch();

        if (!$pkg) {
            http_response_code(404);
            echo json_encode(["error" => "Package not found."]);
            exit();
        }

        if ((int)$pkg['AgencyID'] !== $agency_id) {
            http_response_code(403);
            echo json_encode(["error" => "You do not have permission to delete this package."]);
            exit();
        }

        $stmt = $pdo->prepare("DELETE FROM package WHERE PackageID = :pid");
        $stmt->execute([':pid' => $package_id]);

        http_response_code(200);
        echo json_encode(["message" => "Package deleted successfully."]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to delete package: " . $e->getMessage()]);
    }

    exit();
}

// -----------------------------------------------------------------------
// GET TRAVELLER'S BOOKINGS
// POST /api/traveller/bookings
// Body: { traveller_id }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/traveller/bookings') !== false) {

    if (empty($data['traveller_id']) || !is_numeric($data['traveller_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "A valid traveller_id is required."]);
        exit();
    }

    $traveller_id = (int)$data['traveller_id'];

    try {
        $stmt = $pdo->prepare("
            SELECT
                b.BookingID,
                b.Date           AS BookingDate,
                b.NumberOfPeople,
                b.Status,
                b.TotalPrice     AS BookingPrice,
                p.PackageID,
                p.Title,
                p.Description,
                p.StartDate,
                p.EndDate,
                DATEDIFF(p.EndDate, p.StartDate) AS Nights,
                a.Name           AS AgencyName,
                MIN(pi.ImageURL) AS ThumbnailURL,
                MIN(d.City)      AS City,
                MIN(d.Country)   AS Country
            FROM booking b
            JOIN package p  ON b.PackageID  = p.PackageID
            JOIN agency a   ON p.AgencyID   = a.AgencyID
            LEFT JOIN packageimage pi       ON p.PackageID = pi.PackageID
            LEFT JOIN packagedestination pd ON p.PackageID = pd.PackageID
            LEFT JOIN destination d         ON pd.DestinationID = d.DestinationID
            WHERE b.TravellerID = :traveller_id
            GROUP BY
                b.BookingID, b.Date, b.NumberOfPeople, b.Status, b.TotalPrice,
                p.PackageID, p.Title, p.Description, p.StartDate, p.EndDate, a.Name
            ORDER BY b.Date DESC
        ");
        $stmt->execute([':traveller_id' => $traveller_id]);
        $bookings = $stmt->fetchAll();

        http_response_code(200);
        echo json_encode(["bookings" => $bookings]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to fetch bookings: " . $e->getMessage()]);
    }

    exit();
}

// -----------------------------------------------------------------------
// GET PACKAGES (browse / filter / sort)
// POST /api/packages
// Body: { search?, price?, rating?, duration?, sort? }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/packages/details') === false &&
    strpos($request_uri, '/api/packages') !== false) {

    // --- Sanitise / read filter params ---
    $search   = isset($data['search'])   ? trim($data['search'])   : '';
    $price    = isset($data['price'])    ? trim($data['price'])    : '';
    $rating   = isset($data['rating'])   ? (int)$data['rating']    : 0;
    $duration = isset($data['duration']) ? trim($data['duration']) : '';
    $sort     = isset($data['sort'])     ? trim($data['sort'])     : 'recommended';

    // --- Build WHERE clauses using a whitelist approach ---
    $conditions = [];
    $params     = [];

    if ($search !== '') {
        $conditions[] = "(p.Title LIKE :search OR d.City LIKE :search OR d.Country LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    // Price filter: "0-5000", "5000-15000", "15000+"
    if ($price !== '') {
        if ($price === '0-5000') {
            $conditions[] = "p.TotalPrice < 5000";
        } elseif ($price === '5000-15000') {
            $conditions[] = "p.TotalPrice BETWEEN 5000 AND 15000";
        } elseif ($price === '15000+') {
            $conditions[] = "p.TotalPrice > 15000";
        }
    }

    // Rating filter: minimum average package review rating
    if ($rating > 0) {
        $conditions[] = "COALESCE(AVG_RATING.avg_rating, 0) >= :rating";
        $params[':rating'] = $rating;
    }

    // Duration filter in nights: "1-3", "4-7", "8-14", "15+"
    if ($duration !== '') {
        if ($duration === '1-3') {
            $conditions[] = "DATEDIFF(p.EndDate, p.StartDate) BETWEEN 1 AND 3";
        } elseif ($duration === '4-7') {
            $conditions[] = "DATEDIFF(p.EndDate, p.StartDate) BETWEEN 4 AND 7";
        } elseif ($duration === '8-14') {
            $conditions[] = "DATEDIFF(p.EndDate, p.StartDate) BETWEEN 8 AND 14";
        } elseif ($duration === '15+') {
            $conditions[] = "DATEDIFF(p.EndDate, p.StartDate) >= 15";
        }
    }

    $where = count($conditions) > 0
        ? 'WHERE ' . implode(' AND ', $conditions)
        : '';

    // --- Sort order whitelist ---
    $order_map = [
        'recommended' => 'COALESCE(AVG_RATING.avg_rating, 0) DESC',
        'price-low'   => 'p.TotalPrice ASC',
        'price-high'  => 'p.TotalPrice DESC',
    ];
    $order_by = isset($order_map[$sort]) ? $order_map[$sort] : $order_map['recommended'];

    $sql = "
        SELECT
            p.PackageID,
            a.Name                              AS AgencyName,
            p.Title,
            p.Description,
            p.StartDate,
            p.EndDate,
            DATEDIFF(p.EndDate, p.StartDate)    AS Nights,
            p.MaxParticipants,
            p.TotalPrice,
            MIN(pi.ImageURL)                    AS ThumbnailURL,
            MIN(d.City)                         AS City,
            MIN(d.Country)                      AS Country,
            ROUND(COALESCE(AVG_RATING.avg_rating, 0), 1) AS AvgRating,
            COALESCE(AVG_RATING.review_count, 0) AS ReviewCount
        FROM package p
        JOIN agency a ON p.AgencyID = a.AgencyID
        LEFT JOIN packageimage pi ON p.PackageID = pi.PackageID
        LEFT JOIN packagedestination pd ON p.PackageID = pd.PackageID
        LEFT JOIN destination d ON pd.DestinationID = d.DestinationID
        LEFT JOIN (
            SELECT PackageID,
                   AVG(Rating)   AS avg_rating,
                   COUNT(*)      AS review_count
            FROM packagereview
            GROUP BY PackageID
        ) AS AVG_RATING ON p.PackageID = AVG_RATING.PackageID
        $where
        GROUP BY
            p.PackageID,
            a.Name,
            p.Title,
            p.Description,
            p.StartDate,
            p.EndDate,
            p.MaxParticipants,
            p.TotalPrice,
            AVG_RATING.avg_rating,
            AVG_RATING.review_count
        ORDER BY $order_by
    ";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $packages = $stmt->fetchAll();

        http_response_code(200);
        echo json_encode(["packages" => $packages]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to fetch packages: " . $e->getMessage()]);
    }

    exit();
}

// -----------------------------------------------------------------------
// GET SINGLE PACKAGE DETAILS
// POST /api/packages/details
// Body: { package_id }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/packages/details') !== false) {

    if (empty($data['package_id']) || !is_numeric($data['package_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "A valid package_id is required."]);
        exit();
    }

    $package_id = (int)$data['package_id'];

    try {
        // --- Core package + agency ---
        $stmt = $pdo->prepare("
            SELECT
                p.PackageID,
                p.Title,
                p.Description,
                p.StartDate,
                p.EndDate,
                DATEDIFF(p.EndDate, p.StartDate) AS Nights,
                p.MaxParticipants,
                p.TotalPrice,
                a.AgencyID,
                a.Name    AS AgencyName,
                a.Email   AS AgencyEmail,
                a.Phone   AS AgencyPhone,
                a.City    AS AgencyCity,
                a.Country AS AgencyCountry
            FROM package p
            JOIN agency a ON p.AgencyID = a.AgencyID
            WHERE p.PackageID = :pid
        ");
        $stmt->execute([':pid' => $package_id]);
        $package = $stmt->fetch();

        if (!$package) {
            http_response_code(404);
            echo json_encode(["error" => "Package not found."]);
            exit();
        }

        // --- Images ---
        $stmt = $pdo->prepare("
            SELECT ImageURL FROM packageimage WHERE PackageID = :pid
        ");
        $stmt->execute([':pid' => $package_id]);
        $images = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // --- Destinations ---
        $stmt = $pdo->prepare("
            SELECT d.DestinationID, d.Name, d.City, d.Region, d.Country, d.Description
            FROM destination d
            JOIN packagedestination pd ON d.DestinationID = pd.DestinationID
            WHERE pd.PackageID = :pid
        ");
        $stmt->execute([':pid' => $package_id]);
        $destinations = $stmt->fetchAll();

        // --- Flights (with airport codes) ---
        $stmt = $pdo->prepare("
            SELECT
                f.FlightID,
                f.FlightNumber,
                f.Airline,
                f.DepartureDateTime,
                f.ArrivalDateTime,
                f.BaseCost,
                origin.Code        AS OriginCode,
                origin.City        AS OriginCity,
                dest_ap.Code       AS DestinationCode,
                dest_ap.City       AS DestinationCity
            FROM flight f
            JOIN packageflight pf ON f.FlightID = pf.FlightID
            JOIN airport origin   ON f.OriginAirportID      = origin.AirportID
            JOIN airport dest_ap  ON f.DestinationAirportID = dest_ap.AirportID
            WHERE pf.PackageID = :pid
            ORDER BY f.DepartureDateTime ASC
        ");
        $stmt->execute([':pid' => $package_id]);
        $flights = $stmt->fetchAll();

        // --- Accommodations ---
        $stmt = $pdo->prepare("
            SELECT
                ac.AccommodationID,
                ac.Name,
                ac.Type,
                ac.StarRating,
                ac.AveragePricePerNight,
                ac.Description,
                ac.Street,
                ac.City,
                ac.Country,
                MIN(ai.ImageURL) AS ImageURL
            FROM accommodation ac
            JOIN packageaccommodation pa ON ac.AccommodationID = pa.AccommodationID
            LEFT JOIN accommodationimage ai ON ac.AccommodationID = ai.AccommodationID
            WHERE pa.PackageID = :pid
            GROUP BY
                ac.AccommodationID, ac.Name, ac.Type, ac.StarRating,
                ac.AveragePricePerNight, ac.Description, ac.Street, ac.City, ac.Country
        ");
        $stmt->execute([':pid' => $package_id]);
        $accommodations = $stmt->fetchAll();

        // --- Attractions ---
        $stmt = $pdo->prepare("
            SELECT
                at.AttractionID,
                at.Name,
                at.Description,
                at.EntranceFee,
                at.OpeningHours,
                at.Street,
                at.City,
                at.Country
            FROM attraction at
            JOIN packageattraction pa ON at.AttractionID = pa.AttractionID
            WHERE pa.PackageID = :pid
        ");
        $stmt->execute([':pid' => $package_id]);
        $attractions = $stmt->fetchAll();

        // --- Restaurants ---
        $stmt = $pdo->prepare("
            SELECT
                r.RestaurantID,
                r.Name,
                r.Cuisine,
                r.PriceRange,
                r.ContactNumber,
                r.Street,
                r.City,
                r.Country
            FROM restaurant r
            JOIN packagerestaurant pr ON r.RestaurantID = pr.RestaurantID
            WHERE pr.PackageID = :pid
        ");
        $stmt->execute([':pid' => $package_id]);
        $restaurants = $stmt->fetchAll();

        // --- Reviews (with traveller first name) ---
        $stmt = $pdo->prepare("
            SELECT
                rv.ReviewID,
                t.FirstName AS TravellerName,
                rv.Rating,
                rv.Comment,
                rv.CreatedAt
            FROM packagereview rv
            JOIN traveller t ON rv.TravellerID = t.TravellerID
            WHERE rv.PackageID = :pid
            ORDER BY rv.CreatedAt DESC
        ");
        $stmt->execute([':pid' => $package_id]);
        $reviews = $stmt->fetchAll();

        // --- Average rating ---
        $avg_rating    = 0;
        $review_count  = count($reviews);
        if ($review_count > 0) {
            $avg_rating = round(
                array_sum(array_column($reviews, 'Rating')) / $review_count,
                1
            );
        }

        http_response_code(200);
        echo json_encode([
            "package" => [
                "PackageID"      => (int)$package['PackageID'],
                "Title"          => $package['Title'],
                "Description"    => $package['Description'],
                "StartDate"      => $package['StartDate'],
                "EndDate"        => $package['EndDate'],
                "Nights"         => (int)$package['Nights'],
                "MaxParticipants"=> (int)$package['MaxParticipants'],
                "TotalPrice"     => (float)$package['TotalPrice'],
                "AvgRating"      => $avg_rating,
                "ReviewCount"    => $review_count,
                "Images"         => $images,
                "Agency"         => [
                    "AgencyID"   => (int)$package['AgencyID'],
                    "Name"       => $package['AgencyName'],
                    "Email"      => $package['AgencyEmail'],
                    "Phone"      => $package['AgencyPhone'],
                    "City"       => $package['AgencyCity'],
                    "Country"    => $package['AgencyCountry'],
                ],
                "Destinations"   => $destinations,
                "Flights"        => $flights,
                "Accommodations" => $accommodations,
                "Attractions"    => $attractions,
                "Restaurants"    => $restaurants,
                "Reviews"        => $reviews,
            ]
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to fetch package details: " . $e->getMessage()]);
    }

    exit();
}

http_response_code(404);
echo json_encode(["error" => "Endpoint not found."]);
?>