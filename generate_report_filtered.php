<?php
// Включаем показ ошибок только в лог
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/local-pril/error_log.txt');

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/local-pril/vendor/autoload.php");

use Bitrix\Main\Application;
use PhpOffice\PhpWord\TemplateProcessor;

header('Content-Type: application/json');

// Получаем входные данные (ожидается JSON)
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['status' => 'error', 'message' => 'Неверный формат JSON']);
    exit;
}

// Извлекаем фильтры напрямую из входных данных
$filters = [
    'inventory_type' => isset($input['inventory_type']) ? trim($input['inventory_type']) : '',
    'location' => isset($input['location']) ? trim($input['location']) : '',
    'inventory_status' => isset($input['inventory_status']) ? trim($input['inventory_status']) : '',
    'company' => isset($input['company']) ? trim($input['company']) : '',
    'ip_address' => isset($input['ip_address']) ? trim($input['ip_address']) : '',
    'pc_name' => isset($input['pc_name']) ? trim($input['pc_name']) : '',
    'responsible_user_id' => isset($input['responsible_user_id']) ? trim($input['responsible_user_id']) : '',
    'fio_nb' => isset($input['fio_nb']) ? trim($input['fio_nb']) : ''
];

$skipCells = isset($input['skip_cells']) ? intval($input['skip_cells']) : 0;

$totalCells = 24;
$connection = Application::getConnection();

// Формируем SQL-запрос с учётом фильтров
$whereClauses = [];
$inventoryIds = null; // Для хранения INVENTORY_ID, полученных из таблиц связей

// Фильтры, которые требуют связи через промежуточные таблицы
$referenceFilters = [
    'inventory_type' => [
        'ref_table' => 'iplus_reference_inventory_types',
        'ref_field' => 'TYPE_NAME',
        'link_table' => 'iplus_inventory_vs_inventory_type',
        'link_field' => 'INVENTORY_TYPE_ID'
    ],
    'location' => [
        'ref_table' => 'iplus_reference_location',
        'ref_field' => 'LOCATION_NAME',
        'link_table' => 'iplus_inventory_vs_location',
        'link_field' => 'LOCATION_ID'
    ],
    'inventory_status' => [
        'ref_table' => 'iplus_reference_inventory_status',
        'ref_field' => 'STATUS_NAME',
        'link_table' => 'iplus_inventory_vs_inventory_status',
        'link_field' => 'INVENTORY_STATUS_ID'
    ],
    'company' => [
        'ref_table' => 'iplus_reference_company',
        'ref_field' => 'COMPANY_NAME',
        'link_table' => 'iplus_inventory_vs_company',
        'link_field' => 'COMPANY_ID'
    ]
];

// Обрабатываем фильтры, связанные через промежуточные таблицы
foreach ($referenceFilters as $inputKey => $refConfig) {
    if ($filters[$inputKey] !== '') {
        // Находим ID в справочной таблице
        $refTable = $refConfig['ref_table'];
        $refField = $refConfig['ref_field'];
        $refValue = $connection->getSqlHelper()->forSql($filters[$inputKey]);
        $refSql = "SELECT ID FROM $refTable WHERE $refField = '$refValue' AND ACTIVE = 1";
        $refResult = $connection->query($refSql);
        $refRow = $refResult->fetch();

        if (!$refRow) {
            echo json_encode(['status' => 'error', 'message' => "Значение '$filters[$inputKey]' для $inputKey не найдено в справочной таблице."]);
            exit;
        }

        $refId = $refRow['ID'];

        // Находим INVENTORY_ID в таблице связей
        $linkTable = $refConfig['link_table'];
        $linkField = $refConfig['link_field'];
        $linkSql = "SELECT INVENTORY_ID FROM $linkTable WHERE $linkField = $refId AND ACTIVE = 1";
        $linkResult = $connection->query($linkSql);
        $linkInventoryIds = [];

        while ($linkRow = $linkResult->fetch()) {
            $linkInventoryIds[] = $linkRow['INVENTORY_ID'];
        }

        if (empty($linkInventoryIds)) {
            echo json_encode(['status' => 'error', 'message' => "Записи с $inputKey '$filters[$inputKey]' не найдены."]);
            exit;
        }

        // Если это первый фильтр, инициализируем массив inventoryIds
        if ($inventoryIds === null) {
            $inventoryIds = $linkInventoryIds;
        } else {
            // Пересекаем с уже имеющимися INVENTORY_ID (логическое AND)
            $inventoryIds = array_intersect($inventoryIds, $linkInventoryIds);
        }

        if (empty($inventoryIds)) {
            echo json_encode(['status' => 'error', 'message' => 'Записи по указанным фильтрам не найдены после пересечения условий.']);
            exit;
        }
    }
}

// Если были фильтры по справочникам, добавляем условие на INVENTORY_ID
if ($inventoryIds !== null) {
    $inventoryIdsStr = implode(',', $inventoryIds);
    $whereClauses[] = "ID IN ($inventoryIdsStr)";
}

// Фильтры, которые применяются напрямую к iplus_inventory
$directFilters = [
    'ip_address' => 'IP',
    'pc_name' => 'PC_NAME',
    'responsible_user_id' => 'RESPONSIBLE_USER_ID',
    'fio_nb' => 'FIO_NB'
];

foreach ($directFilters as $inputKey => $dbField) {
    if ($filters[$inputKey] !== '') {
        if ($inputKey === 'fio_nb') {
            $whereClauses[] = "`$dbField` LIKE '%" . $connection->getSqlHelper()->forSql($filters[$inputKey]) . "%'";
            $whereClauses[] = "`RESPONSIBLE_USER_ID` IS NULL"; // Фильтр "Нет в Битрикс" при заполненном fio_nb
        } else {
            $whereClauses[] = "`$dbField` = '" . $connection->getSqlHelper()->forSql($filters[$inputKey]) . "'";
        }
    }
}

// Проверка: хотя бы один фильтр должен быть указан
if (empty($whereClauses)) {
    echo json_encode(['status' => 'error', 'message' => 'Укажите хотя бы один параметр фильтра.']);
    exit;
}

$whereSql = "WHERE " . implode(" AND ", $whereClauses);

// Определяем количество записей
$countSql = "SELECT COUNT(*) as cnt FROM iplus_inventory $whereSql";
$countRes = $connection->query($countSql);
$countRow = $countRes->fetch();
$totalRecords = intval($countRow['cnt']);

if ($totalRecords === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Записи по указанным фильтрам не найдены.']);
    exit;
}

if (($skipCells + $totalRecords) > $totalCells) {
    echo json_encode(['status' => 'error', 'message' => 'Суммарное количество отступов и записей превышает общее количество ячеек (24).']);
    exit;
}

// Получаем данные
$sql = "SELECT inventory_code, QR FROM iplus_inventory $whereSql ORDER BY inventory_code";
$result = $connection->query($sql);

// Загружаем шаблон документа
$templatePath = $_SERVER['DOCUMENT_ROOT'] . "/local-pril/templates/tanex2140.docx";
if (!file_exists($templatePath)) {
    echo json_encode(['status' => 'error', 'message' => 'Шаблон tanex2140.docx не найден']);
    exit;
}

$templateProcessor = new TemplateProcessor($templatePath);

// Вставляем данные: начинаем с ячейки skip_cells + 1
$currentCell = $skipCells + 1;
$insertedCells = []; // Запомним, в какие ячейки вставлены данные

while ($row = $result->fetch()) {
    // Если записи нет или отсутствуют необходимые поля, пропускаем её
    if (!$row || empty($row['QR']) || empty($row['inventory_code'])) {
        continue;
    }

    // Декодируем строку QR-кода, убирая префикс data:image/...
    $qrBase64 = preg_replace('#^data:image/\\w+;base64,#i', '', $row['QR']);
    $imageData = base64_decode($qrBase64);
    if (!$imageData) {
        continue;
    }

    // Создаём каталог для временных QR-кодов, если его нет
    $tempImageDir = $_SERVER['DOCUMENT_ROOT'] . "/local-pril/qr-codes/";
    if (!is_dir($tempImageDir)) {
        mkdir($tempImageDir, 0777, true);
    }
    $tempImagePath = $tempImageDir . "temp_qr_filtered_" . md5($row['inventory_code'] . $currentCell) . ".png";
    file_put_contents($tempImagePath, $imageData);

    // Если превышен лимит ячеек, прекращаем вставку данных
    if ($currentCell > $totalCells) {
        break;
    }

    // Вставляем инвентарный номер и QR-код в ячейку
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

// Очистим все ячейки, которые не входят в диапазон вставленных значений
for ($i = 1; $i <= $totalCells; $i++) {
    if (!in_array($i, $insertedCells)) {
        $templateProcessor->setValue("cell{$i}_code", '');
        $templateProcessor->setValue("cell{$i}_qr", '');
    }
}

// Проверка прав на запись в директорию для сохранения документа
$outputDir = $_SERVER['DOCUMENT_ROOT'] . "/local-pril/documents/";
if (!is_writable($outputDir)) {
    echo json_encode(['status' => 'error', 'message' => 'Нет прав на запись в директорию']);
    exit;
}

// Используем фиксированное имя файла для перезаписи
$outputDocPath = $outputDir . "document_report_filtered.docx";
$templateProcessor->saveAs($outputDocPath);

// Очистка временных файлов QR-кодов
foreach (glob($tempImageDir . "temp_qr_filtered_*.png") as $tempFile) {
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
}

echo json_encode([
    'status'    => 'success',
    'message'   => 'Отчет с фильтром создан',
    'file_path' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $outputDocPath)
]);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>