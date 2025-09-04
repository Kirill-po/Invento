<?php
$targetDir = __DIR__ . '/photos/';

// Проверяем, существует ли папка
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// Получаем данные фото
$data = json_decode(file_get_contents('php://input'), true);
$photoData = $data['photo'];
$photoData = str_replace('data:image/png;base64,', '', $photoData);
$photoData = str_replace(' ', '+', $photoData);
$photoBinary = base64_decode($photoData);
$fileName = uniqid('photo_') . '.png';
$filePath = $targetDir . $fileName;

// Сохраняем файл
if (file_put_contents($filePath, $photoBinary)) {
    echo json_encode(['status' => 'success', 'file' => $fileName]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Не удалось сохранить фото']);
}
?>