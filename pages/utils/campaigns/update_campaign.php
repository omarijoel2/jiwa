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
$campaign_name = trim($_GET['campaign_name'] ?? '');
$campaign_shortcode = trim($_GET['campaign_shortcode'] ?? '');
$campaign_keyword = trim($_GET['campaign_keyword'] ?? '');
$campaign_description = trim($_GET['campaign_description'] ?? '');
$minimum_amount = trim($_GET['minimum_amount'] ?? '');
$enable_campaign = isset($_GET['enable_campaign']) ? intval($_GET['enable_campaign']) : 0;

// Validate required fields
if (empty($campaign_name) || empty($campaign_shortcode) || empty($campaign_keyword) || empty($minimum_amount)) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

// Validate minimum amount
if (!is_numeric($minimum_amount) || $minimum_amount <= 0) {
    echo json_encode(["status" => "error", "message" => "Minimum amount must be a valid number greater than 0."]);
    exit;
}

$check_name = "SELECT id FROM winners_selection.cronjob_config WHERE name = :campaign_name AND id != :id";
$params_name = [
    'campaign_name' => $campaign_name,
    "id" => $id
];

$existing_name = Query::fetchOne($check_name, $params_name);
if ($existing_name) {
    echo json_encode(["status" => "error", "message" => "Campaign name already exists."]);
    exit;
}


$check_short_keyword = "SELECT id FROM winners_selection.cronjob_config WHERE shortcode = :campaign_shortcode and account = :campaign_keyword AND id != :id";
$params_short_keyword = [
    'campaign_shortcode' => $campaign_shortcode,
    'campaign_keyword' => $campaign_keyword,
    "id" => $id
];

$existing_short_keyword = Query::fetchOne($check_short_keyword, $params_short_keyword);
if ($existing_short_keyword) {
    echo json_encode(["status" => "error", "message" => "Paybill and Keyword already exist. They should be unique."]);
    exit;
}


// Update campaign in the database
$sql = "UPDATE winners_selection.cronjob_config 
        SET name = :campaign_name, 
            description = :campaign_description, 
            shortcode = :campaign_shortcode, 
            account = :campaign_keyword, 
            minimum_amount = :minimum_amount, 
            enabled = :enable_campaign 
        WHERE id = :id";

$params = [
    "campaign_name" => $campaign_name,
    "campaign_description" => $campaign_description,
    "campaign_shortcode" => $campaign_shortcode,
    "campaign_keyword" => $campaign_keyword,
    "minimum_amount" => $minimum_amount,
    "enable_campaign" => $enable_campaign,
    "id" => $id
];

$updated = Query::updateDelete($sql, $params);

echo json_encode(["status" => $updated ? "success" : "error", "message" => $updated ? "Campaign updated successfully" : "Failed to update"]);
?>
