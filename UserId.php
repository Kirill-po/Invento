<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Context;
global $USER;

if (!$USER->IsAuthorized()) {
    echo json_encode(['error' => 'Пользователь не авторизован']);
    exit;
}

$response = [
    'user_id' => $USER->GetID(),
];

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
