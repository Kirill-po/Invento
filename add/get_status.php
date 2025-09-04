<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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

// Проверяем параметр show_deleted
$showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === 'true';

$filter = [];
if (!$showDeleted) {
    $filter['ACTIVE'] = '1'; // Показываем только активные записи (используем '1' для D7, как указано)
}

// Подключаем кастомный класс StatusTable
use Iplus\Reference\StatusTable;

$statusList = [];
try {
    // Получаем список статусов через StatusTable
    $statuses = StatusTable::getList([
        'select' => ['ID', 'STATUS_NAME', 'ACTIVE'],
        'filter' => $filter,
        'order' => ['ID' => 'ASC']
    ]);

    while ($status = $statuses->fetch()) {
        $statusList[] = [
            'ID' => $status['ID'],
            'STATUS_NAME' => $status['STATUS_NAME'],
            'ACTIVE' => $status['ACTIVE']
        ];
    }
} catch (\Exception $e) {
    echo json_encode(["error" => "Ошибка при выполнении запроса: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

// Отправляем JSON
echo json_encode($statusList, JSON_UNESCAPED_UNICODE);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>