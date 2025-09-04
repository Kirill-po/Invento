<?php
// Файл: report_filter.php

// Подключаем пролог Bitrix
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Application;

// Получаем соединение с базой
$connection = Application::getConnection();

// Формируем выпадающие списки из справочных таблиц
$references = [
    'inventory_type' => [
        'table' => 'iplus_reference_inventory_types',
        'field' => 'TYPE_NAME'
    ],
    'company' => [
        'table' => 'iplus_reference_company',
        'field' => 'COMPANY_NAME'
    ],
    'location' => [
        'table' => 'iplus_reference_location',
        'field' => 'LOCATION_NAME'
    ],
    'inventory_status' => [
        'table' => 'iplus_reference_inventory_status',
        'field' => 'STATUS_NAME'
    ]
];

$dropdownData = [];
foreach ($references as $fieldKey => $ref) {
    $tableName = $ref['table'];
    $fieldName = $ref['field'];
    $sql = "SELECT DISTINCT {$fieldName} AS value 
            FROM {$tableName} 
            WHERE {$fieldName} IS NOT NULL AND {$fieldName} <> '' 
            AND ACTIVE = 1 
            ORDER BY {$fieldName}";
    $res = $connection->query($sql);
    $values = [];
    while ($row = $res->fetch()) {
        $values[] = $row['value'];
    }
    $dropdownData[$fieldKey] = $values;
}

// Получаем список пользователей Bitrix
$users = [];
$rsUsers = \Bitrix\Main\UserTable::getList([
    'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'],
    'filter' => ['ACTIVE' => 'Y'],
    'order' => ['LAST_NAME' => 'ASC']
]);
while ($user = $rsUsers->fetch()) {
    $fio = trim($user['LAST_NAME'] . ' ' . $user['NAME'] . ' ' . $user['SECOND_NAME']);
    $users[$user['ID']] = $fio;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Генерация отчёта по фильтру</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://api.bitrix24.com/api/v1/"></script>
    <style>
        .btn-amaranth-filled {
            display: block;
            width: 100%;
            padding: 15px 0;
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            background-color: #e50045;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
        }
        #fioNbField {
            display: none;
        }
        /* Дополнительные стили для ограниченного контейнера кнопок */
        .button-container {
            max-width: 400px;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center my-4">Генерация отчёта по фильтру</h1>
        <form id="filterForm">
            <div class="mb-3">
                <label for="inventoryType" class="form-label">Тип инвентаря</label>
                <select id="inventoryType" name="inventory_type" class="form-select">
                    <option value="">Все</option>
                    <?php foreach ($dropdownData['inventory_type'] as $option): ?>
                        <option value="<?= htmlspecialchars($option) ?>"><?= htmlspecialchars($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Местоположение</label>
                <select id="location" name="location" class="form-select">
                    <option value="">Все</option>
                    <?php foreach ($dropdownData['location'] as $option): ?>
                        <option value="<?= htmlspecialchars($option) ?>"><?= htmlspecialchars($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="inventoryStatus" class="form-label">Статус инвентаря</label>
                <select id="inventoryStatus" name="inventory_status" class="form-select">
                    <option value="">Все</option>
                    <?php foreach ($dropdownData['inventory_status'] as $option): ?>
                        <option value="<?= htmlspecialchars($option) ?>"><?= htmlspecialchars($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="company" class="form-label">Компания</label>
                <select id="company" name="company" class="form-select">
                    <option value="">Все</option>
                    <?php foreach ($dropdownData['company'] as $option): ?>
                        <option value="<?= htmlspecialchars($option) ?>"><?= htmlspecialchars($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <div id="responsibleUserField">
                    <label for="responsibleUser" class="form-label">Ответственный пользователь (Bitrix)</label>
                    <select id="responsibleUser" name="responsible_user_id" class="form-select">
                        <option value="">Все</option>
                        <?php foreach ($users as $userId => $fio): ?>
                            <option value="<?= htmlspecialchars($userId) ?>"><?= htmlspecialchars($fio) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input" id="noBitrix" name="no_bitrix">
                    <label class="form-check-label" for="noBitrix">Нет в Битрикс</label>
                </div>
                <div id="fioNbField">
                    <label for="fioNb" class="form-label">ФИО (для "Нет в Битрикс")</label>
                    <input type="text" class="form-control" id="fioNb" name="fio_nb" placeholder="Например, Иванов Иван Иванович">
                </div>
            </div>
            <div class="mb-3">
                <label for="ipAddress" class="form-label">IP адрес</label>
                <input type="text" class="form-control" id="ipAddress" name="ip_address" placeholder="Например, 192.168.1.1">
            </div>
            <div class="mb-3">
                <label for="pcName" class="form-label">Имя ПК</label>
                <input type="text" class="form-control" id="pcName" name="pc_name" placeholder="Например, PC-01">
            </div>
            <div class="mb-3">
                <label for="skipCells" class="form-label">Отступ (skip_cells)</label>
                <input type="number" class="form-control" id="skipCells" name="skip_cells" value="0" min="0" max="24">
            </div>
        </form>
        <!-- Контейнер для кнопок, выровненных вертикально по центру -->
        <div class="d-grid gap-3 my-3 button-container">
            <button type="button" id="generateFilteredReport" class="btn-amaranth-filled">Сгенерировать отчёт</button>
            <a href="pril.php" class="btn-amaranth-filled text-center">Назад</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const noBitrixCheckbox = document.getElementById('noBitrix');
            const responsibleUserField = document.getElementById('responsibleUserField');
            const fioNbField = document.getElementById('fioNbField');
            noBitrixCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    responsibleUserField.style.display = 'none';
                    fioNbField.style.display = 'block';
                } else {
                    responsibleUserField.style.display = 'block';
                    fioNbField.style.display = 'none';
                    document.getElementById('fioNb').value = '';
                    document.getElementById('responsibleUser').value = '';
                }
            });

            document.getElementById('generateFilteredReport').addEventListener('click', function() {
                const formData = new FormData(document.getElementById('filterForm'));
                const requestData = {};
                formData.forEach((value, key) => {
                    if (key !== 'no_bitrix') {
                        requestData[key] = value;
                    }
                });

                const filterFields = ['inventory_type', 'location', 'inventory_status', 'company', 'responsible_user_id', 'ip_address', 'pc_name', 'fio_nb'];
                let hasFilter = false;
                for (let key of filterFields) {
                    if (requestData[key] && requestData[key].trim() !== '') {
                        hasFilter = true;
                        break;
                    }
                }
                if (!hasFilter) {
                    alert('Введите хотя бы один параметр фильтра.');
                    return;
                }

                fetch('/local-pril/generate_report_filtered.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(requestData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Отчёт создан! Файл будет загружен.');
                        const link = document.createElement('a');
                        link.href = data.file_path;
                        link.download = 'document_report_filtered.docx';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        alert('Ошибка: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Ошибка при генерации отчёта.');
                });
            });
        });
    </script>
</body>
</html>