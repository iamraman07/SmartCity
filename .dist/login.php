<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "smart_city"; // Change to your database name

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database Connection Failed: " . $conn->connect_error]));
}

// Get JSON data from request
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($data['email'], $data['password'])) {
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    $email = $data['email'];
    $password = $data['password'];

    // Check if user exists
    $sql = "SELECT id, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(["error" => "SQL Prepare Error: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            echo json_encode(["success" => "Login successful!", "user_id" => $user_id]);
        } else {
            echo json_encode(["error" => "Invalid password!"]);
        }
    } else {
        echo json_encode(["error" => "User does not exist!"]);
    }

    $stmt->close();
}

$conn->close();
?>
