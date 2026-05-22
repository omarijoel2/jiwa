<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Query;

header("Content-Type: application/json");

$campaign_id = isset($_GET['campaign_id']) ? $_GET['campaign_id'] : '';


if ($campaign_id) {
    $sql = "SELECT id, name FROM winners_selection.winner_conditions WHERE cronjob_id = :campaign_id ORDER BY id ASC";
    $conditions = Query::query($sql, [
        "campaign_id" => $campaign_id
    ]);
} else {
    $sql = "SELECT id, name FROM winners_selection.winner_conditions ORDER BY id ASC";
    $conditions = Query::query($sql);
}

echo json_encode($conditions);
?>
