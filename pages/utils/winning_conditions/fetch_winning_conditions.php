<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Query;
use App\Classes\Logger;

header('Content-Type: application/json');

$logger = new Logger();

// DataTables parameters
$draw = $_GET['draw'] ?? 1;
$start = $_GET['start'] ?? 0;
$length = $_GET['length'] ?? 10;
$cronjob_id = $_GET['cronjob_id'] ?? null;

// If no cronjob ID is provided, return empty data
if (!$cronjob_id) {
    echo json_encode(["draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []]);
    exit;
}

// Collect search filters from GET request
$searchFilters = [
    'name'               => $_GET['column_0'] ?? '',
    'amount_min'         => $_GET['column_1'] ?? '',
    'amount_max'         => $_GET['column_2'] ?? '',
    'winnings'           => $_GET['column_3'] ?? '',
    'winning_percentage' => $_GET['column_4'] ?? '',
    'reset_every'        => $_GET['column_5'] ?? '',
    'status'             => $_GET['column_6'] ?? ''
];

// Base SQL query
$baseQuery = "FROM winners_selection.winner_conditions WHERE cronjob_id = :cronjob_id";
$sql = "SELECT id, name, amount_min, amount_max, winnings, winning_percentage, reset_every, enabled, description, winning_message, losing_message $baseQuery";

// Apply filters dynamically
$params = ['cronjob_id' => $cronjob_id];
$filterQuery = [];

if (!empty($searchFilters['name'])) {
    $filterQuery[] = "name LIKE :name";
    $params['name'] = "%" . $searchFilters['name'] . "%";
}
if (!empty($searchFilters['amount_min'])) {
    $filterQuery[] = "amount_min >= :amount_min";
    $params['amount_min'] = (float) $searchFilters['amount_min'];
}
if (!empty($searchFilters['amount_max'])) {
    $filterQuery[] = "amount_max <= :amount_max";
    $params['amount_max'] = (float) $searchFilters['amount_max'];
}
if (!empty($searchFilters['winnings'])) {
    $filterQuery[] = "winnings = :winnings";
    $params['winnings'] = (float) $searchFilters['winnings'];
}
if (!empty($searchFilters['winning_percentage'])) {
    $filterQuery[] = "winning_percentage = :winning_percentage";
    $params['winning_percentage'] = (float) $searchFilters['winning_percentage'];
}
if (!empty($searchFilters['reset_every'])) {
    $filterQuery[] = "reset_every = :reset_every";
    $params['reset_every'] = (int) $searchFilters['reset_every'];
}
if ($searchFilters['status'] === "Active") {
    $filterQuery[] = "enabled = 1";
} elseif ($searchFilters['status'] === "Inactive") {
    $filterQuery[] = "enabled = 0";
}

// Combine filters into query
$filterString = !empty($filterQuery) ? " AND " . implode(" AND ", $filterQuery) : "";

// Get total records count (before filtering)
$totalRecords = Query::fetchOne("SELECT COUNT(id) AS total $baseQuery", ['cronjob_id' => $cronjob_id])['total'];

// Get total filtered records count
$totalFiltered = Query::fetchOne("SELECT COUNT(id) AS total $baseQuery $filterString", $params);
$totalFiltered = $totalFiltered ? $totalFiltered['total'] : $totalRecords;

// Fetch paginated records
$pagedQuery = "$sql $filterString ORDER BY id DESC LIMIT :start, :length";
$conditions = Query::query($pagedQuery, array_merge($params, [
    "start" => (int)$start,
    "length" => (int)$length
]));

// Fetch campaign name
$campaign = Query::fetchOne("SELECT name FROM winners_selection.cronjob_config WHERE id = :cronjob_id", ['cronjob_id' => $cronjob_id]);
$campaign_name = $campaign ? $campaign['name'] : "Unknown Campaign";

// Prepare response data
$data = [];
foreach ($conditions as $condition) {
    $status = $condition['enabled'] ? "<span class='badge badge-success'>Active</span>" : "<span class='badge badge-danger'>Inactive</span>";

    $actions = "<div class='dropdown'>
                    <button class='btn btn-sm btn-secondary dropdown-toggle' type='button' data-toggle='dropdown'>Action</button>
                    <div class='dropdown-menu dropdown-menu-left'>
                        <a class='dropdown-item update-status' href='#' data-id='{$condition['id']}' data-status='".(!$condition['enabled'] ? '1' : '0')."'>"
                        . (!$condition['enabled'] ? "<b style='color:green'>Activate</b>" : "<b style='color:red'>Deactivate</b>") . "</a>
                        <a class='dropdown-item condition-message' href='#' data-toggle='modal' data-target='#condition_message_modal' 
                            data-id='{$condition['id']}'
                            data-name='{$condition['name']}'
                            data-winning_message='{$condition['winning_message']}'
                            data-losing_message='{$condition['losing_message']}'>Update Message(s)</a>
                        <a class='dropdown-item edit-condition' href='#' data-toggle='modal' data-target='#edit_condition_modal' 
                            data-id='{$condition['id']}'
                            data-name='{$condition['name']}'
                            data-amount-min='{$condition['amount_min']}'
                            data-amount-max='{$condition['amount_max']}'
                            data-winnings='{$condition['winnings']}'
                            data-winning-percentage='{$condition['winning_percentage']}'
                            data-description='{$condition['description']}'
                            data-reset-every='{$condition['reset_every']}'
                            data-enabled='{$condition['enabled']}'><b style='color:blue'>Edit</b></a>
                        <a class='dropdown-item delete-condition' href='#' data-toggle='modal' data-target='#delete_condition_modal'
                            data-id='{$condition['id']}'
                            data-name='{$condition['name']}'
                            data-amount-min='{$condition['amount_min']}'
                            data-amount-max='{$condition['amount_max']}'
                            data-winnings='{$condition['winnings']}'
                            data-winning-percentage='{$condition['winning_percentage']}'
                            data-reset-every='{$condition['reset_every']}'
                            data-enabled='{$condition['enabled']}'><b style='color:red'>Delete</b></a>
                    </div>
                </div>";

    $data[] = [
        htmlspecialchars($condition['name']),
        number_format($condition['amount_min'], 2),
        number_format($condition['amount_max'], 2),
        number_format($condition['winnings'], 2),
        number_format($condition['winning_percentage'], 2) . "%",
        $condition['reset_every'],
        $status,
        $actions
    ];
}

// Return JSON response
echo json_encode([
    "draw" => intval($_GET['draw'] ?? 1),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalFiltered,
    "data" => $data,
    "campaign_name" => $campaign_name
]);
?>
