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
    'shortcode' => $_GET['column_1'] ?? '',
    'shortcode_id' => $_GET['column_0'] ?? '',
];

// Base SQL query
$baseQuery = "FROM winners_selection.shortcodes";
$sql = "SELECT id, shortcode, shortcode_id $baseQuery";

// Apply column filters
$params = [];
$filterQuery = [];

if (!empty($searchFilters['shortcode'])) {
    $filterQuery[] = "shortcode LIKE :shortcode";
    $params['shortcode'] = "%{$searchFilters['shortcode']}%";
}
if (!empty($searchFilters['shortcode_id'])) {
    $filterQuery[] = "shortcode_id LIKE :shortcode_id";
    $params['shortcode_id'] = "%{$searchFilters['shortcode_id']}%";
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
$shortcodes = Query::query($pagedQuery, array_merge($params, [
    "start" => (int)$start,
    "length" => (int)$length
]));

// Prepare response data
$data = [];
foreach ($shortcodes as $shortcode) {
    
    $actions = "<div class='dropdown'>
                <button class='btn btn-sm btn-secondary dropdown-toggle' type='button' data-toggle='dropdown'>Action</button>
                <div class='dropdown-menu dropdown-menu-right'>
                    <a class='dropdown-item edit-shortcode' href='#' data-toggle='modal' data-target='#edit_shortcode_modal' 
                        data-id='" . htmlspecialchars($shortcode['id']) . "' 
                        data-shortcode='" . htmlspecialchars($shortcode['shortcode']) . "' 
                        data-shortcode_id='" . htmlspecialchars($shortcode['shortcode_id']) . "'><b style='color:blue'>Edit</b></a>
                    <a class='dropdown-item delete-shortcode' href='#' data-toggle='modal' data-target='#delete_shortcode_modal'
                        data-id='" . htmlspecialchars($shortcode['id']) . "' 
                        data-shortcode='" . htmlspecialchars($shortcode['shortcode']) . "' 
                        data-shortcode_id='" . htmlspecialchars($shortcode['shortcode_id']) . "'><b style='color:red'>Delete</b></a>
                </div>
            </div>";

    $data[] = [
        $shortcode['shortcode_id'],
        $shortcode['shortcode'],
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
