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
    $filter['ACTIVE'] = 'Y'; // Показываем только активные записи (используем 'Y' вместо '1' для D7)
}

// Подключаем кастомный класс CompanyTable
use Iplus\Reference\CompanyTable;

$companyList = [];
try {
    // Получаем список компаний через CompanyTable
    $companies = CompanyTable::getList([
        'select' => ['ID', 'COMPANY_NAME', 'ACTIVE'],
        'filter' => $filter,
        'order' => ['ID' => 'ASC']
    ]);

    while ($company = $companies->fetch()) {
        $companyList[] = [
            'ID' => $company['ID'],
            'COMPANY_NAME' => $company['COMPANY_NAME'],
            'ACTIVE' => $company['ACTIVE']
        ];
    }
} catch (\Exception $e) {
    echo json_encode(["error" => "Ошибка при выполнении запроса: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

// Отправляем JSON
echo json_encode($companyList, JSON_UNESCAPED_UNICODE);

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>