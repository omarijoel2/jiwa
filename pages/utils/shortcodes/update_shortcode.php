<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Query;
use App\Classes\Logger;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$logger = new Logger();

$logger->debug("Data: ", $_GET);
// Extract form data
$id = $_GET['id'] ?? null;
$shortcode = trim($_GET['shortcode'] ?? '');
$shortcode_id = trim($_GET['shortcode_id'] ?? '');

// Validate required fields
if (empty($shortcode) || empty($shortcode_id)) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

// Validate minimum amount
if (!is_numeric($shortcode) || !is_numeric($shortcode_id)) {
    echo json_encode(["status" => "error", "message" => "All fields must be a valid number greater than 0."]);
    exit;
}

$check_shortcode = "SELECT id FROM winners_selection.shortcodes WHERE shortcode = :shortcode AND id != :id";
$params_shortcode = [
    'shortcode' => $shortcode,
    "id" => $id
];

$existing_shortcode = Query::fetchOne($check_shortcode, $params_shortcode);
if ($existing_shortcode) {
    echo json_encode(["status" => "error", "message" => "Shortcode already exists."]);
    exit;
}


$check_shortcode_id = "SELECT id FROM winners_selection.shortcodes WHERE shortcode_id = :shortcode_id and id != :id";
$params_shortcode_id = [
    'shortcode_id' => $shortcode_id,
    "id" => $id
];

$existing_shortcode_id = Query::fetchOne($check_shortcode_id, $params_shortcode_id);
if ($existing_shortcode_id) {
    echo json_encode(["status" => "error", "message" => "Shortcode / Paybill ID already exist."]);
    exit;
}


// Update campaign in the database
$sql = "UPDATE winners_selection.shortcodes 
        SET shortcode = :shortcode, 
            shortcode_id = :shortcode_id
        WHERE id = :id";

$params = [
    "shortcode" => $shortcode,
    "shortcode_id" => $shortcode_id,
    "id" => $id
];

$updated = Query::updateDelete($sql, $params);

echo json_encode(["status" => $updated ? "success" : "error", "message" => $updated ? "Shortcode updated successfully" : "Failed to update"]);
?>
