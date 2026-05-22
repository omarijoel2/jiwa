<?php
require_once __DIR__ . '/../../../Config/autoload.php';
require __DIR__ . '/../../../vendor/autoload.php';
use App\Classes\Query;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

$cronjob_id = $_GET['cronjob_id'] ?? null;
$searchFilters = [
    'condition_name'  => $_GET['column_0'] ?? '',
    'amount_min'      => $_GET['column_1'] ?? '',
    'amount_max'      => $_GET['column_2'] ?? '',
    'winnings'        => $_GET['column_3'] ?? '',
    'winning_percent' => $_GET['column_4'] ?? '',
    'reset_every'     => $_GET['column_5'] ?? '',
    'status'          => $_GET['column_6'] ?? ''
];

if (!$cronjob_id) {
    die("Invalid campaign ID");
}

// Fetch Campaign Details
$campaign = Query::fetchOne("SELECT * FROM winners_selection.cronjob_config WHERE id = :cronjob_id", ['cronjob_id' => $cronjob_id]);
if (!$campaign) {
    die("Campaign not found.");
}

// Build Query with Filters
$sql = "SELECT * FROM winners_selection.winner_conditions WHERE cronjob_id = :cronjob_id";
$params = ['cronjob_id' => $cronjob_id];

// Apply filters if provided
$filterConditions = [];
if (!empty($searchFilters['condition_name'])) {
    $filterConditions[] = "name LIKE :condition_name";
    $params['condition_name'] = "%" . $searchFilters['condition_name'] . "%";
}
if (!empty($searchFilters['amount_min'])) {
    $filterConditions[] = "amount_min >= :amount_min";
    $params['amount_min'] = $searchFilters['amount_min'];
}
if (!empty($searchFilters['amount_max'])) {
    $filterConditions[] = "amount_max <= :amount_max";
    $params['amount_max'] = $searchFilters['amount_max'];
}
if (!empty($searchFilters['winnings'])) {
    $filterConditions[] = "winnings >= :winnings";
    $params['winnings'] = $searchFilters['winnings'];
}
if (!empty($searchFilters['winning_percent'])) {
    $filterConditions[] = "winning_percentage >= :winning_percent";
    $params['winning_percent'] = $searchFilters['winning_percent'];
}
if (!empty($searchFilters['reset_every'])) {
    $filterConditions[] = "reset_every >= :reset_every";
    $params['reset_every'] = $searchFilters['reset_every'];
}
if ($searchFilters['status'] === "Active") {
    $filterConditions[] = "enabled = 1";
} elseif ($searchFilters['status'] === "Inactive") {
    $filterConditions[] = "enabled = 0";
}

// If there are any filters, append them to the query
if (!empty($filterConditions)) {
    $sql .= " AND " . implode(" AND ", $filterConditions);
}

// Fetch Data
$conditions = Query::query($sql, $params);

// Create Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header Styles
$campaignHeaderStyle = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4472C4'] // Blue
    ],
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'] // White text
    ]
];

$conditionHeaderStyle = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '70AD47'] // Green
    ],
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'] // White text
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'] // Black border
        ]
    ]
];

// Add Campaign Details
$sheet->setCellValue('A1', 'Campaign Name:')->getStyle('A1')->applyFromArray($campaignHeaderStyle);
$sheet->setCellValue('B1', $campaign['name']);

$sheet->setCellValue('A2', 'Description:')->getStyle('A2')->applyFromArray($campaignHeaderStyle);
$sheet->setCellValue('B2', $campaign['description']);

$sheet->setCellValue('A3', 'Shortcode / Paybill:')->getStyle('A3')->applyFromArray($campaignHeaderStyle);
$sheet->setCellValue('B3', $campaign['shortcode']);

$sheet->setCellValue('A4', 'Minimum Amount:')->getStyle('A4')->applyFromArray($campaignHeaderStyle);
$sheet->setCellValue('B4', number_format($campaign['minimum_amount'], 2));

$sheet->setCellValue('A5', 'Status:')->getStyle('A5')->applyFromArray($campaignHeaderStyle);
$sheet->setCellValue('B5', ($campaign['enabled'] ? 'Active' : 'Inactive'));

// Add Table Headers
$sheet->setCellValue('A7', 'Winning Conditions')->getStyle('A7')->getFont()->setBold(true);

$headers = ["Condition Name", "Description", "Min Amount", "Max Amount", "Winnings", "Winning %", "Reset Every", "Status"];
$columnIndex = 'A';
$rowIndex = 8; 

foreach ($headers as $header) {
    $cell = $columnIndex . $rowIndex;
    $sheet->setCellValue($cell, $header)
          ->getStyle($cell)
          ->applyFromArray($conditionHeaderStyle);
    $columnIndex++;
}

// Insert Data
$rowIndex = 9; 
foreach ($conditions as $condition) {
    $sheet->setCellValue("A$rowIndex", $condition['name']);
    $sheet->setCellValue("B$rowIndex", $condition['description']);
    $sheet->setCellValue("C$rowIndex", number_format($condition['amount_min'], 2));
    $sheet->setCellValue("D$rowIndex", number_format($condition['amount_max'], 2));
    $sheet->setCellValue("E$rowIndex", number_format($condition['winnings'], 2));
    $sheet->setCellValue("F$rowIndex", number_format($condition['winning_percentage'], 2) . '%');
    $sheet->setCellValue("G$rowIndex", $condition['reset_every']);
    $sheet->setCellValue("H$rowIndex", ($condition['enabled'] ? 'Active' : 'Inactive'));

    $rowIndex++;
}

// Auto-size Columns
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set Filename & Headers
$filename = "Winning_Conditions_for_campaign_" . $campaign['name'] . "_" . date('Y-m-d') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Save & Download
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
