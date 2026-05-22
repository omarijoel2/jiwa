<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Query;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "message" => "Invalid campaign ID"]);
    exit;
}

// Delete campaign from database
$sql = "DELETE FROM winners_selection.cronjob_config WHERE id = :id";
$params = ["id" => $id];

$deleted = Query::updateDelete($sql, $params);

if ($deleted) {
    echo json_encode(["status" => "success", "message" => "Campaign deleted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete campaign"]);
}
?>
