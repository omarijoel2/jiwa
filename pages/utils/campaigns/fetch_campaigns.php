<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Query;

header('Content-Type: application/json');

// DataTables parameters
$draw = $_GET['draw'] ?? 1;
$start = $_GET['start'] ?? 0;
$length = $_GET['length'] ?? 10;

// Column-specific search values
$searchFilters = [
    'name' => $_GET['column_0'] ?? '',
    'shortcode' => $_GET['column_1'] ?? '',
    'keyword' => $_GET['column_2'] ?? '',
    'min_amount' => $_GET['column_3'] ?? '',
    'status' => $_GET['column_4'] ?? ''
];

// Base SQL query with JOIN to fetch shortcode value
$baseQuery = "FROM winners_selection.cronjob_config 
              LEFT JOIN shortcodes ON cronjob_config.shortcode = shortcodes.shortcode_id";
$sql = "SELECT cronjob_config.id, cronjob_config.name, shortcodes.shortcode AS shortcode_value, 
        cronjob_config.account, cronjob_config.minimum_amount, cronjob_config.enabled, 
        cronjob_config.description $baseQuery";

// Apply column filters
$params = [];
$filterQuery = [];
if (!empty($searchFilters['name'])) {
    $filterQuery[] = "cronjob_config.name LIKE :name";
    $params['name'] = "%{$searchFilters['name']}%";
}
if (!empty($searchFilters['shortcode'])) {
    $filterQuery[] = "shortcodes.shortcode LIKE :shortcode";
    $params['shortcode'] = "%{$searchFilters['shortcode']}%";
}
if (!empty($searchFilters['keyword'])) {
    $filterQuery[] = "cronjob_config.account LIKE :account";
    $params['account'] = "%{$searchFilters['keyword']}%";
}
if (!empty($searchFilters['min_amount'])) {
    $filterQuery[] = "cronjob_config.minimum_amount >= :min_amount";
    $params['min_amount'] = (float) $searchFilters['min_amount'];
}
if ($searchFilters['status'] === "Active") {
    $filterQuery[] = "cronjob_config.enabled = 1";
} elseif ($searchFilters['status'] === "Inactive") {
    $filterQuery[] = "cronjob_config.enabled = 0";
}

// Combine filters
$filterString = !empty($filterQuery) ? " WHERE " . implode(" AND ", $filterQuery) : "";

// Get total number of records (before filtering)
$totalRecords = Query::fetchOne("SELECT COUNT(cronjob_config.id) AS total $baseQuery")['total'];

// Get total number of filtered records
$totalFiltered = Query::fetchOne("SELECT COUNT(cronjob_config.id) AS total $baseQuery $filterString", $params);
$totalFiltered = $totalFiltered ? $totalFiltered['total'] : $totalRecords;

// Fetch paginated records
$pagedQuery = "$sql $filterString ORDER BY cronjob_config.id DESC LIMIT :start, :length";
$campaigns = Query::query($pagedQuery, array_merge($params, [
    "start" => (int)$start,
    "length" => (int)$length
]));

// Prepare response data
$data = [];
foreach ($campaigns as $campaign) {
    $status = $campaign['enabled'] ? "<span class='badge badge-success'>Active</span>" : "<span class='badge badge-danger'>Inactive</span>";
    $activate = $campaign['enabled'] ? "<a class='dropdown-item update-status' href='#' data-id='" . htmlspecialchars($campaign['id']) . "' data-status='0'><b style='color:red'>Deactivate</b></a>" : "<a class='dropdown-item update-status' href='#' data-id='" . htmlspecialchars($campaign['id']) . "' data-status='1'><b style='color:green'>Activate</b></a>";

    $actions = "<div class='dropdown'>
                <button class='btn btn-sm btn-secondary dropdown-toggle' type='button' data-toggle='dropdown'>Action</button>
                <div class='dropdown-menu dropdown-menu-right'>
                    ".$activate."
                    <a class='dropdown-item edit-campaign' href='#' data-toggle='modal' data-target='#edit_campaign_modal' 
                        data-id='" . htmlspecialchars($campaign['id']) . "' 
                        data-name='" . htmlspecialchars($campaign['name']) . "' 
                        data-description='" . htmlspecialchars($campaign['description']) . "'
                        data-shortcode='" . htmlspecialchars($campaign['shortcode_value']) . "' 
                        data-account='" . htmlspecialchars($campaign['account']) . "' 
                        data-minimum_amount='" . htmlspecialchars($campaign['minimum_amount']) . "' 
                        data-enabled='" . htmlspecialchars($campaign['enabled']) . "'><b style='color:blue'>Edit</b></a>
                    <a class='dropdown-item delete-campaign' href='#' data-toggle='modal' data-target='#delete_campaign_modal'
                        data-id='" . htmlspecialchars($campaign['id']) . "' 
                        data-name='" . htmlspecialchars($campaign['name']) . "' 
                        data-description='" . htmlspecialchars($campaign['description']) . "'
                        data-shortcode='" . htmlspecialchars($campaign['shortcode_value']) . "' 
                        data-account='" . htmlspecialchars($campaign['account']) . "' 
                        data-minimum_amount='" . htmlspecialchars($campaign['minimum_amount']) . "' 
                        data-enabled='" . htmlspecialchars($campaign['enabled']) . "'><b style='color:red'>Delete</b></a>
                     <a class='dropdown-item view-winning-conditions' href='winning_conditions.php?_id=" . htmlspecialchars($campaign['id']) . "'>Winning Conditions</a>
                </div>
            </div>";

    $data[] = [
        $campaign['name'],
        $campaign['shortcode_value'], // Now showing the shortcode value instead of the ID
        $campaign['account'],
        number_format($campaign['minimum_amount'], 2),
        $status,
        $actions
    ];
}

// Return JSON response
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalFiltered,
    "data" => $data
]);
?>
