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
$campaign_name = $_GET['campaign_name'] ?? '';
$campaign_description = $_GET['campaign_description'] ?? '';
// $campaign_shortcode = $_GET['campaign_shortcode'] ?? '';
$campaign_shortcode = $_GET['campaign_shortcode'] ?? '';
$campaign_keyword = $_GET['campaign_keyword'] ?? '';
$minimum_amount = $_GET['minimum_amount'] ?? '';
$enable_campaign = isset($_GET['enable_campaign']) ? 1 : 0;

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

// Check if campaign name, shortcode, or keyword already exists
$check_name = "SELECT id FROM winners_selection.cronjob_config WHERE name = :campaign_name";
$params_name = [
    'campaign_name' => $campaign_name,
];

$existing_name = Query::fetchOne($check_name, $params_name);
if ($existing_name) {
    echo json_encode(["status" => "error", "message" => "Campaign name already exists."]);
    exit;
}

$check_short_keyword = "SELECT id FROM winners_selection.cronjob_config WHERE shortcode = :campaign_shortcode and account = :campaign_keyword";
$params_short_keyword = [
    'campaign_shortcode' => $campaign_shortcode,
    'campaign_keyword' => $campaign_keyword
];

$existing_short_keyword = Query::fetchOne($check_short_keyword, $params_short_keyword);
if ($existing_short_keyword) {
    echo json_encode(["status" => "error", "message" => "Paybill and Keyword already exist. They should be unique."]);
    exit;
}

$sql = "INSERT INTO winners_selection.cronjob_config (name, description, shortcode, account, minimum_amount, enabled) 
        VALUES (:campaign_name, :campaign_description, :campaign_shortcode, :campaign_keyword, :minimum_amount, :enable_campaign)";

$params = [
    'campaign_name' => $campaign_name,
    'campaign_description' => $campaign_description,
    'campaign_shortcode' => $campaign_shortcode,
    'campaign_keyword' => $campaign_keyword,
    'minimum_amount' => $minimum_amount,
    'enable_campaign' => $enable_campaign
];

$logger = new Logger();
$inserted = Query::insert($sql, $params);

if ($inserted) {
    // $logger->info("Campaign added successfully",$params);
    echo json_encode(["status" => "success", "message" => "Campaign added successfully"]);
} else {
    // $logger->error("Failed to add campaign", $params);
    echo json_encode(["status" => "error", "message" => "Failed to add campaign"]);
}
?>
