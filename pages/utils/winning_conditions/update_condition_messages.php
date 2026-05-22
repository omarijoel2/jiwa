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
$cronjob_id = $_GET['cronjob_id'] ?? null; // Use _id as cronjob_id
$id = $_GET['id'] ?? null;
$winning_message = trim($_GET['winning_message'] ?? '');
$losing_message = trim($_GET['losing_message'] ?? '');



// Validate required fields
if (empty($winning_message) || empty($losing_message)) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}


// Update winning conditions in the database
$sql = "UPDATE winners_selection.winner_conditions 
        SET winning_message = :winning_message, 
            losing_message = :losing_message
        WHERE id = :id";

$params = [
    "winning_message" => $winning_message,
    "losing_message" => $losing_message,
    "id" => $id
];

$updated = Query::updateDelete($sql, $params);

echo json_encode(["status" => $updated ? "success" : "error", "message" => $updated ? "Condition Messages updated successfully" : "Failed to update"]);
?>
