<?php
require_once __DIR__ . '/../../../Config/autoload.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Ensure PHPSpreadsheet is installed

use App\Classes\Query;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


$searchFilters = [ 
    'campaign_name' => $_GET['column_0'] ?? '',
    'shortcode' => $_GET['column_1'] ?? '',
    'condition_name' => $_GET['column_2'] ?? '',
    'start_date' => isset($_GET['column_3_a']) ? date('Y-m-d H:i:s', strtotime($_GET['column_3_a'] . ' -2 hours')) : '',
    'end_date' => isset($_GET['column_3_b']) ? date('Y-m-d H:i:s', strtotime($_GET['column_3_b'] . ' -2 hours')) : '',
];

$baseQuery = "FROM winners_selection.winners_log 
              LEFT JOIN shortcodes ON winners_log.shortcode = shortcodes.shortcode_id";

$sql = "SELECT winners_log.id, shortcodes.shortcode AS shortcode_value, winners_log.customer_name, winners_log.msisdn, winners_log.transaction_code,  
        winners_log.keyword, winners_log.amount_transacted, winners_log.amount_won, winners_log.cronjob_name, 
        winners_log.condition_name, winners_log.timestamp $baseQuery";

$params = [];
$filterQuery = [];
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

$filterString = !empty($filterQuery) ? " WHERE " . implode(" AND ", $filterQuery) : "";
$pagedQuery = "$sql $filterString ORDER BY winners_log.id DESC";
$winners_logs = Query::query($pagedQuery, $params);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$winnerHeaderStyle = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '333333'] // Dark gray background
    ],
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'] // White text
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'FFFFFF'] 
        ]
    ]
];
$sheet->setCellValue('A1', 'MSISDN')->getStyle('A1')->applyFromArray($winnerHeaderStyle);
$sheet->setCellValue('B1', 'Customer Name')->getStyle('B1')->applyFromArray($winnerHeaderStyle);
$sheet->setCellValue('C1', 'Shortcode')->getStyle('C1')->applyFromArray($winnerHeaderStyle);
$sheet->setCellValue('D1', 'Keyword')->getStyle('D1')->applyFromArray($winnerHeaderStyle);
$sheet->setCellValue('E1', 'Transaction Code')->getStyle('E1')->applyFromArray($winnerHeaderStyle);
$sheet->setCellValue('F1', 'Amount Transacted')->getStyle('F1')->applyFromArray($winnerHeaderStyle);
$sheet->setCellValue('G1', 'Amount Won')->getStyle('G1')->applyFromArray($winnerHeaderStyle);
$sheet->setCellValue('H1', 'Cronjob Name')->getStyle('H1')->applyFromArray($winnerHeaderStyle);
$sheet->setCellValue('I1', 'Condition Name')->getStyle('I1')->applyFromArray($winnerHeaderStyle);
$sheet->setCellValue('J1', 'Timestamp')->getStyle('J1')->applyFromArray($winnerHeaderStyle);

$row = 2;
foreach ($winners_logs as $winners_log) {
    $sheet->setCellValue("A{$row}", $winners_log['msisdn']);
    $sheet->setCellValue("B{$row}", $winners_log['customer_name']);
    $sheet->setCellValue("C{$row}", $winners_log['shortcode_value']);
    $sheet->setCellValue("D{$row}", $winners_log['keyword']);
    $sheet->setCellValue("E{$row}", $winners_log['transaction_code']);
    $sheet->setCellValue("F{$row}", $winners_log['amount_transacted']);
    $sheet->setCellValue("G{$row}", $winners_log['amount_won']);
    $sheet->setCellValue("H{$row}", $winners_log['cronjob_name']);
    $sheet->setCellValue("I{$row}", $winners_log['condition_name']);
    $sheet->setCellValue("J{$row}", date('Y-m-d H:i:s', strtotime($winners_log['timestamp'] . ' +2 hours')));
    $row++;
}

$filename = "Winners_log_" . date('Y-m-d') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
