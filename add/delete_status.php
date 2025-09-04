<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Подключаем модуль main
use Bitrix\Main\Loader;

if (!Loader::includeModule('main')) {
    echo json_encode(["error" => "Ошибка: модуль main не установлен"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Проверяем авторизацию
if (!$USER->IsAuthorized()) {
    echo json_encode(["error" => "Ошибка: пользователь не авторизован"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Получаем ID статуса из запроса
$statusId = isset($_GET['id']) ? $_GET['id'] : null;
if (!$statusId || !is_numeric($statusId)) {
    echo json_encode(["error" => "Ошибка: неверный ID статуса"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Выполняем SQL-запрос для обновления ACTIVE на 0 через StatusTable
use Iplus\Reference\StatusTable;

try {
    $result = StatusTable::update($statusId, ['ACTIVE' => '0']); // Используем '0' для D7, как указано

    if (!$result->isSuccess()) {
        $errors = $result->getErrors();
        $errorMessage = "Ошибка при обновлении статуса: " . implode("; ", array_map(fn($e) => $e->getMessage(), $errors));
        echo json_encode(["error" => $errorMessage], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["status" => "success", "message" => "Статус успешно удалён"]);
    }
} catch (\Exception $e) {
    echo json_encode(["error" => "Ошибка при удалении статуса: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>