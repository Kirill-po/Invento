<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json; charset=utf-8');

// Лог-файл
$logFile = $_SERVER['DOCUMENT_ROOT'] . "/local-pril/log.txt";
file_put_contents($logFile, date("Y-m-d H:i:s") . " | Запрос на добавление локации от: " . $_SERVER['REMOTE_ADDR'] . "\n", FILE_APPEND);

if (!$USER->IsAuthorized()) {
    $error = "Ошибка: пользователь не авторизован";
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

use Bitrix\Main\Loader;
use Iplus\Reference\LocationTable;

if (!Loader::includeModule('main')) {
    $error = "Ошибка: модуль main не установлен";
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fieldValue'])) {
    $locationName = trim($_POST['fieldValue']);

    if (empty($locationName)) {
        $error = "Ошибка: название локации не может быть пустым";
        file_put_contents($logFile, $error . "\n", FILE_APPEND);
        echo json_encode(["error" => $error], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $result = LocationTable::add([
            'fields' => [
                'LOCATION_NAME' => $locationName,
                'ACTIVE' => '1', // По умолчанию активная локация (используем '1' для D7, как указано)
            ],
        ]);

        if ($result->isSuccess()) {
            file_put_contents($logFile, "Локация добавлена успешно: " . $locationName . "\n", FILE_APPEND);
            echo json_encode(["success" => true, "message" => "Локация добавлена успешно", "id" => $result->getId()], JSON_UNESCAPED_UNICODE);
        } else {
            $errors = $result->getErrorMessages();
            $error = "Ошибка добавления: " . implode("; ", $errors);
            file_put_contents($logFile, $error . "\n", FILE_APPEND);
            echo json_encode(["error" => $error], JSON_UNESCAPED_UNICODE);
        }
    } catch (\Exception $e) {
        $error = "Ошибка сервера: " . $e->getMessage();
        file_put_contents($logFile, $error . "\n", FILE_APPEND);
        echo json_encode(["error" => $error], JSON_UNESCAPED_UNICODE);
    }
} else {
    $error = "Ошибка: неверный запрос";
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error], JSON_UNESCAPED_UNICODE);
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>