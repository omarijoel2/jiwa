<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Query;
use App\Classes\Logger;

header('Content-Type: application/json');

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}


// Extract values
$id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;

// Ensure required values exist
if (!$id || !in_array($status, ['0', '1'], true)) {
    echo json_encode(["status" => "error", "message" => "Invalid parameters"]);
    exit;
}

// Update status in database
$sql = "UPDATE winners_selection.cronjob_config SET enabled = :status WHERE id = :id";
$params = ["status" => $status, "id" => $id];

$updated = Query::updateDelete($sql, $params);

if ($updated) {
    echo json_encode(["status" => "success", "message" => "Campaign status updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update campaign. Try again."]);
}
?>
