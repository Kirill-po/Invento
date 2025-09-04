<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\UserTable;
use Bitrix\Iblock\ElementTable;

Loader::includeModule("main");
Loader::includeModule("iblock");

$connection = Application::getConnection();
$tblName = 'user_permissions';
$departmentPermissionsTable = 'department_permissions';
$iblockId = 3; // ID инфоблока отделов
header('Content-Type: application/json');
if (!$connection->isTableExists($tblName)) {
    $connection->queryExecute("
        CREATE TABLE {$tblName} (
            USER_ID INT NOT NULL,
            PERMISSION ENUM('view', 'edit', 'full') NOT NULL DEFAULT 'view',
            PRIMARY KEY (USER_ID)
        )
    ");
}
if (!$connection->isTableExists($departmentPermissionsTable)) {
    $connection->queryExecute("
        CREATE TABLE {$departmentPermissionsTable} (
            DEPARTMENT_ID INT NOT NULL,
            PERMISSION ENUM('view', 'edit', 'full') NOT NULL DEFAULT 'view',
            PRIMARY KEY (DEPARTMENT_ID)
        )
    ");
}

$requestMethod = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? $input['action'] ?? '';

switch ($action) {
    case 'get_departments':
        if ($requestMethod === 'GET') {
            $departments = ElementTable::getList([
                'filter' => ['IBLOCK_ID' => $iblockId],
                'select' => ['ID', 'NAME']
            ])->fetchAll();

            foreach ($departments as $department) {
                $existingPermission = $connection->queryScalar("SELECT PERMISSION FROM {$departmentPermissionsTable} WHERE DEPARTMENT_ID = {$department['ID']}");

                if (!$existingPermission) {
                    $connection->queryExecute("INSERT INTO {$departmentPermissionsTable} (DEPARTMENT_ID, PERMISSION) VALUES ({$department['ID']}, 'view')");
                }
            }

            echo json_encode($departments);
            exit;
        }
        break;

    case 'get_users':
        if ($requestMethod === 'GET') {
            $users = UserTable::getList([
                'select' => ['ID', 'NAME', 'LAST_NAME']
            ])->fetchAll();
            echo json_encode($users);
            exit;
        }
        break;

    case 'get_users_by_department':
        if ($requestMethod === 'GET' && isset($_GET['department_id'])) {
            $departmentId = (int)$_GET['department_id'];
            $users = UserTable::getList([
                'filter' => ['UF_DEPARTMENT' => $departmentId],
                'select' => ['ID', 'NAME', 'LAST_NAME']
            ])->fetchAll();
            echo json_encode($users);
            exit;
        }
        break;

    case 'get_permissions':
        if ($requestMethod === 'GET' && isset($_GET['user_id'])) {
            $userId = (int)$_GET['user_id'];
            $permission = $connection->queryScalar("SELECT PERMISSION FROM {$tblName} WHERE USER_ID = {$userId}") ?? 'view';
            echo json_encode(['user_id' => $userId, 'permission' => $permission]);
            exit;
        }
        break;

    case 'update_permission':
        if ($requestMethod === 'POST' && isset($input['user_id'], $input['permission'])) {
            $userId = (int)$input['user_id'];
            $permission = in_array($input['permission'], ['full', 'edit', 'view']) ? $input['permission'] : 'view';
            $connection->queryExecute("INSERT INTO {$tblName} (USER_ID, PERMISSION) VALUES ({$userId}, '{$permission}') ON DUPLICATE KEY UPDATE PERMISSION = '{$permission}'");
            echo json_encode(["status" => "success", "message" => "Права успешно обновлены"]);
            exit;
        }
        break;

    case 'get_users_with_permissions':
        if ($requestMethod === 'GET') {
            $users = UserTable::getList([
                'select' => ['ID', 'NAME', 'LAST_NAME']
            ])->fetchAll();
            
            foreach ($users as &$user) {
                $permission = $connection->queryScalar("SELECT PERMISSION FROM {$tblName} WHERE USER_ID = {$user['ID']}") ?? 'view';
                $user['PERMISSION'] = $permission;
            }
            echo json_encode($users);
            exit;
        }
        break;
    case 'sync_departments_permissions':
        if ($requestMethod === 'POST') {
            // Получаем список всех отделов
            $departments = ElementTable::getList([
                'filter' => ['IBLOCK_ID' => $iblockId],
                'select' => ['ID', 'NAME']
            ])->fetchAll();

            foreach ($departments as $department) {
                $existingPermission = $connection->queryScalar("SELECT PERMISSION FROM {$departmentPermissionsTable} WHERE DEPARTMENT_ID = {$department['ID']}");

                if (!$existingPermission) {
                    $connection->queryExecute("INSERT INTO {$departmentPermissionsTable} (DEPARTMENT_ID, PERMISSION) VALUES ({$department['ID']}, 'view')");
                }
            }

            echo json_encode(["status" => "success", "message" => "Права для отделов синхронизированы"]);
            exit;
        }
        break;
}

echo json_encode(["status" => "error", "message" => "Неизвестный запрос"]);
exit;
