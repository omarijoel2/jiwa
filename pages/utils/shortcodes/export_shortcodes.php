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
    'shortcode_id' => $_GET['column_0'] ?? '',
    'shortcode' => $_GET['column_1'] ?? ''
];

// Base SQL query
$baseQuery = "FROM winners_selection.shortcodes";
$sql = "SELECT id, shortcode, shortcode_id $baseQuery";

// Apply column filters
$params = [];
$filterQuery = [];
if (!empty($filters['shortcode'])) {
    $filterQuery[] = "shortcode LIKE :shortcode";
    $params['shortcode'] = "%{$filters['shortcode']}%";
}
if (!empty($filters['shortcode_id'])) {
    $filterQuery[] = "shortcode_id LIKE :shortcode_id";
    $params['shortcode_id'] = "%{$filters['shortcode_id']}%";
}

// Combine filters only if at least one exists
$filterString = !empty($filterQuery) ? " WHERE " . implode(" AND ", $filterQuery) : "";

// Fetch all filtered or unfiltered records
$query = "$sql $filterString ORDER BY id DESC";
$shortcodes = Query::query($query, $params);

// If no data found, set a default empty row
if (empty($shortcodes)) {
    $shortcodes[] = [
        'Shortcode ID' => 'No data available',
        'shortcode' => '',
    ];
}

// Create Excel file
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set header row
$headers = ['Shortcode ID', 'Shortcode'];
$columnLetters = ['A', 'B'];

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
foreach ($shortcodes as $shortcode) {
    $sheet->setCellValue("A$row", $shortcode['shortcode_id']);
    $sheet->setCellValue("B$row", $shortcode['shortcode']);
    $row++;
}

// Auto-size columns
foreach ($columnLetters as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Output to browser
$filename = "Shortcodes_Export_" . date('Y-m-d') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;
