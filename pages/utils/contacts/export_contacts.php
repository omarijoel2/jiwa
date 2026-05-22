<?php
require_once __DIR__ . '/../../../Config/autoload.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
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
    'start_date' => date('Y-m-d H:i:s', strtotime($_GET['column_3_a'] . ' -2 hours')) ?? '',
    'end_date' => date('Y-m-d H:i:s', strtotime($_GET['column_3_b'] . ' -2 hours')) ?? '',
    'customer_name' => $_GET['column_4'] ?? '',
    'unhashed_msisdn' => $_GET['column_5'] ?? '',
    'hashed_msisdn' => $_GET['column_6'] ?? ''
];

$baseQuery = "FROM winners_selection.contacts 
              LEFT JOIN shortcodes ON contacts.shortcode = shortcodes.shortcode_id";

$sql = "SELECT contacts.id, shortcodes.shortcode AS shortcode_value, contacts.customer_name, contacts.hashed_msisdn, contacts.unhashed_msisdn,  
        contacts.keyword, contacts.date_added $baseQuery";

$params = [];
$filterQuery = [];

if (!empty($searchFilters['shortcode'])) {
    $filterQuery[] = "shortcodes.shortcode_id LIKE :shortcode";
    $params['shortcode'] = "%{$searchFilters['shortcode']}%";
}
if (!empty($searchFilters['start_date']) && !empty($searchFilters['end_date'])) {
    $filterQuery[] = "contacts.date_added >= :start_date AND contacts.date_added <= :end_date";
    $params['start_date'] = $searchFilters['start_date'];
    $params['end_date'] = $searchFilters['end_date'];
} 


if (!empty($searchFilters['keyword'])) {
    $filterQuery[] = "contacts.keyword LIKE :keyword";
    $params['keyword'] = "%{$searchFilters['keyword']}%";
}

if (!empty($searchFilters['customer_name'])) {
    $filterQuery[] = "contacts.customer_name LIKE :customer_name";
    $params['customer_name'] = "%{$searchFilters['customer_name']}%";
}

if (!empty($searchFilters['unhashed_msisdn'])) {
    $filterQuery[] = "contacts.unhashed_msisdn LIKE :unhashed_msisdn";
    $params['unhashed_msisdn'] = "%{$searchFilters['unhashed_msisdn']}%";
}

if (!empty($searchFilters['hashed_msisdn'])) {
    $filterQuery[] = "contacts.hashed_msisdn = :hashed_msisdn";
    $params['hashed_msisdn'] = $searchFilters['hashed_msisdn'];
}

$filterString = !empty($filterQuery) ? " WHERE " . implode(" AND ", $filterQuery) : "";

$pagedQuery = "$sql $filterString ORDER BY contacts.id DESC";
$contacts = Query::query($pagedQuery, $params);


$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$contactsHeaderStyle = [
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

$sheet->setCellValue('A1', 'MSISDN')->getStyle('A1')->applyFromArray($contactsHeaderStyle);
$sheet->setCellValue('B1', 'Customer Name')->getStyle('B1')->applyFromArray($contactsHeaderStyle);
$sheet->setCellValue('C1', 'Shortcode')->getStyle('C1')->applyFromArray($contactsHeaderStyle);
$sheet->setCellValue('D1', 'Keyword')->getStyle('D1')->applyFromArray($contactsHeaderStyle);
$sheet->setCellValue('E1', 'Hashed MSISDN')->getStyle('E1')->applyFromArray($contactsHeaderStyle);
$sheet->setCellValue('F1', 'Date Added')->getStyle('F1')->applyFromArray($contactsHeaderStyle);

$row = 2;

foreach($contacts as $contact){
    $sheet->setCellValue("A{$row}", $contact['unhashed_msisdn']);
    $sheet->setCellValue("B{$row}", $contact['customer_name']);
    $sheet->setCellValue("C{$row}", $contact['shortcode_value']);
    $sheet->setCellValue("D{$row}", $contact['keyword']);
    $sheet->setCellValue("E{$row}", $contact['hashed_msisdn']);
    $sheet->setCellValue("F{$row}", date('Y-m-d H:i:s', strtotime($contact['date_added'] . ' +2 hours')));
    $row++;
}

$filename = "Contacts_" . date('Y-m-d') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
