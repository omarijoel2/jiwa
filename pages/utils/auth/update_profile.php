<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Session;
use App\Classes\Users;

header('Content-Type: application/json');
Session::start();

if (!Session::has('user_id')) {
    echo json_encode(["status" => "error", "message" => "Unauthorized request."]);
    exit;
}

$user_id = Session::get('user_id');
$name = trim($_GET['name'] ?? '');
$email = trim($_GET['email'] ?? '');
$errors = [];

// Validate inputs
if (empty($name)) {
    $errors['name'] = "Full name is required.";
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Valid email is required.";
}
if (!empty($errors)) {
    echo json_encode(["status" => "error", "errors" => $errors]);
    exit;
}

// Update user details
$update = Users::updateProfile($user_id, $name, $email);

if ($update) {
    echo json_encode(["status" => "success", "message" => "Profile updated successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update profile."]);
}
?>
