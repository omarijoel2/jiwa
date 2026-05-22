<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Query;

header('Content-Type: application/json');

$cronjob_id = $_GET['cronjob_id'] ?? null; // Use _id as cronjob_id
$condition_name = trim($_GET['condition_name'] ?? '');
$condition_description = trim($_GET['condition_description'] ?? '');
$minimum_amount = $_GET['minimum_amount'] ?? null;
$maximum_amount = $_GET['maximum_amount'] ?? null;
$winnings = $_GET['winnings'] ?? null;
$winning_percentage = $_GET['winning_percentage'] ?? null;
$reset_every = $_GET['reset_every'] ?? null;
$enabled = isset($_GET['enable_condition']) ? 1 : 0;

// Validate required fields
// if (!$cronjob_id || empty($condition_name) || !$minimum_amount || !$maximum_amount) {
//     echo json_encode(["status" => "error", "message" => "All required fields must be filled."]);
//     exit;
// }

// Ensure minimum_amount is numeric & valid
$minimum_amount = floatval($minimum_amount);
if ($minimum_amount <= 0) {
    echo json_encode(["status" => "error", "message" => "Minimum amount must be greater than 0."]);
    exit;
}

// Ensure maximum_amount is numeric & greater than minimum_amount
$maximum_amount = floatval($maximum_amount);
if ($maximum_amount <= $minimum_amount) {
    echo json_encode(["status" => "error", "message" => "Maximum amount must be greater than the minimum amount."]);
    exit;
}

// Fetch the campaign's minimum amount
$campaign = Query::fetchOne("SELECT minimum_amount FROM winners_selection.cronjob_config WHERE id = :cronjob_id", ['cronjob_id' => $cronjob_id]);
if (!$campaign) {
    echo json_encode(["status" => "error", "message" => "Invalid campaign ID."]);
    exit;
}

// Ensure minimum_amount is not less than the campaign's minimum
if ($minimum_amount < floatval($campaign['minimum_amount'])) {
    echo json_encode(["status" => "error", "message" => "Minimum amount cannot be lower than the campaign's set minimum ({$campaign['minimum_amount']} /=)."]);
    exit;
}

// Check if minimum amount already exists
$duplicateMin = Query::fetchOne("SELECT COUNT(id) AS total FROM winners_selection.winner_conditions WHERE cronjob_id = :cronjob_id AND amount_min = :minimum_amount", ['cronjob_id' => $cronjob_id, 'minimum_amount' => $minimum_amount]);
if ($duplicateMin['total'] > 0) {
    echo json_encode(["status" => "error", "message" => "A winning condition with this minimum amount already exists."]);
    exit;
}

// Check if maximum amount already exists
$duplicateMax = Query::fetchOne("SELECT COUNT(id) AS total FROM winners_selection.winner_conditions WHERE cronjob_id = :cronjob_id AND amount_max = :maximum_amount", ['cronjob_id' => $cronjob_id, 'maximum_amount' => $maximum_amount]);
if ($duplicateMax['total'] > 0) {
    echo json_encode(["status" => "error", "message" => "A winning condition with this maximum amount already exists."]);
    exit;
}

// Check if condition name already exists
$check_name = "SELECT id FROM winners_selection.winner_conditions WHERE name = :condition_name";
$params_name = [
    'condition_name' => $condition_name,
];

$existing_name = Query::fetchOne($check_name, $params_name);
if ($existing_name) {
    echo json_encode(["status" => "error", "message" => "Condition name already exists."]);
    exit;
}

// Insert the new condition
$sql = "INSERT INTO winners_selection.winner_conditions (cronjob_id, name, description, amount_min, amount_max, winnings, winning_percentage, reset_every, enabled) 
        VALUES (:cronjob_id, :condition_name, :condition_description, :minimum_amount, :maximum_amount, :winnings, :winning_percentage, :reset_every, :enabled)";

$params = [
    'cronjob_id' => $cronjob_id,
    'condition_name' => $condition_name,
    'condition_description' => $condition_description,
    'minimum_amount' => $minimum_amount,
    'maximum_amount' => $maximum_amount,
    'winnings' => $winnings,
    'winning_percentage' => $winning_percentage,
    'reset_every' => $reset_every,
    'enabled' => $enabled
];

$inserted = Query::insert($sql, $params);

if ($inserted) {
    echo json_encode(["status" => "success", "message" => "Winning condition added successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add winning condition."]);
}
?>
