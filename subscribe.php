<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "final_asp");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$userId = intval($data["user_id"]);
$email = $conn->real_escape_string($data["email"]);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email."]);
    exit;
}


$checkUser = $conn->prepare("SELECT id FROM users WHERE id = ?");
$checkUser->bind_param("i", $userId);
$checkUser->execute();
$userResult = $checkUser->get_result();

if ($userResult->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "User ID not found in users table."]);
    exit;
}

$sql = "INSERT INTO subscriptions (user_id, email) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $userId, $email);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "SQL Error: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
