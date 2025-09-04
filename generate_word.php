<?php
// Включаем показ ошибок только в лог
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Настройка логирования ошибок в файл
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/local-pril/error_log.txt');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Подключаем автозагрузчик Composer, где установлен PHPWord
require_once($_SERVER["DOCUMENT_ROOT"]."/local-pril/vendor/autoload.php");

use Bitrix\Main\Application;
use PhpOffice\PhpWord\TemplateProcessor;

header('Content-Type: application/json');

// Функция логирования в файл
function logMessage($message) {
    $logFile = $_SERVER['DOCUMENT_ROOT'] . "/local-pril/log.txt";
    $date = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[{$date}] {$message}\n", FILE_APPEND);
}

// Читаем входные данные
$input = json_decode(file_get_contents('php://input'), true);
$recordId      = isset($input['record_id']) ? intval($input['record_id']) : 0;
$inventoryCode = isset($input['inventory_code']) ? trim($input['inventory_code']) : '';
$skipCells     = isset($input['skip_cells']) ? intval($input['skip_cells']) : 0;

logMessage("Получен запрос: record_id={$recordId}, inventory_code={$inventoryCode}, skip_cells={$skipCells}");

if (!$recordId || !$inventoryCode) {
    logMessage("Ошибка: отсутствует record_id или inventory_code");
    echo json_encode(['status'=>'error','message'=>'Отсутствует record_id или inventory_code']);
    exit;
}

// Подключаемся к БД, получаем QR-код
$connection = Application::getConnection();
$sql = "SELECT QR FROM iplus_inventory WHERE ID = {$recordId}";
$result = $connection->query($sql);
$row = $result->fetch();
if (!$row || empty($row['QR'])) {
    logMessage("Ошибка: QR-код не найден для record_id={$recordId}");
    echo json_encode(['status'=>'error','message'=>'QR-код не найден']);
    exit;
}
$qrBase64 = $row['QR'];

// Убираем префикс "data:image/png;base64," из строки base64
$base64Str = preg_replace('#^data:image/\w+;base64,#i', '', $qrBase64);
$imageData = base64_decode($base64Str);

if (!$imageData) {
    logMessage("Ошибка: QR-код не декодирован для record_id={$recordId}");
    echo json_encode(['status'=>'error','message'=>'Ошибка при декодировании QR-кода']);
    exit;
}

// Сохраняем QR-код в виде PNG с нормальными размерами
$tempImagePath = $_SERVER['DOCUMENT_ROOT'] . "/local-pril/photos/temp_qr_{$recordId}.png";
$tempDir = dirname($tempImagePath);
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true); // создаём директорию, если её нет
}
file_put_contents($tempImagePath, $imageData);

// Проверка успешности записи файла
if (!file_exists($tempImagePath)) {
    logMessage("Ошибка: Не удалось сохранить QR-код во временный файл. Путь: {$tempImagePath}");
    echo json_encode(['status'=>'error','message'=>'Ошибка при сохранении QR-кода']);
    exit;
}

// Загружаем шаблон TANEX 2140
$templatePath = $_SERVER['DOCUMENT_ROOT'] . "/local-pril/templates/tanex2140.docx";
if (!file_exists($templatePath)) {
    logMessage("Ошибка: Шаблон tanex2140.docx не найден");
    echo json_encode(['status'=>'error','message'=>'Шаблон tanex2140.docx не найден']);
    exit;
}

// Создаём TemplateProcessor
$templateProcessor = new TemplateProcessor($templatePath);

// Очищаем все ячейки перед заполнением, пропуская целевую ячейку
$totalCells = 24;

// Определяем номер ячейки для вставки
$targetCell = $skipCells + 1;
if ($targetCell > $totalCells) {
    logMessage("Ошибка: Пропущено {$skipCells} ячеек, но всего доступно {$totalCells}");
    echo json_encode(['status'=>'error','message'=>"Пропущено {$skipCells} ячеек, а всего доступно {$totalCells}"]);
    exit;
}

// Логируем целевую ячейку
logMessage("Заполняем ячейку №{$targetCell}");

// Заполняем целевую ячейку
$templateProcessor->setValue("cell{$targetCell}_code", $inventoryCode);
$templateProcessor->setImageValue("cell{$targetCell}_qr", [
    'path' => $tempImagePath,
    'width' => 100,  // Подберите размеры для качественного отображения
    'height' => 100,
    'ratio' => true
]);

// Проверка прав на запись в директорию
$outputDir = $_SERVER['DOCUMENT_ROOT']."/local-pril/documents/";
if (!is_writable($outputDir)) {
    logMessage("Ошибка: Нет прав на запись в директорию {$outputDir}");
    echo json_encode(['status'=>'error','message'=>'Нет прав на запись в директорию']);
    exit;
}
for ($i = 1; $i <= $totalCells; $i++) {
    // Пропускаем целевую ячейку
    if ($i != $targetCell) {
        $templateProcessor->setValue("cell{$i}_code", '');
        $templateProcessor->setValue("cell{$i}_qr", '');
    }
}
// Сохраняем результат
$outputDocPath = $_SERVER['DOCUMENT_ROOT']."/local-pril/documents/document_{$recordId}.docx";
$templateProcessor->saveAs($outputDocPath);

// Проверка сохранения документа
if (!file_exists($outputDocPath)) {
    logMessage("Ошибка: Не удалось сохранить DOCX");
    echo json_encode(['status'=>'error','message'=>'Ошибка при сохранении DOCX']);
    exit;
}

// Логируем успешное создание файла
logMessage("Документ успешно создан: {$outputDocPath}");

echo json_encode([
    'status'=>'success',
    'message'=>'Документ создан',
    'file_path'=>"/local-pril/documents/document_{$recordId}.docx"
]);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
