<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Query;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$id = $_GET['id'] ?? null;

if(!$id){
    echo json_encode(["status" => "error", "message" => "Invalid condition ID"]);
    exit;
}

// Delete condition from database
$sql = "DELETE FROM winners_selection.winner_conditions WHERE id = :id";
$params = ["id" => $id];

$deleted = Query::updateDelete($sql, $params);

if ($deleted) {
    echo json_encode(["status" => "success", "message" => "Condition deleted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete condition"]);
}
?>
