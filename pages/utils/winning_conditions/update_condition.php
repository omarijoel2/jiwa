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
$condition_name = trim($_GET['condition_name'] ?? '');
$minimum_amount = trim($_GET['minimum_amount'] ?? '');
$maximum_amount = trim($_GET['maximum_amount'] ?? '');
$winnings = trim($_GET['winnings'] ?? '');
$winning_percentage = trim($_GET['winning_percentage']);
$reset_every = trim($_GET['reset_every']);
$enable_condition = isset($_GET['enable_condition']) ? intval($_GET['enable_condition']) : 0;
$condition_description = trim($_GET['description'] ?? '');


// Validate required fields
if (empty($condition_name) || empty($minimum_amount) || empty($maximum_amount) || empty($winnings) || empty($winning_percentage) || empty($reset_every)) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

// Validate minimum amount
if (!is_numeric($minimum_amount) || $minimum_amount <= 0) {
    echo json_encode(["status" => "error", "message" => "Minimum amount must be a valid number greater than 0."]);
    exit;
}

if (!is_numeric($maximum_amount) || $maximum_amount <= 0) {
    echo json_encode(["status" => "error", "message" => "Maximum amount must be a valid number greater than 0."]);
    exit;
}

$check_name = "SELECT id FROM winners_selection.winner_conditions WHERE name = :condition_name AND id != :id";
$params_name = [
    'condition_name' => $condition_name,
    "id" => $id
];

$existing_name = Query::fetchOne($check_name, $params_name);
if ($existing_name) {
    echo json_encode(["status" => "error", "message" => "Condition name already exists."]);
    exit;
}


$check_minimum_amount = "SELECT id FROM winners_selection.winner_conditions WHERE amount_min = :minimum_amount AND cronjob_id = :cronjob_id AND id != :id";
$params_minimum_amount = [
    'minimum_amount' => $minimum_amount,
    'cronjob_id' => $cronjob_id,
    "id" => $id
];

$existing_minimum_amount = Query::fetchOne($check_minimum_amount, $params_minimum_amount);
if ($existing_minimum_amount) {
    echo json_encode(["status" => "error", "message" => "Minimum Amount already exist under this campaign. It should be unique."]);
    exit;
}

$check_maximum_amount = "SELECT id FROM winners_selection.winner_conditions WHERE amount_max = :maximum_amount AND cronjob_id = :cronjob_id AND id != :id";
$params_maximum_amount = [
    'maximum_amount' => $maximum_amount,
    'cronjob_id' => $cronjob_id,
    "id" => $id
];

$existing_maximum_amount = Query::fetchOne($check_maximum_amount, $params_maximum_amount);
if ($existing_maximum_amount) {
    echo json_encode(["status" => "error", "message" => "Maximum Amount already exist under this campaign. It should be unique."]);
    exit;
}

// Update winning conditions in the database
$sql = "UPDATE winners_selection.winner_conditions 
        SET name = :condition_name, 
            description = :condition_description, 
            amount_min = :minimum_amount,
            amount_max = :maximum_amount,
            winnings = :winnings,
            winning_percentage = :winning_percentage,
            reset_every = :reset_every,
            enabled = :enable_condition 
        WHERE id = :id";

$params = [
    "condition_name" => $condition_name,
    "condition_description" => $condition_description,
    "minimum_amount" => $minimum_amount,
    "maximum_amount" => $maximum_amount,
    "winnings" => $winnings,
    "winning_percentage" => $winning_percentage,
    "reset_every" => $reset_every,
    "enable_condition" => $enable_condition,
    "id" => $id
];

$updated = Query::updateDelete($sql, $params);

echo json_encode(["status" => $updated ? "success" : "error", "message" => $updated ? "Condition updated successfully" : "Failed to update"]);
?>
