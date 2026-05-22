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
$current_password = trim($_GET['current_password'] ?? '');
$new_password = trim($_GET['new_password'] ?? '');
$confirm_password = trim($_GET['confirm_password'] ?? '');
$errors = [];

// Validate input
if (empty($current_password)) {
    $errors['current_password'] = "Current password is required.";
}
if (empty($new_password) || strlen($new_password) < 6) {
    $errors['new_password'] = "New password must be at least 6 characters.";
}
if ($new_password !== $confirm_password) {
    $errors['confirm_password'] = "Passwords do not match.";
}
if (!empty($errors)) {
    echo json_encode(["status" => "error", "errors" => $errors]);
    exit;
}

// Verify current password
if (!Users::verifyPassword($user_id, $current_password)) {
    echo json_encode(["status" => "error", "message" => "Current password is incorrect."]);
    exit;
}

// Update new password
$update = Users::updatePassword($user_id, $new_password);

if ($update) {
    echo json_encode(["status" => "success", "message" => "Password updated successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update password."]);
}
?>
