<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Query;
use App\Classes\Logger;

header('Content-Type: application/json');

$draw = $_GET['draw'] ?? 1;
$start = $_GET['start'] ?? 0;
$length = $_GET['length'] ?? 10;

$searchFilters = [ 
    'shortcode' => $_GET['column_1'] ?? '',
    'keyword' => $_GET['column_2'] ?? '',
    'start_date' => date('Y-m-d H:i:s', strtotime($_GET['column_3_a'] . ' -2 hours')) ?? '',
    'end_date' => date('Y-m-d H:i:s', strtotime($_GET['column_3_b'] . ' -2 hours')) ?? '',
    'customer_name' => $_GET['column_4'] ?? '',
    'unhashed_msisdn' => $_GET['column_5'] ?? '',
    'hashed_msisdn' => $_GET['column_6'] ?? '',
    // 'name' => $_GET['column_0'] ?? '',
    // 'name' => $_GET['column_0'] ?? '',
];

$baseQuery = "FROM winners_selection.contacts 
              LEFT JOIN shortcodes ON contacts.shortcode = shortcodes.shortcode_id";

$sql = "SELECT contacts.id, shortcodes.shortcode AS shortcode_value, contacts.customer_name, contacts.hashed_msisdn, contacts.unhashed_msisdn,  
        contacts.keyword, contacts.date_added $baseQuery";

$params = [];
$filterQuery = [];

if (!empty($searchFilters['shortcode'])) {
    $filterQuery[] = "shortcodes.shortcode_id LIKE :shortcode";
    $params['shortcode'] = "%{$searchFilters['shortcode']}%";
}
if (!empty($searchFilters['start_date']) && !empty($searchFilters['end_date'])) {
    $filterQuery[] = "contacts.date_added >= :start_date AND contacts.date_added <= :end_date";
    $params['start_date'] = $searchFilters['start_date'];
    $params['end_date'] = $searchFilters['end_date'];
} 


if (!empty($searchFilters['keyword'])) {
    $filterQuery[] = "contacts.keyword LIKE :keyword";
    $params['keyword'] = "%{$searchFilters['keyword']}%";
}

if (!empty($searchFilters['customer_name'])) {
    $filterQuery[] = "contacts.customer_name LIKE :customer_name";
    $params['customer_name'] = "%{$searchFilters['customer_name']}%";
}

if (!empty($searchFilters['unhashed_msisdn'])) {
    $filterQuery[] = "contacts.unhashed_msisdn LIKE :unhashed_msisdn";
    $params['unhashed_msisdn'] = "%{$searchFilters['unhashed_msisdn']}%";
}

if (!empty($searchFilters['hashed_msisdn'])) {
    $filterQuery[] = "contacts.hashed_msisdn = :hashed_msisdn";
    $params['hashed_msisdn'] = $searchFilters['hashed_msisdn'];
}

$filterString = !empty($filterQuery) ? " WHERE " . implode(" AND ", $filterQuery) : "";

$totalRecords = Query::fetchOne("SELECT COUNT(contacts.id) AS total $baseQuery")['total'];

$totalFiltered = Query::fetchOne("SELECT COUNT(contacts.id) AS total $baseQuery $filterString", $params);
$totalFiltered = $totalFiltered ? $totalFiltered['total'] : $totalRecords;

$pagedQuery = "$sql $filterString ORDER BY contacts.id DESC LIMIT :start, :length";
$contacts = Query::query($pagedQuery, array_merge($params, [
    "start" => (int)$start,
    "length" => (int)$length
]));

// FIXME: Remove this code
// $logger = new Logger();

// $logger->log('Query Fetch Winners', $pagedQuery );

$data = [];
foreach($contacts as $contact){
    $data[] = [
        $contact['unhashed_msisdn'],
        $contact['hashed_msisdn'],
        $contact['shortcode_value'],
        $contact['keyword'],
        $contact['customer_name'],
        date('Y-m-d H:i:s', strtotime($contact['date_added'] . ' +2 hours'))
    ];
}

echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalFiltered,
    "data" => $data
]);

?>
