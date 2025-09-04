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

// Получаем ID типа из запроса
$typeId = isset($_GET['id']) ? $_GET['id'] : null;
if (!$typeId || !is_numeric($typeId)) {
    echo json_encode(["error" => "Ошибка: неверный ID типа"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Выполняем SQL-запрос для обновления ACTIVE на 0 через TypeTable
use Iplus\Reference\TypeTable;

try {
    $result = TypeTable::update($typeId, ['ACTIVE' => '0']); // Используем 'N' для D7

    if (!$result->isSuccess()) {
        $errors = $result->getErrors();
        $errorMessage = "Ошибка при обновлении типа: " . implode("; ", array_map(fn($e) => $e->getMessage(), $errors));
        echo json_encode(["error" => $errorMessage], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["status" => "success", "message" => "Тип инвентаря успешно удалён"]);
    }
} catch (\Exception $e) {
    echo json_encode(["error" => "Ошибка при удалении типа: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>