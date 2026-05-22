<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Query;

header("Content-Type: application/json");

$sql = "SELECT id, name FROM winners_selection.cronjob_config ORDER BY id ASC";

$campaigns = Query::query($sql);

echo json_encode($campaigns);
?>
