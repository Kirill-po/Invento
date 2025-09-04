<?php
// Разрешаем CORS для тестирования (уберите или настройте для безопасности)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Подключаем Bitrix
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application;
use Bitrix\Main\Loader;

// Подключаем необходимые модули
Loader::includeModule('main');

// Сохраняем токены, если они переданы
if (!empty($_REQUEST['auth'])) {
    file_put_contents('home/bitrix/www/local-pril/tokens.json', json_encode($_REQUEST['auth']));
}

// Получаем ID текущего пользователя
$userId = null;

// Способ 1: Используем CUser для получения текущего пользователя
global $USER;
if (is_object($USER) && $USER->IsAuthorized()) {
    $userId = $USER->GetID();
}

// Способ 2: Проверяем $_REQUEST, если CUser не сработал
//if (empty($userId) && !empty($_REQUEST['user_id'])) {
//    $userId = (int)$_REQUEST['user_id'];
//}

// Проверяем, удалось ли получить ID пользователя
if (empty($userId)) {
    echo '<p class="text-danger">Ошибка: Не удалось определить ID пользователя. Убедитесь, что установка выполняется через Bitrix24.</p>';
    exit;
}

// Вызываем sql_tables.php для создания таблиц
try {
    require_once 'sql_tables.php';
} catch (Exception $e) {
    // Если произошла ошибка при создании таблиц, выводим её (для отладки)
    echo '<p class="text-danger">Ошибка при создании таблиц: ' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}

// После успешного создания таблиц добавляем или обновляем права пользователя в user_permissions
try {
    $connection = Application::getConnection();
    $connection->queryExecute("
        INSERT INTO user_permissions (USER_ID, PERMISSION)
        VALUES (" . (int)$userId . ", 'full')
        ON DUPLICATE KEY UPDATE PERMISSION = 'full'
    ");
} catch (Exception $e) {
    echo '<p class="text-danger">Ошибка при добавлении прав пользователя: ' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Установка приложения</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://api.bitrix24.com/api/v1/"></script>
</head>
<body>
    <div id="bx24-output" class="container mt-3">Проверяем...</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof BX24 === 'undefined') {
                document.getElementById('bx24-output').innerHTML = 
                    '<p class="text-danger">BX24 НЕ загружен! Убедитесь, что страница открыта через Bitrix24.</p>';
                return;
            }

            BX24.init(function() {
                BX24.installFinish(function() {
                    document.getElementById('bx24-output').innerHTML = 
                        '<p class="text-success">Приложение успешно установлено!</p>';
                    // Перенаправляем на главную страницу после установки
                    setTimeout(function() {
                        window.location.href = 'https://predprod.reforma-sk.ru/local-pril/pril.php';
                    }, 1000);
                });
            });
        });
    </script>
</body>
</html>