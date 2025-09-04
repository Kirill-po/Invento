<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\UserTable;

Loader::includeModule("main");

$connection = Application::getConnection();
$tblName = 'user_permissions';

header('Content-Type: application/json');

// Получение прав пользователя
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'get_permissions') {
    $userId = (int)$_GET['user_id'];
    $permission = $connection->queryScalar("SELECT PERMISSION FROM {$tblName} WHERE USER_ID = {$userId}");
    if (!$permission) {
        $permission = 'view'; // По умолчанию
    }

    echo json_encode(['user_id' => $userId, 'permission' => $permission]);
    exit;
}

// Проверка доступа пользователя
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'check_access') {
    $userId = (int)$_GET['user_id'];
    $requiredPermission = $_GET['required_permission'];
    $permission = $connection->queryScalar("SELECT PERMISSION FROM {$tblName} WHERE USER_ID = {$userId}");
    if (!$permission) {
        $permission = 'view'; // По умолчанию
    }

    $hasAccess = false;
    if ($permission === 'full') {
        $hasAccess = true;
    } elseif ($permission === 'edit' && in_array($requiredPermission, ['edit', 'view'])) {
        $hasAccess = true;
    } elseif ($permission === 'view' && $requiredPermission === 'view') {
        $hasAccess = true;
    }

    echo json_encode(['user_id' => $userId, 'has_access' => $hasAccess]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Неизвестный запрос"]);
exit;