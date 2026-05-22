<?php
require_once __DIR__ . '/../../../Config/autoload.php';
require __DIR__ . '/../../../vendor/autoload.php'; // PHPSpreadsheet

use App\Classes\Query;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Column-specific search filters
$filters = [
    'name' => $_GET['column_0'] ?? '',
    'shortcode' => $_GET['column_1'] ?? '',
    'keyword' => $_GET['column_2'] ?? '',
    'min_amount' => $_GET['column_3'] ?? '',
    'status' => $_GET['column_4'] ?? ''
];

// Base SQL query with JOIN
$baseQuery = "FROM winners_selection.cronjob_config 
              LEFT JOIN winners_selection.shortcodes ON shortcodes.shortcode_id = cronjob_config.shortcode";

$sql = "SELECT cronjob_config.id, cronjob_config.name, shortcodes.shortcode, 
               cronjob_config.account, cronjob_config.minimum_amount, cronjob_config.enabled, cronjob_config.description 
        $baseQuery";

// Apply column filters
$params = [];
$filterQuery = [];
if (!empty($filters['name'])) {
    $filterQuery[] = "cronjob_config.name LIKE :name";
    $params['name'] = "%{$filters['name']}%";
}
if (!empty($filters['shortcode'])) {
    $filterQuery[] = "shortcodes.shortcode LIKE :shortcode";
    $params['shortcode'] = "%{$filters['shortcode']}%";
}
if (!empty($filters['keyword'])) {
    $filterQuery[] = "cronjob_config.account LIKE :account";
    $params['account'] = "%{$filters['keyword']}%";
}
if (!empty($filters['min_amount'])) {
    $filterQuery[] = "cronjob_config.minimum_amount >= :min_amount";
    $params['min_amount'] = (float) $filters['min_amount'];
}
if ($filters['status'] === "Active") {
    $filterQuery[] = "cronjob_config.enabled = 1";
} elseif ($filters['status'] === "Inactive") {
    $filterQuery[] = "cronjob_config.enabled = 0";
}

// Combine filters only if at least one exists
$filterString = !empty($filterQuery) ? " WHERE " . implode(" AND ", $filterQuery) : "";

// Fetch all filtered or unfiltered records
$query = "$sql $filterString ORDER BY cronjob_config.id DESC";
$campaigns = Query::query($query, $params);

// If no data found, set a default empty row
if (empty($campaigns)) {
    $campaigns[] = [
        'name' => 'No data available',
        'shortcode' => '',
        'account' => '',
        'minimum_amount' => '',
        'enabled' => ''
    ];
}

// Create Excel file
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->getColumnDimension('A')->setWidth(20);

// Set header row
$headers = ['Campaign Name', 'Shortcode', 'Keyword', 'Min. Amount', 'Status'];
$columnLetters = ['A', 'B', 'C', 'D', 'E'];

// Apply styles to header row
foreach ($columnLetters as $col) {
    $sheet->getStyle($col . '1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], // White text
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '333333']], // Dark gray background
        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]]
    ]);
}

// Set header text
for ($i = 0; $i < count($headers); $i++) {
    $sheet->setCellValue($columnLetters[$i] . '1', $headers[$i]);
}

// Fill data rows
$row = 2;
foreach ($campaigns as $campaign) {
    $sheet->setCellValue("A$row", $campaign['name']);
    $sheet->setCellValue("B$row", $campaign['shortcode']); // Now showing actual shortcode
    $sheet->setCellValue("C$row", $campaign['account']);
    $sheet->setCellValue("D$row", number_format($campaign['minimum_amount'], 2));
    $sheet->setCellValue("E$row", $campaign['enabled'] ? "Active" : "Inactive");
    $row++;
}

// Auto-size columns
foreach ($columnLetters as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Output to browser
$filename = "Campaigns_Export_" . date('Y-m-d') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;

?>