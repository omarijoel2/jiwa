<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Query;

header("Content-Type: application/json");

$sql = "SELECT id, shortcode_id, shortcode FROM winners_selection.shortcodes ORDER BY id ASC";
// $result = $conn->query($sql);
$shortcodes = Query::query($sql);

echo json_encode($shortcodes);
?>
