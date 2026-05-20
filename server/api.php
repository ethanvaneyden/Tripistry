<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept, Authorization");

$db_host = "127.0.0.1";
$db_user = "root";
$db_pass = "Silver4monsters";
$db_name = "tripistry";

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
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit();
}

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
        SELECT FirstName as Name, Email, Password 
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
                "name" => $user['Name'],
                "email" => $user['Email'],
                "role" => "traveller"
            ]
        ]);

        exit();
    }

    $stmt = $pdo->prepare("
        SELECT Name, Email, Password 
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
                "name" => $agency['Name'],
                "email" => $agency['Email'],
                "role" => "agency"
            ]
        ]);

        exit();
    }

    http_response_code(401);
    echo json_encode(["error" => "Invalid email or password."]);
    exit();
}

http_response_code(404);
echo json_encode(["error" => "Endpoint not found."]);
?>