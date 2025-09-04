<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

function getConnection() {
    return Application::getConnection();
}

function handleQrCode($recordId, $text, $qrCode) {
    $connection = getConnection();

    // Подготовленный запрос для получения ID записи
    $sql = "SELECT ID FROM iplus_inventory WHERE COMMENT = '".$connection->getSqlHelper()->forSql($text)."'";

    try {
        $result = $connection->query($sql);
        if ($record = $result->fetch()) {
            // Обновляем запись
            $updateSql = "UPDATE iplus_inventory SET QR = '".$connection->getSqlHelper()->forSql($qrCode)."' WHERE ID = ".$record['ID'];
            $connection->query($updateSql);

            return ['status' => 'success', 'message' => 'Запись обновлена'];
        } else {
            // Запись не найдена, создаем новую запись
            $insertSql = "INSERT INTO iplus_inventory (COMMENT, QR) VALUES ('".$connection->getSqlHelper()->forSql($text)."', '".$connection->getSqlHelper()->forSql($qrCode)."')";
            $connection->query($insertSql);

            return ['status' => 'success', 'message' => 'Новая запись создана'];
        }
    } catch (SqlQueryException $e) {
        return ['status' => 'error', 'message' => 'Ошибка выполнения SQL: ' . $e->getMessage()];
    }
}

// Обработка входящих данных
$requestData = json_decode(file_get_contents('php://input'), true);

if (isset($requestData['record_id'], $requestData['text'], $requestData['qr_code'])) {
    $recordId = $requestData['record_id'];
    $text = $requestData['text'];
    $qrCode = $requestData['qr_code'];

    $response = handleQrCode($recordId, $text, $qrCode);
    echo json_encode($response);
} else {
    echo json_encode(['status' => 'error', 'message' => 'QR-код не передан']);
}
