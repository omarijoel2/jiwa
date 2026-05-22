<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Session;

header('Content-Type: application/json');

Session::start();
setcookie("remember_me", "", time() - 3600, "/"); // Expire cookie
Session::destroy();
// header("Location: login.php");
echo json_encode(["status" => "success", "redirect" => "login"]);
exit;
?>
