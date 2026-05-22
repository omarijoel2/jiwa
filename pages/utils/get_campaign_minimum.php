<?php
require_once __DIR__ . '/../../Config/autoload.php';
use App\Classes\Query;
use App\Classes\Logger;

header('Content-Type: application/json');

$cronjob_id = $_GET['cronjob_id'] ?? null;

$logger = new Logger();
$logger->debug($cronjob_id);

if (!$cronjob_id) {
    echo json_encode(["status" => "error", "message" => "Invalid campaign ID"]);
    exit;
}

// Fetch the campaign's minimum amount
$query = "SELECT minimum_amount FROM winners_selection.cronjob_config WHERE id = :cronjob_id";
$campaign = Query::fetchOne($query, ['cronjob_id' => $cronjob_id]);

if ($campaign) {
    echo json_encode(["status" => "success", "min_amount" => $campaign['minimum_amount']]);
} else {
    echo json_encode(["status" => "error", "message" => "Campaign not found"]);
}
?>
