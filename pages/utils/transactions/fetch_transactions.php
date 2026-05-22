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
    'start_date' => $_GET['column_3_a'] ?? '', 
    'end_date' => $_GET['column_3_b'] ?? '', 
    'customer_name' => $_GET['column_4'] ?? '',
    'msisdn' => $_GET['column_5'] ?? '',
    'transaction_code' => $_GET['column_6'] ?? ''
   
];

$baseQuery = "FROM winners_selection.transactions 
              LEFT JOIN shortcodes ON transactions.shortcode_id = shortcodes.shortcode_id";

$sql = "SELECT transactions.id, shortcodes.shortcode AS shortcode_value, transactions.customer_name, transactions.msisdn, transactions.transaction_code,  
transactions.keyword, transactions.amount, transactions.amount_user_transacted, transactions.amount_transacted_code, 
transactions.amount_transacted_time, transactions.trans_time $baseQuery"; 

$params = [];
$filterQuery = [];

if (!empty($searchFilters['shortcode'])) {
    $filterQuery[] = "shortcodes.shortcode_id LIKE :shortcode";
    $params['shortcode'] = "%{$searchFilters['shortcode']}%";
}

if (!empty($searchFilters['keyword'])) {
    $filterQuery[] = "transactions.keyword LIKE :keyword";
    $params['keyword'] = "%{$searchFilters['keyword']}%";
}

if (!empty($searchFilters['start_date']) && !empty($searchFilters['end_date'])) {
    $filterQuery[] = "transactions.trans_time >= :start_date AND transactions.trans_time <= :end_date";
    $params['start_date'] = $searchFilters['start_date'];
    $params['end_date'] = $searchFilters['end_date'];
} 

if (!empty($searchFilters['customer_name'])) {
    $filterQuery[] = "transactions.customer_name LIKE :customer_name";
    $params['customer_name'] = "%{$searchFilters['customer_name']}%";
}

if (!empty($searchFilters['msisdn'])) {
    $filterQuery[] = "transactions.msisdn LIKE :msisdn";
    $params['msisdn'] = "%{$searchFilters['msisdn']}%";
}

if (!empty($searchFilters['transaction_code'])) {
    $filterQuery[] = "transactions.transaction_code LIKE :transaction_code";
    $params['transaction_code'] = "%{$searchFilters['transaction_code']}%";
}

$filterString = !empty($filterQuery) ? " WHERE " . implode(" AND ", $filterQuery) : "";

$totalRecords = Query::fetchOne("SELECT COUNT(transactions.id) AS total $baseQuery")['total'];


$totalFiltered = Query::fetchOne("SELECT COUNT(transactions.id) AS total $baseQuery $filterString", $params);
$totalFiltered = $totalFiltered ? $totalFiltered['total'] : $totalRecords;

$pagedQuery = "$sql $filterString ORDER BY transactions.id DESC LIMIT :start, :length";
$transactions = Query::query($pagedQuery, array_merge($params, [
    "start" => (int)$start,
    "length" => (int)$length
]));

$data = [];

foreach ($transactions as $transaction) {
    $actions = "<div class='dropdown'>
                <button class='btn btn-sm btn-secondary dropdown-toggle' type='button' data-toggle='dropdown'>Action</button>
                <div class='dropdown-menu dropdown-menu-right'>
                    <a class='dropdown-item view-transaction' href='#' data-toggle='modal' data-target='#view_transaction_modal' 
                        data-id='" . htmlspecialchars($transaction['id']) . "' 
                        data-shortcode='" . htmlspecialchars($transaction['shortcode_value']) . "' 
                        data-keyword='" . htmlspecialchars($transaction['keyword']) . "'
                        data-transaction_code='" . htmlspecialchars($transaction['transaction_code']) . "' 
                        data-amount='" . htmlspecialchars($transaction['amount']) . "' 
                        data-customer_name='" . htmlspecialchars($transaction['customer_name']) . "' 
                        data-msisdn='" . htmlspecialchars($transaction['msisdn']) . "' 
                        data-trans_time='" . htmlspecialchars($transaction['trans_time']) . "' 
                        data-amount_user_transacted='" . htmlspecialchars($transaction['amount_user_transacted']) . "' 
                        data-amount_transacted_code='" . htmlspecialchars($transaction['amount_transacted_code']) . "'
                        data-amount_transacted_time='" . htmlspecialchars(date('Y-m-d H:i:s', strtotime($transaction['amount_transacted_time'] . ' +2 hours'))) . "'>View Details</a>
                    
                </div>
            </div>";
    $data[] = [
        $transaction['shortcode_value'],
        $transaction['keyword'],
        $transaction['transaction_code'],
        $transaction['amount'],
        $transaction['customer_name'],
        $transaction['msisdn'],
        $transaction['trans_time'],
        $actions
    ];
}

echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalFiltered,
    "data" => $data
]);

?>