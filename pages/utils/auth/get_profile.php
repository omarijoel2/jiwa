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

// Fetch user details
$user = Users::getUserById(Session::get('user_id'));

if ($user) {
    echo json_encode(["status" => "success", "data" => $user]);
} else {
    echo json_encode(["status" => "error", "message" => "User not found."]);
}
?>
