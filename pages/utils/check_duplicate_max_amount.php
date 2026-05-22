<?php
require_once __DIR__ . '/../../Config/autoload.php';
use App\Classes\Query;

header('Content-Type: application/json');

$cronjob_id = $_GET['cronjob_id'] ?? null;
$maximum_amount = $_GET['maximum_amount'] ?? null;

if (!$cronjob_id || !$maximum_amount) {
    echo json_encode(false);
    exit;
}

// Check if the maximum amount already exists in another winning condition
$query = "SELECT COUNT(id) AS total FROM winners_selection.winner_conditions 
          WHERE cronjob_id = :cronjob_id AND amount_max = :maximum_amount";
$result = Query::fetchOne($query, ['cronjob_id' => $cronjob_id, 'maximum_amount' => $maximum_amount]);

if ($result && $result['total'] > 0) {
    echo json_encode(false); // Duplicate found
} else {
    echo json_encode(true); // No duplicate, allow submission
}
?>
