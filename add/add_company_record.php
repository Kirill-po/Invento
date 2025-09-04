<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json; charset=utf-8');

// Лог-файл
$logFile = $_SERVER['DOCUMENT_ROOT'] . "/local-pril/log.txt";
file_put_contents($logFile, date("Y-m-d H:i:s") . " | Запрос на добавление компании от: " . $_SERVER['REMOTE_ADDR'] . "\n", FILE_APPEND);

if (!$USER->IsAuthorized()) {
    $error = "Ошибка: пользователь не авторизован";
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

use Bitrix\Main\Loader;
use Iplus\Reference\CompanyTable;

if (!Loader::includeModule('main')) {
    $error = "Ошибка: модуль main не установлен";
    file_put_contents($logFile, $error . "\n", FILE_APPEND);
    echo json_encode(["error" => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fieldValue'])) {
    $companyName = trim($_POST['fieldValue']);

    if (empty($companyName)) {
        $error = "Ошибка: название компании не может быть пустым";
        file_put_contents($logFile, $error . "\n", FILE_APPEND);
        echo json_encode(["error" => $error], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $result = CompanyTable::add([
            'fields' => [
                'COMPANY_NAME' => $companyName,
                'ACTIVE' => '1', // По умолчанию активная компания (используем 'Y' для D7)
            ],
        ]);

        if ($result->isSuccess()) {
            file_put_contents($logFile, "Компания добавлена успешно: " . $companyName . "\n", FILE_APPEND);
            echo json_encode(["success" => true, "message" => "Компания успешно добавлена", "id" => $result->getId()], JSON_UNESCAPED_UNICODE);
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