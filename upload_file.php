<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Only POST allowed']);
    exit;
}

if (!isset($_FILES['template_file']) || $_FILES['template_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'File upload error']);
    exit;
}

$operationId = isset($_POST['operation_id']) ? intval($_POST['operation_id']) : 0;
if ($operationId <= 0) {
    echo json_encode(['error' => 'Invalid operation id']);
    exit;
}

if (pathinfo($_FILES['template_file']['name'], PATHINFO_EXTENSION) !== 'docx') {
    echo json_encode(['error' => 'Invalid file format, only .docx allowed']);
    exit;
}

$fileContent = file_get_contents($_FILES['template_file']['tmp_name']);
if ($fileContent === false) {
    echo json_encode(['error' => 'Failed to read file']);
    exit;
}

// Base64-кодируем содержимое файла для безопасного сохранения
$encodedFile = base64_encode($fileContent);

$connection = Application::getConnection();
$helper = $connection->getSqlHelper();

// Экранируем строку
$escapedFile = $helper->forSql($encodedFile);

$sql = "UPDATE iplus_reference_operations 
        SET PRINTED_FORM_TEMPLATE = '{$escapedFile}' 
        WHERE ID = {$operationId}";

$result = $connection->query($sql);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
} else {
    echo json_encode(['error' => 'Database error']);
}
