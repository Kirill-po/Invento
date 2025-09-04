<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

use Bitrix\Main\Loader;
use Bitrix\Main\Application;

if (!Loader::includeModule('main')) {
    echo json_encode(["error" => "Ошибка: модуль main не установлен"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Проверяем авторизацию
global $USER;
if (!$USER->IsAuthorized()) {
    echo json_encode(["error" => "Ошибка: пользователь не авторизован"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Получаем ID компании из запроса
$companyId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($companyId <= 0) {
    echo json_encode(["error" => "Ошибка: неверный ID компании"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Подключаемся к базе данных
$connection = Application::getConnection();
$sql = "UPDATE iplus_reference_company SET ACTIVE = 0 WHERE ID = " . $companyId;

try {
    $result = $connection->queryExecute($sql);
    if ($connection->getAffectedRowsCount() > 0) {
        echo json_encode(["status" => "success", "message" => "Компания успешно удалена"], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["error" => "Компания не найдена или уже удалена"], JSON_UNESCAPED_UNICODE);
    }
} catch (\Exception $e) {
    echo json_encode(["error" => "Ошибка при удалении компании: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>
