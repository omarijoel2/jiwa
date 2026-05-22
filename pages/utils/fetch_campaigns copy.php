<?php
require_once __DIR__ . '/../../Config/autoload.php';
use App\Classes\Query;

header('Content-Type: application/json');

// DataTables parameters
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$searchValue = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';
$statusFilter = isset($_GET['status']) && $_GET['status'] !== '' ? intval($_GET['status']) : null;

// Base SQL query
$baseQuery = "FROM winners_selection.cronjob_config";
$sql = "SELECT id, name, shortcode, account, minimum_amount, enabled, description $baseQuery";

// Apply search and status filters
$params = [];
$filterQuery = [];
if (!empty($searchValue)) {
    $filterQuery[] = "(name LIKE :search OR account LIKE :search OR shortcode LIKE :search)";
    $params['search'] = "%$searchValue%";
}
if ($statusFilter !== null) {
    $filterQuery[] = "enabled = :status";
    $params['status'] = $statusFilter;
}

// Combine filters
$filterString = !empty($filterQuery) ? " WHERE " . implode(" AND ", $filterQuery) : "";

// Get total number of records (before filtering)
$totalRecords = Query::fetchOne("SELECT COUNT(id) AS total $baseQuery")['total'];

// Get total number of filtered records
$totalFiltered = Query::fetchOne("SELECT COUNT(id) AS total $baseQuery $filterString", $params);
$totalFiltered = $totalFiltered ? $totalFiltered['total'] : $totalRecords;

// Fetch paginated records
$pagedQuery = "$sql $filterString ORDER BY id DESC LIMIT :start, :length";
$campaigns = Query::query($pagedQuery, array_merge($params, [
    "start" => (int)$start,
    "length" => (int)$length
]));

// Prepare response data
$data = [];
foreach ($campaigns as $campaign) {
    $status = $campaign['enabled'] ? "<span class='badge badge-success'>Active</span>" : "<span class='badge badge-danger'>Inactive</span>";

    $actions = "<div class='dropdown'>
                <button class='btn btn-sm btn-secondary dropdown-toggle' type='button' data-toggle='dropdown'>Actions</button>
                <div class='dropdown-menu'>
                    <a class='dropdown-item update-status' href='#' data-id='" . htmlspecialchars($campaign['id']) . "' data-status='1'>Activate</a>
                    <a class='dropdown-item update-status' href='#' data-id='" . htmlspecialchars($campaign['id']) . "' data-status='0'>Deactivate</a>
                    <a class='dropdown-item edit-campaign' href='#' data-toggle='modal' data-target='#edit_campaign_modal' 
                        data-id='" . htmlspecialchars($campaign['id']) . "' 
                        data-name='" . htmlspecialchars($campaign['name']) . "' 
                        data-description='" . htmlspecialchars($campaign['description']) . "'
                        data-shortcode='" . htmlspecialchars($campaign['shortcode']) . "' 
                        data-account='" . htmlspecialchars($campaign['account']) . "' 
                        data-minimum_amount='" . htmlspecialchars($campaign['minimum_amount']) . "' 
                        data-enabled='" . htmlspecialchars($campaign['enabled']) . "'>Edit</a>
                    <a class='dropdown-item delete-campaign' href='#' data-toggle='modal' data-target='#delete_campaign_modal'
                        data-id='" . htmlspecialchars($campaign['id']) . "' 
                        data-name='" . htmlspecialchars($campaign['name']) . "' 
                        data-description='" . htmlspecialchars($campaign['description']) . "'
                        data-shortcode='" . htmlspecialchars($campaign['shortcode']) . "' 
                        data-account='" . htmlspecialchars($campaign['account']) . "' 
                        data-minimum_amount='" . htmlspecialchars($campaign['minimum_amount']) . "' 
                        data-enabled='" . htmlspecialchars($campaign['enabled']) . "'>Delete</a>
                </div>
            </div>";

    $data[] = [
        $campaign['name'],
        $campaign['shortcode'],
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
