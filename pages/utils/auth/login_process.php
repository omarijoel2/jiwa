<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Users;
use App\Classes\Session;
use App\Classes\Logger;

header('Content-Type: application/json');
Session::start();

$logger = new Logger();


// Retrieve data
$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$password = isset($_GET['password']) ? $_GET['password'] : '';
$remember_me = isset($_GET['remember_me']);

// $logger->debug("Data: ", ['email' => $email, 'password' => $password, 'remember_me' => $remember_me]);

// Server-side validation
$errors = [];

if (empty($email)) {
    $errors['email'] = "Please enter your email.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email format.";
}

if (empty($password)) {
    $errors['password'] = "Please enter your password.";
}

if (!empty($errors)) {
    echo json_encode(["status" => "error", "errors" => $errors]);
    exit;
}

// Authenticate user
$user = Users::authenticate($email, $password);

if ($user === "inactive") {
    echo json_encode(["status" => "error", "message" => "Your account is inactive. Contact admin."]);
    exit;
} elseif (!$user) {
    echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
    exit;
}

// Set session variables
Session::set('user_id', $user['id']);
Session::set('role_id', $user['role_id']);
$redirectPage = (Session::getLastPage() );

// Remember Me functionality
if ($remember_me) {
    $token = bin2hex(random_bytes(32));
    setcookie("remember_me", $token, time() + (30 * 24 * 60 * 60), "/"); // 30 days
    Users::setRememberMeToken($user['id'], $token);
}

echo json_encode(["status" => "success", "message" => "Login successful.", "redirect" => $redirectPage]);
?>
