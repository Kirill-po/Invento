<?php

// Путь к файлу логов
define('LOG_FILE', $_SERVER['DOCUMENT_ROOT'] . '/local-pril/logs/generate_document_log.txt');

// Подключаем зависимости в начале файла
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$vendorPath = $_SERVER["DOCUMENT_ROOT"] . "/local-pril/vendor/autoload.php";
if (!file_exists($vendorPath)) {
    die("Vendor autoload file not found at: {$vendorPath}");
}
require_once($vendorPath);

// Импорт пространства имён в начале файла
use PhpOffice\PhpWord\TemplateProcessor;

// Функция для логирования
function customLog($message) {
    $logFile = LOG_FILE;
    $timestamp = date('Y-m-d H:i:s');
    $logDir = dirname($logFile);

    if (!is_dir($logDir) && !mkdir($logDir, 0755, true)) {
        return "Cannot create log directory: {$logDir}";
    }
    if (!file_exists($logFile) && !touch($logFile)) {
        return "Cannot create log file: {$logFile}";
    }
    if (!is_writable($logFile)) {
        return "Cannot write to log file: {$logFile}";
    }

    file_put_contents($logFile, "[$timestamp] [generate_operation_document] $message\n", FILE_APPEND);
    return null;
}

// Основная функция для генерации документа
function generateOperationDocument($params) {
    customLog("Starting generateOperationDocument");

    // Проверяем параметры
    $requiredParams = [
        'template_path', 'current_date', 'current_time', 'inventory_id', 'inventory_model',
        'inventory_serial_code', 'inventory_code', 'inventory_pc_name', 'inventory_ip',
        'inventory_comment', 'inventory_responsible_fullname', 'inventory_responsible_email',
        'inventory_responsible_number', 'inventory_status_name', 'inventory_status_id',
        'inventory_status_date_of_creation', 'inventory_status_date_of_completion',
        'inventory_location_name', 'inventory_location_id', 'operation_id',
        'operation_user_fullname', 'operation_user_id', 'operation_direct_name',
        'operation_reverse_name', 'operation_operation_id', 'operation_activity_start',
        'operation_execution_status', 'operation_after_status_name', 'operation_after_status_id',
        'operation_support_activity_start', 'operation_support_activity_end', 'operation_comment'
    ];

    foreach ($requiredParams as $param) {
        if (!array_key_exists($param, $params)) {
            customLog("Missing required parameter: {$param}");
            return ['error' => "Missing required parameter: {$param}"];
        }
    }

    $templatePath = $params['template_path'];
    if (empty($templatePath)) {
        customLog("Template path is empty");
        return ['error' => "Template path is empty"];
    }
    if (!file_exists($templatePath)) {
        customLog("Template file not found at: {$templatePath}");
        return ['error' => "Template file not found at: {$templatePath}"];
    }
    if (!is_readable($templatePath)) {
        customLog("Template file at {$templatePath} is not readable");
        return ['error' => "Template file at {$templatePath} is not readable"];
    }
    if (filesize($templatePath) == 0) {
        customLog("Template file is empty at: {$templatePath}");
        return ['error' => "Template file is empty at: {$templatePath}"];
    }

    $outputDir = $_SERVER['DOCUMENT_ROOT'] . '/local-pril/generated_documents/';
    if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true)) {
        customLog("Failed to create output directory: {$outputDir}");
        return ['error' => "Failed to create output directory: {$outputDir}"];
    }
    if (!is_writable($outputDir)) {
        customLog("Output directory {$outputDir} is not writable");
        return ['error' => "Output directory {$outputDir} is not writable"];
    }

    try {
        customLog("Creating TemplateProcessor with template: {$templatePath}");
        $templateProcessor = new TemplateProcessor($templatePath);

        foreach ($params as $key => $value) {
            $value = $value ?? ''; // Защита от NULL
            customLog("Setting value for {$key}: " . substr($value, 0, 50));
            $templateProcessor->setValue($key, $value);
        }

        $outputFileName = 'operation_' . time() . '_' . $params['inventory_id'] . '.docx';
        $outputPath = $outputDir . $outputFileName;
        customLog("Saving document to: {$outputPath}");

        $templateProcessor->saveAs($outputPath);

        if (!file_exists($outputPath) || !is_readable($outputPath)) {
            customLog("Failed to save document at: {$outputPath}");
            return ['error' => "Failed to save document at: {$outputPath}"];
        }

        $fileContent = file_get_contents($outputPath);
        $encodedDocument = base64_encode($fileContent);

        customLog("Document generation successful");
        return [
            'success' => true,
            'document' => $encodedDocument,
            'file_path' => $outputPath
        ];
    } catch (Exception $e) {
        customLog("Error in document generation: " . $e->getMessage() . " at line " . $e->getLine());
        return ['error' => "Document generation failed: " . $e->getMessage()];
    }
}