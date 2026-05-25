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
    // Log the real error internally; never expose DB internals to the client
    write_log('ERROR', 'DB_CONNECTION_FAILED', ['msg' => $e->getMessage()]);
    http_response_code(500);
    echo json_encode(["error" => "A server error occurred. Please try again later."]);
    exit();
}


define('LOGIN_MAX_ATTEMPTS',    5);
define('LOGIN_WINDOW_SECONDS',  600);   
define('LOGIN_LOCKOUT_SECONDS', 10);   


$pdo->exec("
    CREATE TABLE IF NOT EXISTS login_attempts (
        id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ip_address   VARCHAR(45)  NOT NULL,
        attempted_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ip_time (ip_address, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

function get_client_ip(): string {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function check_rate_limit(PDO $pdo): void {
    $ip      = get_client_ip();
    $window  = date('Y-m-d H:i:s', time() - LOGIN_WINDOW_SECONDS);

    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS attempts
        FROM login_attempts
        WHERE ip_address = :ip AND attempted_at >= :window
    ");
    $stmt->execute([':ip' => $ip, ':window' => $window]);
    $row = $stmt->fetch();

    if ((int)$row['attempts'] >= LOGIN_MAX_ATTEMPTS) {
        write_log('WARN', 'RATE_LIMIT_BLOCKED', ['ip' => $ip, 'attempts' => $row['attempts']]);
        http_response_code(429);
        echo json_encode([
            "error" => "Too many failed login attempts. Please wait 15 minutes before trying again."
        ]);
        exit();
    }
}

function record_failed_attempt(PDO $pdo): void {
    $ip = get_client_ip();
    $pdo->prepare("INSERT INTO login_attempts (ip_address) VALUES (:ip)")
        ->execute([':ip' => $ip]);
}

function clear_attempts(PDO $pdo): void {
   
    $ip = get_client_ip();
    $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = :ip")
        ->execute([':ip' => $ip]);
}

$method = $_SERVER['REQUEST_METHOD'];

$request_uri = isset($_GET['route']) 
    ? $_GET['route'] 
    : parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

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
            ':password'    => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            ':phone'       => $data['phone'],
            ':nationality' => $data['nationality'],
            ':dob'         => $data['dateofbirth']
        ]);

        write_log('INFO', 'TRAVELLER_REGISTERED', ['email' => $data['email']]);
        http_response_code(201);
        echo json_encode(["message" => "Traveller registered successfully!"]);

    } catch (PDOException $e) {

        http_response_code(400);

        if ($e->getCode() == 23000) {
            echo json_encode(["error" => "Email is already registered as a traveller."]);
        } else {
            write_log('ERROR', 'REGISTRATION_FAILED', ['msg' => $e->getMessage()]);
            echo json_encode(['error' => 'A server error occurred. Please try again.']);
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
            ':password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            ':phone'    => $data['phone'],
            ':street'   => $data['street'],
            ':city'     => $data['city'],
            ':country'  => $data['country']
        ]);

        write_log('INFO', 'AGENCY_REGISTERED', ['email' => $data['email']]);
        http_response_code(201);
        echo json_encode(["message" => "Agency registered successfully!"]);

    } catch (PDOException $e) {

        http_response_code(400);

        if ($e->getCode() == 23000) {
            echo json_encode(["error" => "Email is already registered as an agency."]);
        } else {
            write_log('ERROR', 'REGISTRATION_FAILED', ['msg' => $e->getMessage()]);
            echo json_encode(['error' => 'A server error occurred. Please try again.']);
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

    check_rate_limit($pdo);

    $email    = $data['email'];
    $password = $data['password'];

    $stmt = $pdo->prepare("
        SELECT TravellerID, FirstName as Name, Email, Password 
        FROM traveller 
        WHERE Email = :email
    ");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['Password'])) {
        clear_attempts($pdo);
        write_log('INFO', 'LOGIN_SUCCESS', ['email' => $email, 'role' => 'traveller']);

        if (password_needs_rehash($user['Password'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $pdo->prepare("UPDATE traveller SET Password = :h WHERE TravellerID = :id")
                ->execute([':h' => $newHash, ':id' => $user['TravellerID']]);
        }

        http_response_code(200);
        echo json_encode([
            "message" => "Login successful!",
            "user" => [
                "id"    => (int)$user['TravellerID'],
                "name"  => $user['Name'],
                "email" => $user['Email'],
                "role"  => "traveller"
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
        clear_attempts($pdo);
        write_log('INFO', 'LOGIN_SUCCESS', ['email' => $email, 'role' => 'agency']);

        if (password_needs_rehash($agency['Password'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $pdo->prepare("UPDATE agency SET Password = :h WHERE AgencyID = :id")
                ->execute([':h' => $newHash, ':id' => $agency['AgencyID']]);
        }

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

  
    record_failed_attempt($pdo);
    write_log('WARN', 'LOGIN_FAILED', ['email' => $email]);

    http_response_code(401);
    echo json_encode(["error" => "Invalid email or password."]);
    exit();
}


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
        write_log('ERROR', 'SEARCH_FAILED', ['msg' => $e->getMessage()]);
        echo json_encode(['error' => 'A server error occurred. Please try again.']);
    }

    exit();
}


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
        write_log('ERROR', 'FAILED_TO_LINK', ['msg' => $e->getMessage()]);
        echo json_encode(['error' => 'A server error occurred. Please try again.']);
    }

    exit();
}


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
        write_log('ERROR', 'FAILED_TO_UNLINK', ['msg' => $e->getMessage()]);
        echo json_encode(['error' => 'A server error occurred. Please try again.']);
    }

    exit();
}



if (strpos($request_uri, '/api/agency/packages/create') === false &&
    strpos($request_uri, '/api/agency/packages/delete') === false &&
    strpos($request_uri, '/api/agency/packages/draft') === false &&
    strpos($request_uri, '/api/agency/packages/update') === false &&
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
                COALESCE(BK.booking_count, 0)          AS BookingCount,
                COALESCE(BK.seats_filled, 0)           AS SeatsFilled
            FROM package p
            LEFT JOIN packageimage pi ON p.PackageID = pi.PackageID
            LEFT JOIN packagedestination pd ON p.PackageID = pd.PackageID
            LEFT JOIN destination d ON pd.DestinationID = d.DestinationID
            LEFT JOIN (
                SELECT PackageID, AVG(Rating) AS avg_rating, COUNT(*) AS review_count
                FROM packagereview GROUP BY PackageID
            ) AS AVG_R ON p.PackageID = AVG_R.PackageID
            LEFT JOIN (
                SELECT PackageID, 
                       COUNT(*) AS booking_count,
                       SUM(NumberOfPeople) AS seats_filled
                FROM booking 
                WHERE Status != 'Cancelled'
                GROUP BY PackageID
            ) AS BK ON p.PackageID = BK.PackageID
            WHERE p.AgencyID = :agency_id AND p.Title != 'Draft Package'
            GROUP BY
                p.PackageID, p.Title, p.Description, p.StartDate, p.EndDate,
                p.MaxParticipants, p.TotalPrice,
                AVG_R.avg_rating, AVG_R.review_count, BK.booking_count, BK.seats_filled
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
        write_log('ERROR', 'FAILED_TO_FETCH_AGENCY_PACKAGES', ['msg' => $e->getMessage()]);
        echo json_encode(['error' => 'A server error occurred. Please try again.']);
    }

    exit();
}


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

        $package_id = (int)$pdo->lastInsertId();

         
        if (!empty($data['image_url'])) {
            $stmtImg = $pdo->prepare("INSERT INTO packageimage (PackageID, ImageURL) VALUES (:pid, :url)");
            $stmtImg->execute([':pid' => $package_id, ':url' => trim($data['image_url'])]);
        }

        http_response_code(201);
        echo json_encode([
            "message"    => "Package created successfully.",
            "package_id" => $package_id,
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        write_log('ERROR', 'FAILED_TO_CREATE_PACKAGE', ['msg' => $e->getMessage()]);
        echo json_encode(['error' => 'A server error occurred. Please try again.']);
    }

    exit();
}


if (strpos($request_uri, '/api/agency/packages/update') !== false) {

    $required = ['package_id', 'agency_id', 'title', 'description', 'start_date', 'end_date', 'max_participants', 'total_price'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            http_response_code(400);
            echo json_encode(["error" => "Field '$field' is required."]);
            exit();
        }
    }

    try {
        // Verify ownership
        $stmt = $pdo->prepare("SELECT AgencyID FROM package WHERE PackageID = :pid");
        $stmt->execute([':pid' => (int)$data['package_id']]);
        $pkg = $stmt->fetch();

        if (!$pkg || (int)$pkg['AgencyID'] !== (int)$data['agency_id']) {
            http_response_code(403);
            echo json_encode(["error" => "Package not found or access denied."]);
            exit();
        }

        // Update package text details
        $stmt = $pdo->prepare("
            UPDATE package 
            SET Title = :title, 
                Description = :description, 
                StartDate = :start_date, 
                EndDate = :end_date, 
                MaxParticipants = :max_participants, 
                TotalPrice = :total_price
            WHERE PackageID = :package_id AND AgencyID = :agency_id
        ");
        $stmt->execute([
            ':title'            => trim($data['title']),
            ':description'      => trim($data['description']),
            ':start_date'       => $data['start_date'],
            ':end_date'         => $data['end_date'],
            ':max_participants' => (int)$data['max_participants'],
            ':total_price'      => (float)$data['total_price'],
            ':package_id'       => (int)$data['package_id'],
            ':agency_id'        => (int)$data['agency_id']
        ]);

       
        $package_id = (int)$data['package_id'];
        $stmtDel = $pdo->prepare("DELETE FROM packageimage WHERE PackageID = :pid");
        $stmtDel->execute([':pid' => $package_id]);

        if (!empty($data['image_url'])) {
            $stmtImg = $pdo->prepare("INSERT INTO packageimage (PackageID, ImageURL) VALUES (:pid, :url)");
            $stmtImg->execute([':pid' => $package_id, ':url' => trim($data['image_url'])]);
        }

        http_response_code(200);
        echo json_encode(["message" => "Package updated successfully."]);

    } catch (PDOException $e) {
        http_response_code(500);
        write_log('ERROR', 'FAILED_TO_UPDATE_PACKAGE', ['msg' => $e->getMessage()]);
        echo json_encode(['error' => 'A server error occurred. Please try again.']);
    }

    exit();
}

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
        write_log('ERROR', 'FAILED_TO_DELETE_PACKAGE', ['msg' => $e->getMessage()]);
        echo json_encode(['error' => 'A server error occurred. Please try again.']);
    }

    exit();
}

// -----------------------------------------------------------------------
// GET TRAVELLER'S BOOKINGS
// -----------------------------------------------------------------------
// CHECK IF TRAVELLER ALREADY BOOKED A PACKAGE
// POST /api/booking/check
// Body: { traveller_id, package_id }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/booking/check') !== false) {

    if (empty($data['traveller_id']) || !is_numeric($data['traveller_id']) ||
        empty($data['package_id'])   || !is_numeric($data['package_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "traveller_id and package_id are required."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            SELECT BookingID FROM booking
            WHERE TravellerID = :tid AND PackageID = :pid
              AND Status NOT IN ('Cancelled')
            LIMIT 1
        ");
        $stmt->execute([
            ':tid' => (int)$data['traveller_id'],
            ':pid' => (int)$data['package_id']
        ]);
        $booking = $stmt->fetch();

        http_response_code(200);
        echo json_encode([
            "already_booked" => (bool)$booking,
            "booking_id"     => $booking ? (int)$booking['BookingID'] : null
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Check failed: " . $e->getMessage()]);
    }

    exit();
}

// -----------------------------------------------------------------------
// CREATE FLIGHT AND LINK TO PACKAGE
// POST /api/flight/create
// Body: { agency_id, package_id, airline, flight_number, departure_datetime,
//         arrival_datetime, base_cost, origin_airport_id, destination_airport_id }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/flight/create') !== false) {

    $required = ['agency_id','package_id','airline','flight_number',
                 'departure_datetime','arrival_datetime','base_cost',
                 'origin_airport_id','destination_airport_id'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(["error" => "Field '$field' is required."]);
            exit();
        }
    }

    $agency_id  = (int)$data['agency_id'];
    $package_id = (int)$data['package_id'];

    try {
        // Verify package belongs to agency
        $stmt = $pdo->prepare("SELECT AgencyID FROM package WHERE PackageID = :pid");
        $stmt->execute([':pid' => $package_id]);
        $pkg  = $stmt->fetch();
        if (!$pkg || (int)$pkg['AgencyID'] !== $agency_id) {
            http_response_code(403);
            echo json_encode(["error" => "Package not found or access denied."]);
            exit();
        }

        // Insert flight
        $stmt = $pdo->prepare("
            INSERT INTO flight
                (FlightNumber, Airline, DepartureDateTime, ArrivalDateTime,
                 BaseCost, OriginAirportID, DestinationAirportID)
            VALUES
                (:fn, :airline, :dep, :arr, :cost, :orig, :dest)
        ");
        $stmt->execute([
            ':fn'     => trim($data['flight_number']),
            ':airline'=> trim($data['airline']),
            ':dep'    => $data['departure_datetime'],
            ':arr'    => $data['arrival_datetime'],
            ':cost'   => (float)$data['base_cost'],
            ':orig'   => (int)$data['origin_airport_id'],
            ':dest'   => (int)$data['destination_airport_id'],
        ]);
        $flight_id = (int)$pdo->lastInsertId();

        // Link to package
        $stmt = $pdo->prepare("INSERT IGNORE INTO packageflight (PackageID, FlightID) VALUES (:pid, :fid)");
        $stmt->execute([':pid' => $package_id, ':fid' => $flight_id]);

        http_response_code(201);
        echo json_encode(["message" => "Flight created and linked.", "flight_id" => $flight_id]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to create flight: " . $e->getMessage()]);
    }

    exit();
}

// -----------------------------------------------------------------------
// SEARCH AIRPORTS
// POST /api/airports/search
// Body: { query }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/airports/search') !== false) {

    $query = isset($data['query']) ? trim($data['query']) : '';
    $like  = '%' . $query . '%';

    try {
        $stmt = $pdo->prepare("
            SELECT AirportID AS id, Code, Name, City, Country
            FROM airport
            WHERE Code LIKE :q1 OR Name LIKE :q2 OR City LIKE :q3
            LIMIT 20
        ");
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
        http_response_code(200);
        echo json_encode(["results" => $stmt->fetchAll()]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Airport search failed: " . $e->getMessage()]);
    }

    exit();
}

// -----------------------------------------------------------------------
// CREATE DRAFT PACKAGE (for resource linking before publish)
// POST /api/agency/packages/draft
// Body: { agency_id }
// -----------------------------------------------------------------------
// -----------------------------------------------------------------------
// CREATE DRAFT PACKAGE (for resource linking before publish)
// POST /api/agency/packages/draft
// Body: { agency_id }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/agency/packages/draft') !== false) {

    if (empty($data['agency_id']) || !is_numeric($data['agency_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "agency_id is required."]);
        exit();
    }

    try {
        // We use DATE_ADD to push the EndDate 1 day into the future to pass the chk_package_dates DB constraint.
        // We also default TotalPrice to 1 in case there is a strict check preventing 0 price.
        $stmt = $pdo->prepare("
            INSERT INTO package (AgencyID, Title, Description, StartDate, EndDate, MaxParticipants, TotalPrice)
            VALUES (:aid, 'Draft Package', 'Draft Description', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 DAY), 1, 1)
        ");
        $stmt->execute([':aid' => (int)$data['agency_id']]);
        http_response_code(201);
        echo json_encode(["package_id" => (int)$pdo->lastInsertId()]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to create draft: " . $e->getMessage()]);
    }

    exit();
}
// -----------------------------------------------------------------------
// CREATE BOOKING
// POST /api/booking/create
// Body: { traveller_id, package_id, number_of_people }
// Requires: traveller session (validated client-side via sessionStorage)
// -----------------------------------------------------------------------
// -----------------------------------------------------------------------
// CREATE BOOKING
// POST /api/booking/create
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/booking/create') !== false) {

    if (
        empty($data['traveller_id']) || !is_numeric($data['traveller_id']) ||
        empty($data['package_id'])   || !is_numeric($data['package_id'])   ||
        empty($data['number_of_people']) || !is_numeric($data['number_of_people'])
    ) {
        http_response_code(400);
        echo json_encode(["error" => "traveller_id, package_id and number_of_people are required."]);
        exit();
    }

    $traveller_id     = (int)$data['traveller_id'];
    $package_id       = (int)$data['package_id'];
    $number_of_people = (int)$data['number_of_people'];

    if ($number_of_people < 1) {
        http_response_code(400);
        echo json_encode(["error" => "number_of_people must be at least 1."]);
        exit();
    }

    try {
        // 1. Fetch the absolute live package details directly from the core row
        $stmtPkg = $pdo->prepare("SELECT TotalPrice, MaxParticipants FROM package WHERE PackageID = :pid");
        $stmtPkg->execute([':pid' => $package_id]);
        $pkg = $stmtPkg->fetch();

        if (!$pkg) {
            http_response_code(404);
            echo json_encode(["error" => "Package not found."]);
            exit();
        }

        // 2. Sum up ALL seats claimed across active bookings for this specific package
        $stmtCount = $pdo->prepare("
            SELECT COALESCE(SUM(NumberOfPeople), 0) AS TotalSeatsClaimed 
            FROM booking 
            WHERE PackageID = :pid AND Status NOT IN ('Cancelled')
        ");
        $stmtCount->execute([':pid' => $package_id]);
        $bookingData = $stmtCount->fetch();
        
        $totalSeatsClaimed = (int)$bookingData['TotalSeatsClaimed'];
        $maxCapacity = (int)$pkg['MaxParticipants'];

        // 3. FIX: If it's a solo package (MaxParticipants was set to 1 or 0 by default), 
        // bypass the aggregate blocking cap so unlimited individual travelers can buy it!
        if ($maxCapacity <= 1) {
            // Solo trip flow: Capacity check is skipped because each departure accommodates infinite separate solo sign-ups
            $spots_left = 999; 
        } else {
            // Group trip flow: Strictly enforce your live edited database capacity limits
            $spots_left = $maxCapacity - $totalSeatsClaimed;
        }

        // Validate incoming seat request against computed remaining capacity
        if ($maxCapacity > 1 && $number_of_people > $spots_left) {
            http_response_code(409);
            echo json_encode([
                "error"      => "Not enough spots available on this expedition.",
                "spots_left" => Math.max(0, $spots_left)
            ]);
            exit();
        }

        // Calculate checkout total price
        $total_price = (float)$pkg['TotalPrice'] * $number_of_people;

        // Insert new active booking row safely
        $stmt = $pdo->prepare("
            INSERT INTO booking (TravellerID, PackageID, NumberOfPeople, Status, TotalPrice)
            VALUES (:tid, :pid, :people, 'Pending', :price)
        ");
        $stmt->execute([
            ':tid'    => $traveller_id,
            ':pid'    => $package_id,
            ':people' => $number_of_people,
            ':price'  => $total_price
        ]);

        $booking_id = (int)$pdo->lastInsertId();

        http_response_code(201);
        echo json_encode([
            "message"     => "Booking created successfully!",
            "booking_id"  => $booking_id,
            "total_price" => $total_price,
            "status"      => "Pending"
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Booking processing failed: " . $e->getMessage()]);
    }

    exit();
}

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
        write_log('ERROR', 'FAILED_TO_FETCH_BOOKINGS', ['msg' => $e->getMessage()]);
        echo json_encode(['error' => 'A server error occurred. Please try again.']);
    }

    exit();
}


if (strpos($request_uri, '/api/packages/details') === false &&
    strpos($request_uri, '/api/packages') !== false) {

    
    $search   = isset($data['search'])   ? trim($data['search'])   : '';
    $price    = isset($data['price'])    ? trim($data['price'])    : '';
    $rating   = isset($data['rating'])   ? (int)$data['rating']    : 0;
    $duration = isset($data['duration']) ? trim($data['duration']) : '';
    $sort     = isset($data['sort'])     ? trim($data['sort'])     : 'recommended';

    
   
    $conditions = ["p.Title != 'Draft Package'"];
    $params     = [];

    if ($search !== '') {
        $conditions[] = "(p.Title LIKE :search OR d.City LIKE :search OR d.Country LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

   
    if ($price !== '') {
        if ($price === '0-5000') {
            $conditions[] = "p.TotalPrice < 5000";
        } elseif ($price === '5000-15000') {
            $conditions[] = "p.TotalPrice BETWEEN 5000 AND 15000";
        } elseif ($price === '15000+') {
            $conditions[] = "p.TotalPrice > 15000";
        }
    }

  
    if ($rating > 0) {
        $conditions[] = "COALESCE(AVG_RATING.avg_rating, 0) >= :rating";
        $params[':rating'] = $rating;
    }

   
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
        write_log('ERROR', 'FAILED_TO_FETCH_PACKAGES', ['msg' => $e->getMessage()]);
        echo json_encode(['error' => 'A server error occurred. Please try again.']);
    }

    exit();
}


if (strpos($request_uri, '/api/packages/details') !== false) {

    if (empty($data['package_id']) || !is_numeric($data['package_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "A valid package_id is required."]);
        exit();
    }

    $package_id = (int)$data['package_id'];

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

        
        $stmt = $pdo->prepare("
            SELECT ImageURL FROM packageimage WHERE PackageID = :pid
        ");
        $stmt->execute([':pid' => $package_id]);
        $images = $stmt->fetchAll(PDO::FETCH_COLUMN);

        
        $stmt = $pdo->prepare("
            SELECT d.DestinationID, d.Name, d.City, d.Region, d.Country, d.Description
            FROM destination d
            JOIN packagedestination pd ON d.DestinationID = pd.DestinationID
            WHERE pd.PackageID = :pid
        ");
        $stmt->execute([':pid' => $package_id]);
        $destinations = $stmt->fetchAll();

        
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

        
        
        $stmt = $pdo->prepare("
            SELECT 
                b.BookingID,
                CONCAT(t.FirstName, ' ', t.Surname) AS PassengerName,
                b.NumberOfPeople AS SeatsClaimed,
                b.Status AS FinancialStatus
            FROM booking b
            JOIN traveller t ON b.TravellerID = t.TravellerID
            WHERE b.PackageID = :pid AND b.Status NOT IN ('Cancelled')
            ORDER BY b.Date DESC
        ");
        $stmt->execute([':pid' => $package_id]);
        $passenger_bookings = $stmt->fetchAll();

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
                "BookingsList"   => $passenger_bookings
            ]
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        write_log('ERROR', 'FAILED_TO_FETCH_PACKAGE_DETAILS', ['msg' => $e->getMessage()]);
        echo json_encode(['error' => 'A server error occurred. Please try again.']);
    }

    exit();
}
// -----------------------------------------------------------------------
// REVIEWS: CREATE
// POST /api/review/create
// Body: { traveller_id, package_id, rating, comment }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/review/create') !== false) {
    if (empty($data['traveller_id']) || empty($data['package_id']) || empty($data['rating'])) {
        http_response_code(400);
        echo json_encode(["error" => "traveller_id, package_id, and rating are required."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO packagereview (PackageID, TravellerID, Rating, Comment, CreatedAt)
            VALUES (:pid, :tid, :rating, :comment, NOW())
        ");
        $stmt->execute([
            ':pid'     => (int)$data['package_id'],
            ':tid'     => (int)$data['traveller_id'],
            ':rating'  => (int)$data['rating'],
            ':comment' => trim($data['comment'] ?? '')
        ]);

        http_response_code(201);
        echo json_encode(["message" => "Review created successfully.", "review_id" => (int)$pdo->lastInsertId()]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to create review: " . $e->getMessage()]);
    }
    exit();
}

// -----------------------------------------------------------------------
// REVIEWS: UPDATE
// POST /api/review/update
// Body: { review_id, traveller_id, rating, comment }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/review/update') !== false) {
    if (empty($data['review_id']) || empty($data['traveller_id']) || empty($data['rating'])) {
        http_response_code(400);
        echo json_encode(["error" => "review_id, traveller_id, and rating are required."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE packagereview 
            SET Rating = :rating, Comment = :comment
            WHERE ReviewID = :rid AND TravellerID = :tid
        ");
        $stmt->execute([
            ':rating'  => (int)$data['rating'],
            ':comment' => trim($data['comment'] ?? ''),
            ':rid'     => (int)$data['review_id'],
            ':tid'     => (int)$data['traveller_id']
        ]);

        http_response_code(200);
        echo json_encode(["message" => "Review updated successfully."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to update review: " . $e->getMessage()]);
    }
    exit();
}

// -----------------------------------------------------------------------
// REVIEWS: DELETE
// POST /api/review/delete
// Body: { review_id, traveller_id }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/review/delete') !== false) {
    if (empty($data['review_id']) || empty($data['traveller_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "review_id and traveller_id are required."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM packagereview WHERE ReviewID = :rid AND TravellerID = :tid");
        $stmt->execute([
            ':rid' => (int)$data['review_id'],
            ':tid' => (int)$data['traveller_id']
        ]);

        http_response_code(200);
        echo json_encode(["message" => "Review deleted successfully."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to delete review: " . $e->getMessage()]);
    }
    exit();
}

// -----------------------------------------------------------------------
// REVIEWS: TRAVELLER DASHBOARD
// POST /api/review/traveller
// Body: { traveller_id }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/review/traveller') !== false) {
    if (empty($data['traveller_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "traveller_id is required."]);
        exit();
    }

    $tid = (int)$data['traveller_id'];

    try {
        // 1. Get Eligible Packages (Booked, ended, not yet reviewed)
        $stmtEligible = $pdo->prepare("
            SELECT p.PackageID, p.Title, p.EndDate
            FROM booking b
            JOIN package p ON b.PackageID = p.PackageID
            WHERE b.TravellerID = :tid 
              AND b.Status != 'Cancelled'
            
              AND NOT EXISTS (
                  SELECT 1 FROM packagereview pr 
                  WHERE pr.PackageID = p.PackageID AND pr.TravellerID = b.TravellerID
              )
            GROUP BY p.PackageID
            ORDER BY p.EndDate DESC
        ");
        $stmtEligible->execute([':tid' => $tid]);
        $eligible = $stmtEligible->fetchAll();

        // 2. Get Review History
        $stmtHistory = $pdo->prepare("
            SELECT pr.ReviewID, pr.PackageID, p.Title, pr.Rating, pr.Comment, pr.CreatedAt
            FROM packagereview pr
            JOIN package p ON pr.PackageID = p.PackageID
            WHERE pr.TravellerID = :tid
            ORDER BY pr.CreatedAt DESC
        ");
        $stmtHistory->execute([':tid' => $tid]);
        $history = $stmtHistory->fetchAll();

        http_response_code(200);
        echo json_encode([
            "eligible_packages" => $eligible,
            "review_history"    => $history
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to fetch traveller reviews: " . $e->getMessage()]);
    }
    exit();
}

// -----------------------------------------------------------------------
// REVIEWS: AGENCY DASHBOARD
// POST /api/review/agency
// Body: { agency_id }
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/review/agency') !== false) {
    if (empty($data['agency_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "agency_id is required."]);
        exit();
    }

    $aid = (int)$data['agency_id'];

    try {
        // 1. Get all reviews for this agency's packages
        $stmtReviews = $pdo->prepare("
            SELECT pr.ReviewID, pr.Rating, pr.Comment, pr.CreatedAt, p.Title as PackageName, t.FirstName, t.Surname
            FROM packagereview pr
            JOIN package p ON pr.PackageID = p.PackageID
            JOIN traveller t ON pr.TravellerID = t.TravellerID
            WHERE p.AgencyID = :aid
            ORDER BY pr.CreatedAt DESC
        ");
        $stmtReviews->execute([':aid' => $aid]);
        $reviews = $stmtReviews->fetchAll();

        // 2. Calculate metrics
        $totalReviews = count($reviews);
        $avgPackageRating = 0;
        
        if ($totalReviews > 0) {
            $totalStars = array_sum(array_column($reviews, 'Rating'));
            $avgPackageRating = round($totalStars / $totalReviews, 1);
        }

        http_response_code(200);
        echo json_encode([
            "metrics" => [
                "total_reviews" => $totalReviews,
                "avg_rating" => $avgPackageRating
            ],
            "reviews" => $reviews
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to fetch agency reviews: " . $e->getMessage()]);
    }
    exit();
}

// -----------------------------------------------------------------------
// CANCEL PENDING BOOKING (TRAVELLER)
// POST /api/booking/cancel
// -----------------------------------------------------------------------
if (strpos($request_uri, '/api/booking/cancel') !== false) {

    if (empty($data['booking_id']) || empty($data['traveller_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "booking_id and traveller_id are required."]);
        exit();
    }

    $booking_id   = (int)$data['booking_id'];
    $traveller_id = (int)$data['traveller_id'];

    try {
        // Verify ownership and confirm the booking is actually still Pending
        $stmt = $pdo->prepare("SELECT Status FROM booking WHERE BookingID = :bid AND TravellerID = :tid");
        $stmt->execute([':bid' => $booking_id, ':tid' => $traveller_id]);
        $booking = $stmt->fetch();

        if (!$booking) {
            http_response_code(404);
            echo json_encode(["error" => "Booking record not found."]);
            exit();
        }

        if ($booking['Status'] !== 'Pending') {
            http_response_code(400);
            echo json_encode(["error" => "Only pending bookings can be cancelled."]);
            exit();
        }

        // Execute the cancellation status change
        $updateStmt = $pdo->prepare("UPDATE booking SET Status = 'Cancelled' WHERE BookingID = :bid");
        $updateStmt->execute([':bid' => $booking_id]);

        http_response_code(200);
        echo json_encode(["message" => "Booking cancelled successfully."]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Cancellation failed: " . $e->getMessage()]);
    }

    exit();
}

http_response_code(404);
echo json_encode(["error" => "Endpoint not found."]);
?>