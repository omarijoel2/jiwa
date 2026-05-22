<?php
require_once __DIR__ . '/../../../Config/autoload.php';

use App\Classes\Query;
use App\Classes\Logger;

header('Content-Type: application/json');

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$cacheTtl = 60; // Cache time in seconds

$draw = $_GET['draw'] ?? 1;
$start = $_GET['start'] ?? 0;
$length = $_GET['length'] ?? 10;

$searchFilters = [
    'campaign_name' => $_GET['column_0'] ?? '',
    'shortcode' => $_GET['column_1'] ?? '',
    'condition_name' => $_GET['column_2'] ?? '',
    'start_date' => isset($_GET['column_3_a']) && !empty($_GET['column_3_a']) ? date('Y-m-d H:i:s', strtotime($_GET['column_3_a'] . ' -2 hours')) : '',
    'end_date' => isset($_GET['column_3_b']) && !empty($_GET['column_3_b']) ? date('Y-m-d H:i:s', strtotime($_GET['column_3_b'] . ' -2 hours')) : '',
];

// Generate cache key
$cacheKey = "winners_log:" . md5(json_encode([$searchFilters, $start, $length]));

if ($redis->exists($cacheKey)) {
    echo $redis->get($cacheKey);
    exit;
}

$baseQuery = "FROM winners_selection.winners_log 
              LEFT JOIN shortcodes ON winners_log.shortcode = shortcodes.shortcode_id";

$sql = "SELECT winners_log.id, shortcodes.shortcode AS shortcode_value, winners_log.customer_name, winners_log.msisdn, winners_log.transaction_code,  
        winners_log.keyword, winners_log.amount_transacted, winners_log.amount_won, winners_log.cronjob_name, 
        winners_log.condition_name, winners_log.timestamp $baseQuery";

$params = [];
$filterQuery = [];

// Dynamically add filters if they are set
if (!empty($searchFilters['campaign_name'])) {
    $filterQuery[] = "winners_log.cronjob_name LIKE :campaign_name";
    $params['campaign_name'] = "%{$searchFilters['campaign_name']}%";
}

if (!empty($searchFilters['condition_name'])) {
    $filterQuery[] = "winners_log.condition_name LIKE :condition_name";
    $params['condition_name'] = "%{$searchFilters['condition_name']}%";
}

if (!empty($searchFilters['shortcode'])) {
    $filterQuery[] = "shortcodes.shortcode_id LIKE :shortcode";
    $params['shortcode'] = "%{$searchFilters['shortcode']}%";
}

if (!empty($searchFilters['start_date']) && !empty($searchFilters['end_date'])) {
    $filterQuery[] = "winners_log.timestamp BETWEEN :start_date AND :end_date";
    $params['start_date'] = $searchFilters['start_date'];
    $params['end_date'] = $searchFilters['end_date'];
}

// Combine filters into WHERE clause
$filterString = !empty($filterQuery) ? " WHERE " . implode(" AND ", $filterQuery) : "";

// Fetch total records before filtering
$totalRecords = Query::fetchOne("SELECT COUNT(winners_log.id) AS total $baseQuery")['total'];

// Fetch total filtered records
$totalFilteredQuery = "SELECT COUNT(winners_log.id) AS total $baseQuery $filterString";
$totalFiltered = Query::fetchOne($totalFilteredQuery, $params);
$totalFiltered = $totalFiltered ? $totalFiltered['total'] : $totalRecords;

// Fetch paginated results
$pagedQuery = "$sql $filterString ORDER BY winners_log.id DESC LIMIT :start, :length";
$winners_logs = Query::query($pagedQuery, array_merge($params, [
    "start" => (int)$start,
    "length" => (int)$length
]));

$data = [];
foreach ($winners_logs as $winners_log) {
    $data[] = [
        $winners_log['shortcode_value'],
        $winners_log['customer_name'],
        $winners_log['msisdn'],
        $winners_log['transaction_code'],
        $winners_log['keyword'],
        $winners_log['amount_transacted'],
        $winners_log['amount_won'],
        $winners_log['cronjob_name'],
        $winners_log['condition_name'],
        date('Y-m-d H:i:s', strtotime($winners_log['timestamp'] . ' +2 hours'))
    ];
}

$response = json_encode([
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalFiltered,
    "data" => $data
]);

// Store in Redis cache
$redis->setex($cacheKey, $cacheTtl, $response);

echo $response;
?>
