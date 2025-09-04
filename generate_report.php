<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/local-pril/error_log.txt');

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/local-pril/vendor/autoload.php");

use Bitrix\Main\Application;
use PhpOffice\PhpWord\TemplateProcessor;

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
$recordIdsStr = isset($input['record_ids']) ? $input['record_ids'] : '';
$skipCells   = isset($input['skip_cells']) ? intval($input['skip_cells']) : 0;

if (empty($recordIdsStr)) {
    echo json_encode(['status' => 'error', 'message' => 'Отсутствуют record_ids']);
    exit;
}
$recordIds = array_map('trim', explode(',', $recordIdsStr));
$totalRecords = count($recordIds);
$totalCells = 24;
if (($skipCells + $totalRecords) > $totalCells) {
    echo json_encode(['status' => 'error', 'message' => 'Суммарное количество отступов и записей превышает общее количество ячеек (24).']);
    exit;
}

$connection = Application::getConnection();
$templatePath = $_SERVER['DOCUMENT_ROOT'] . "/local-pril/templates/tanex2140.docx";
if (!file_exists($templatePath)) {
    echo json_encode(['status' => 'error', 'message' => 'Шаблон tanex2140.docx не найден']);
    exit;
}

$templateProcessor = new TemplateProcessor($templatePath);
$currentCell = $skipCells + 1;
$insertedCells = [];

foreach ($recordIds as $recordId) {
    $recordId = intval($recordId);
    $sql = "SELECT inventory_code, QR FROM iplus_inventory WHERE ID = {$recordId}";
    $result = $connection->query($sql);
    $row = $result->fetch();
    if (!$row || empty($row['QR']) || empty($row['inventory_code'])) {
        continue;
    }
    $qrBase64 = preg_replace('#^data:image/\\w+;base64,#i', '', $row['QR']);
    $imageData = base64_decode($qrBase64);
    if (!$imageData) {
        continue;
    }
    $tempImageDir = $_SERVER['DOCUMENT_ROOT'] . "/local-pril/qr-codes/";
    if (!is_dir($tempImageDir)) {
        mkdir($tempImageDir, 0777, true);
    }
    $tempImagePath = $tempImageDir . "temp_qr_{$recordId}.png";
    file_put_contents($tempImagePath, $imageData);
    if ($currentCell > $totalCells) {
        break;
    }
    $templateProcessor->setValue("cell{$currentCell}_code", $row['inventory_code']);
    $templateProcessor->setImageValue("cell{$currentCell}_qr", [
        'path'   => $tempImagePath,
        'width'  => 100, 
        'height' => 100,
        'ratio'  => true
    ]);
    $insertedCells[] = $currentCell;
    $currentCell++;
}
for ($i = 1; $i <= $totalCells; $i++) {
    if (!in_array($i, $insertedCells)) {
        $templateProcessor->setValue("cell{$i}_code", '');
        $templateProcessor->setValue("cell{$i}_qr", '');
    }
}
$outputDir = $_SERVER['DOCUMENT_ROOT'] . "/local-pril/documents/";
if (!is_writable($outputDir)) {
    echo json_encode(['status' => 'error', 'message' => 'Нет прав на запись в директорию']);
    exit;
}

$outputDocPath = $outputDir . "document_report.docx";
$templateProcessor->saveAs($outputDocPath);

echo json_encode([
    'status'    => 'success',
    'message'   => 'Документ создан',
    'file_path' => "/local-pril/documents/document_report.docx"
]);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>
