<?php
require_once __DIR__ . '/../../../Config/autoload.php';
require __DIR__ . '/../../../vendor/autoload.php';

use App\Classes\Query;
use App\Classes\Logger;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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


$pagedQuery = "$sql $filterString ORDER BY transactions.id DESC";
$transactions = Query::query($pagedQuery, $params);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$transactionsHeaderStyle = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '333333'] 
    ],
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'] 
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'FFFFFF'] 
        ]
    ]
];
$sheet->setCellValue('A1', 'MSISDN')->getStyle('A1')->applyFromArray($transactionsHeaderStyle);
$sheet->setCellValue('B1', 'Customer Name')->getStyle('B1')->applyFromArray($transactionsHeaderStyle);
$sheet->setCellValue('C1', 'Shortcode')->getStyle('C1')->applyFromArray($transactionsHeaderStyle);
$sheet->setCellValue('D1', 'Keyword')->getStyle('D1')->applyFromArray($transactionsHeaderStyle);
$sheet->setCellValue('E1', 'Transaction Code')->getStyle('E1')->applyFromArray($transactionsHeaderStyle);
$sheet->setCellValue('F1', 'Amount Won')->getStyle('F1')->applyFromArray($transactionsHeaderStyle);
$sheet->setCellValue('G1', 'Transction Time')->getStyle('G1')->applyFromArray($transactionsHeaderStyle);

$row = 2;

foreach ($transactions as $transaction) {
    $sheet->setCellValue("A{$row}", $transaction['msisdn']);
    $sheet->setCellValue("B{$row}", $transaction['customer_name']);
    $sheet->setCellValue("C{$row}", $transaction['shortcode_value']);
    $sheet->setCellValue("D{$row}", $transaction['keyword']);
    $sheet->setCellValue("E{$row}", $transaction['transaction_code']);
    $sheet->setCellValue("F{$row}", $transaction['amount']);
    $sheet->setCellValue("G{$row}", $transaction['trans_time']);
    $row++;
}

$filename = "Transactions_" . date('Y-m-d') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>