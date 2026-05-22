<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Query;
use App\Classes\Logger;

header('Content-Type: application/json');


// Allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["status" => "error", "message" => "Invalid request method: " . $_SERVER['REQUEST_METHOD']]);
    exit;
}

// Read GET parameters
$shortcode = $_GET['d_shortcode'] ?? '';
$shortcode_id = $_GET['d_id'] ?? '';


// Validate required fields
if (empty($shortcode) || empty($shortcode_id)) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

// Validate shortcode
if (!is_numeric($shortcode) || $shortcode <= 0) {
    echo json_encode(["status" => "error", "message" => "Minimum amount must be a valid number greater than 0."]);
    exit;
}

if (!is_numeric($shortcode_id) || $shortcode_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Minimum amount must be a valid number greater than 0."]);
    exit;
}

// Check if shortcode or shortcode_id already exists
$check_shortcode = "SELECT id FROM winners_selection.shortcodes WHERE shortcode = :shortcode";
$params_shortcode = [
    'shortcode' => $shortcode,
];

$existing_shortcode = Query::fetchOne($check_shortcode, $params_shortcode);
if ($existing_shortcode) {
    echo json_encode(["status" => "error", "message" => "Shortcode / Paybill already exists."]);
    exit;
}

$check_shortcode_id = "SELECT id FROM winners_selection.shortcodes WHERE shortcode_id = :shortcode_id";
$params_shortcode_id = [
    'shortcode_id' => $shortcode_id
];

$existing_shortcode_id = Query::fetchOne($check_shortcode_id, $params_shortcode_id);
if ($existing_shortcode_id) {
    echo json_encode(["status" => "error", "message" => "Shortcode ID already exists"]);
    exit;
}

$sql = "INSERT INTO winners_selection.shortcodes (shortcode, shortcode_id) 
        VALUES (:shortcode, :shortcode_id)";

$params = [
    'shortcode' => $shortcode,
    'shortcode_id' => $shortcode_id
];

$logger = new Logger();
$inserted = Query::insert($sql, $params);

if ($inserted) {
    // $logger->info("Campaign added successfully",$params);
    echo json_encode(["status" => "success", "message" => "Shortcode added successfully"]);
} else {
    // $logger->error("Failed to add campaign", $params);
    echo json_encode(["status" => "error", "message" => "Failed to add shortcode"]);
}
?>
