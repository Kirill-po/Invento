<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>История операций</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://api.bitrix24.com/api/v1/"></script>
    <style>
        * {
            scroll-behavior: auto;
        }
        @font-face {
            font-family: 'Gilroy-Light';
            src: url('https://predprod.reforma-sk.ru/local-pril/Gilroy-Light.otf') format('opentype');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'Gilroy-Light', sans-serif;
            color: rgb(29, 25, 84);
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        .controls {
            position: fixed;
            top: 20px;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            padding: 0 20px;
            z-index: 1000;
            gap: 10px;
        }
        .back-button {
            display: inline-block;
            color: #000000;
            text-decoration: none;
            font-size: clamp(16px, 2vw, 20px);
            border: 2px solid #e50045;
            padding: 10px 15px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .back-button:hover {
            background-color: #d0003f;
            color: #fff;
        }
        .filter-button {
            display: inline-block;
            color: rgb(29, 25, 84);
            text-decoration: none;
            font-size: clamp(16px, 2vw, 20px);
            border: 2px solid rgb(29, 25, 84);
            padding: 10px 15px;
            border-radius: 5px;
            transition: 0.3s;
            background-color: transparent;
            cursor: pointer;
        }
        .filter-button:hover {
            background-color: rgb(29, 25, 84);
            color: #fff;
        }
        .container {
            width: 95%;
            max-width: 600px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 80px;
            margin-bottom: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .records {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .record-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            border: 2px solid rgb(29, 25, 84);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 10px;
            font-size: 18px;
            cursor: pointer;
        }
        .record-item:hover {
            background-color: #f8f9fa;
        }
        @media (max-width: 768px) {
            .controls {
                display: flex;
                align-items: center;
                justify-content: flex-start;
                width: 100%;
                padding: 0px 10px;
                gap: 10px;
            }
            .back-button,
            .filter-button {
                font-size: 12px;
                padding: 6px 12px;
                border-radius: 6px;
                min-width: 60px;
                text-align: center;
                margin: 0 5px;
            }
            .container {
                width: 95%;
                margin-top: 80px;
                padding: 10px;
            }
        }
        .modal-content {
            border-radius: 10px;
            padding: 20px;
        }
        .modal-body {
            font-size: 16px;
        }
        .modal-body p {
            margin-bottom: 10px;
        }
        .modal-lg {
            max-width: 800px;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .execution-status {
            font-size: 16px;
            margin: 0 10px;
        }
        .execution-status.completed {
            color: green;
        }
        .execution-status.rejected {
            color: #1a1a1a;
        }
        .record-item.completed {
            border-left: 5px solid green;
        }
        .record-item.rejected {
            border-left: 5px solid #1a1a1a;
        }
    </style>
</head>
<body>
<div class="controls">
    <a class="back-button" href="pril.php">НАЗАД</a>
    <button class="filter-button" data-bs-toggle="modal" data-bs-target="#filterModal">Фильтр</button>
</div>

<div class="container">
    <div class="header">
        <h1>История операций</h1>
    </div>
    <ul class="records" id="history-records"></ul>
</div>

<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Фильтр операций</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="filter-id" class="form-label">ID операции</label>
                        <input type="text" class="form-control" id="filter-id" placeholder="Введите ID">
                    </div>
                    <div class="col-md-6">
                        <label for="filter-user" class="form-label">Пользователь</label>
                        <select class="form-select" id="filter-user">
                            <option value="">Все пользователи</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="filter-operation" class="form-label">Тип операции</label>
                        <select class="form-select" id="filter-operation">
                            <option value="">Все операции</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="filter-execution-status" class="form-label">Статус выполнения</label>
                        <select class="form-select" id="filter-execution-status">
                            <option value="">Все статусы</option>
                            <option value="Completed">Завершено</option>
                            <option value="Refused">Отказано</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="filter-date-start" class="form-label">Дата начала</label>
                        <input type="date" class="form-control" id="filter-date-start">
                    </div>
                    <div class="col-md-6">
                        <label for="filter-date-end" class="form-label">Дата окончания</label>
                        <input type="date" class="form-control" id="filter-date-end">
                    </div>
                    <div class="col-md-6">
                        <label for="filter-location" class="form-label">Локация</label>
                        <select class="form-select" id="filter-location">
                            <option value="">Все локации</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="filter-inventory-id" class="form-label">ID инвентаря</label>
                        <input type="text" class="form-control" id="filter-inventory-id" placeholder="Введите ID инвентаря">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-danger" onclick="resetFilter()">Сбросить</button>
                <button type="button" class="btn btn-primary" id="apply-filter">Применить</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="historyDetailModal" tabindex="-1" aria-labelledby="historyDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyDetailModalLabel">Детали операции</h5>
                <span id="execution-status" class="execution-status"></span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="history-detail-content"></div>
            <div class="modal-footer" id="history-detail-footer"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const historyRecordsList = document.getElementById('history-records');
        const historyDetailModal = new bootstrap.Modal(document.getElementById('historyDetailModal'));
        const applyFilterButton = document.getElementById('apply-filter');
        const filterIdInput = document.getElementById('filter-id');
        const filterUserSelect = document.getElementById('filter-user');
        const filterOperationSelect = document.getElementById('filter-operation');
        const filterExecutionStatusSelect = document.getElementById('filter-execution-status');
        const filterDateStart = document.getElementById('filter-date-start');
        const filterDateEnd = document.getElementById('filter-date-end');
        const filterLocation = document.getElementById('filter-location');
        const filterInventoryIdInput = document.getElementById('filter-inventory-id');

        let allRecords = [];
        let usersCache = {};
        let operationsCache = {};
        let locationsCache = {};

        if (typeof BX24 === 'undefined') {
            historyRecordsList.innerHTML = '<li>Ошибка: BX24 не загружен. Откройте страницу через Bitrix24.</li>';
            return;
        }

        BX24.init(async function() {
            try {
                const userResult = await new Promise((resolve, reject) => {
                    BX24.callMethod('user.current', {}, function(result) {
                        if (result.error()) reject(result.error());
                        else resolve(result.data());
                    });
                });
                const currentUserId = userResult.ID;

                await fetchHistory();

                setupFilterDropdowns();

                const urlParams = new URLSearchParams(window.location.search);
                const inventoryIdFromUrl = urlParams.get('inventory_id');
                if (inventoryIdFromUrl) {
                    filterInventoryIdInput.value = inventoryIdFromUrl;
                    filterRecords();
                } else {
                    displayRecords(allRecords);
                }

                applyFilterButton.addEventListener('click', filterRecords);
            } catch (err) {
                console.error('Ошибка инициализации:', err);
                historyRecordsList.innerHTML = '<li>Ошибка инициализации: ' + err.message + '</li>';
            }
        });

        function fetchHistory() {
            return new Promise((resolve, reject) => {
                const urlParams = new URLSearchParams(window.location.search);
                const inventoryId = urlParams.get('inventory_id') || null;

                BX24.callMethod(
                    'custom.getfulloperationshistory',
                    { 
                        inventory_id: inventoryId ? parseInt(inventoryId) : null, 
                        limit: 1000, 
                        offset: 0, 
                        _t: new Date().getTime() 
                    },
                    function(result) {
                        if (result.error()) {
                            console.error('Ошибка получения истории:', result.error());
                            historyRecordsList.innerHTML = '<li>Ошибка получения данных: ' + result.error().message + '</li>';
                            reject(result.error());
                        } else {
                            allRecords = result.data().result || [];
                            allRecords = allRecords.filter(record => 
                                record.EXECUTION_STATUS === 'Completed' || record.EXECUTION_STATUS === 'Refused'
                            );

                            allRecords.forEach(record => {
                                if (record.USER_ID) {
                                    usersCache[record.USER_ID] = `${record.USER_LAST_NAME || ''} ${record.USER_NAME || ''} ${record.USER_SECOND_NAME || ''}`.trim();
                                }
                                if (record.INVENTORY_RESPONSIBLE_USER_ID && record.INVENTORY_RESPONSIBLE_USER_ID != 0) {
                                    usersCache[record.INVENTORY_RESPONSIBLE_USER_ID] = record.RESPONSIBLE_USER_FULLNAME || 'Не указано';
                                }
                                if (record.OPERATION_ID) {
                                    operationsCache[record.OPERATION_ID] = {
                                        NAME_OPERATION: record.NAME_OPERATION || '',
                                        DIRECT_OPERATION_NAME: record.DIRECT_OPERATION_NAME || 'Неизвестная операция',
                                        REVERSE_OPERATION_NAME: record.REVERSE_OPERATION_NAME || 'Неизвестная операция',
                                        PRINTED_FORM_TEMPLATE: record.PRINTED_FORM_TEMPLATE || null
                                    };
                                }
                                if (record.INVENTORY_LOCATION_ID) {
                                    locationsCache[record.INVENTORY_LOCATION_ID] = record.INVENTORY_LOCATION_NAME || 'Не указана';
                                }

                                let operationName = record.NAME_OPERATION && record.NAME_OPERATION.trim() 
                                    ? record.NAME_OPERATION 
                                    : record.REVERSE_OPERATION_NAME || record.DIRECT_OPERATION_NAME || 'Неизвестная операция';

                                record.userName = usersCache[record.USER_ID] || 'Неизвестный пользователь';
                                record.operationName = operationName;
                                record.templateBase64 = record.PRINTED_FORM_TEMPLATE || null;
                                record.filledTemplateBase64 = record.FILLED_TEMPLATE_BASE64 || null;
                                record.locationName = record.INVENTORY_LOCATION_NAME || 'Не указана';
                                record.responsibleUserName = record.RESPONSIBLE_USER_FULLNAME || 'Не указано';
                                record.executionStatusMapped = mapExecutionStatus(record.EXECUTION_STATUS);
                            });

                            resolve();
                        }
                    }
                );
            });
        }

        function setupFilterDropdowns() {
            const userOptions = Object.entries(usersCache).map(([id, name]) => `<option value="${id}">${name}</option>`);
            filterUserSelect.innerHTML = '<option value="">Все пользователи</option>' + userOptions.join('');

            const operationOptions = Object.entries(operationsCache).map(([id, op]) => {
                const displayName = op.NAME_OPERATION && op.NAME_OPERATION.trim() ? op.NAME_OPERATION : op.REVERSE_OPERATION_NAME;
                return `<option value="${id}">${displayName}</option>`;
            });
            filterOperationSelect.innerHTML = '<option value="">Все операции</option>' + operationOptions.join('');

            const locationOptions = Object.entries(locationsCache).map(([id, name]) => `<option value="${id}">${name}</option>`);
            filterLocation.innerHTML = '<option value="">Все локации</option>' + locationOptions.join('');
        }

        function mapExecutionStatus(status) {
            switch (status) {
                case 'Отказано':
                case 'Refused':
                    return 'Refused';
                case 'Выполнена':
                case 'Completed':
                case 'Завершена':
                    return 'Completed';
                default:
                    return 'Completed';
            }
        }

        function parseFormattedDate(dateInput) {
            if (!dateInput || dateInput === '' || (typeof dateInput === 'object' && Object.keys(dateInput).length === 0)) {
                return null;
            }

            let date;
            if (typeof dateInput === 'string' && dateInput.match(/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}$/)) {
                const [day, month, year, hours, minutes, seconds] = dateInput.split(/[\.\s:]/);
                date = new Date(year, month - 1, day, hours, minutes, seconds);
            } else {
                date = new Date(dateInput);
            }

            return isNaN(date.getTime()) ? null : date;
        }

        function formatDate(dateInput) {
            const date = parseFormattedDate(dateInput);
            if (!date) return 'Дата не определена';

            const formattedDay = String(date.getDate()).padStart(2, '0');
            const formattedMonth = String(date.getMonth() + 1).padStart(2, '0');
            const formattedYear = date.getFullYear();
            const formattedHours = String(date.getHours()).padStart(2, '0');
            const formattedMinutes = String(date.getMinutes()).padStart(2, '0');
            const formattedSeconds = String(date.getSeconds()).padStart(2, '0');

            return `${formattedDay}.${formattedMonth}.${formattedYear} ${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
        }

        function formatActivityStartDate(dateInput) {
            const date = parseFormattedDate(dateInput);
            if (!date) return 'Дата не определена';

            date.setHours(date.getHours() + 9);
            const formattedDay = String(date.getDate()).padStart(2, '0');
            const formattedMonth = String(date.getMonth() + 1).padStart(2, '0');
            const formattedYear = date.getFullYear();
            const formattedHours = String(date.getHours()).padStart(2, '0');
            const formattedMinutes = String(date.getMinutes()).padStart(2, '0');
            const formattedSeconds = String(date.getSeconds()).padStart(2, '0');

            return `${formattedDay}.${formattedMonth}.${formattedYear} ${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
        }

        function filterRecords() {
            const filterId = filterIdInput.value.trim();
            const filterUser = filterUserSelect.value;
            const filterOperation = filterOperationSelect.value;
            const filterExecutionStatus = filterExecutionStatusSelect.value;
            const filterDateStartValue = filterDateStart.value;
            const filterDateEndValue = filterDateEnd.value;
            const filterLocationValue = filterLocation.value;
            const filterInventoryId = filterInventoryIdInput.value.trim();

            let filteredRecords = [...allRecords];

            if (filterId) {
                filteredRecords = filteredRecords.filter(record => record.ID == filterId);
            }
            if (filterUser) {
                filteredRecords = filteredRecords.filter(record => record.USER_ID == filterUser);
            }
            if (filterOperation) {
                filteredRecords = filteredRecords.filter(record => record.OPERATION_ID == filterOperation);
            }
            if (filterExecutionStatus) {
                filteredRecords = filteredRecords.filter(record => record.executionStatusMapped == filterExecutionStatus);
            }
            if (filterDateStartValue) {
                filteredRecords = filteredRecords.filter(record => {
                    const recordDate = parseFormattedDate(record.ACTIVITY_START);
                    return recordDate && recordDate >= new Date(filterDateStartValue);
                });
            }
            if (filterDateEndValue) {
                filteredRecords = filteredRecords.filter(record => {
                    const recordDate = parseFormattedDate(record.ACTIVITY_START);
                    return recordDate && recordDate <= new Date(filterDateEndValue + 'T23:59:59');
                });
            }
            if (filterLocationValue) {
                filteredRecords = filteredRecords.filter(record => record.INVENTORY_LOCATION_ID == filterLocationValue);
            }
            if (filterInventoryId) {
                filteredRecords = filteredRecords.filter(record => String(record.INVENTORY_ID) === filterInventoryId);
            }

            displayRecords(filteredRecords);
            bootstrap.Modal.getInstance(document.getElementById('filterModal'))?.hide();
        }

        function resetFilter() {
            document.querySelectorAll('#filterModal input, #filterModal select').forEach(element => {
                element.value = '';
            });
            displayRecords(allRecords);
        }

        function displayRecords(recordsToShow) {
            historyRecordsList.innerHTML = '';
            if (!recordsToShow || !recordsToShow.length) {
                historyRecordsList.innerHTML = '<li>Нет данных об операциях</li>';
                return;
            }
            recordsToShow.forEach(record => {
                const li = document.createElement('li');
                li.className = 'record-item';
                li.setAttribute('data-id', record.ID);

                const status = record.executionStatusMapped || 'Completed';
                let statusClass = status === 'Completed' ? 'completed' : 'rejected';
                li.classList.add(statusClass);

                li.innerHTML = `
                    <span>${record.userName || 'Неизвестный пользователь'}</span>
                    <span>${record.operationName || 'Неизвестная операция'}</span>
                    <span>${formatActivityStartDate(record.ACTIVITY_START)}</span>
                `;
                li.addEventListener('click', () => openDetailModal(record));
                historyRecordsList.appendChild(li);
            });
        }

        function downloadBase64File(base64Data, fileName) {
            if (!base64Data) return;
            try {
                const byteCharacters = atob(base64Data);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], { type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = fileName || 'document.docx';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (e) {
                console.error('Ошибка при скачивании файла:', e);
                alert('Ошибка при скачивании файла');
            }
        }

        function openDetailModal(record) {
            const content = document.getElementById('history-detail-content');
            const executionStatusElement = document.getElementById('execution-status');
            const modalFooter = document.getElementById('history-detail-footer');

            let status = record.executionStatusMapped || 'Completed';
            executionStatusElement.textContent = status === 'Completed' ? 'Завершено' : 'Отказано';
            executionStatusElement.className = 'execution-status ' + (status === 'Completed' ? 'completed' : 'rejected');

            modalFooter.innerHTML = '';

            let responsibleField = '';
            if (record.INVENTORY_RESPONSIBLE_USER_ID && record.INVENTORY_RESPONSIBLE_USER_ID != 0) {
                responsibleField = `<p><strong>Ответственный в Bitrix:</strong> ${usersCache[record.INVENTORY_RESPONSIBLE_USER_ID] || 'Неизвестный'} (ID: ${record.INVENTORY_RESPONSIBLE_USER_ID})</p>`;
            } else if (record.RESPONSIBLE_USER_FULLNAME && record.RESPONSIBLE_USER_FULLNAME !== 'Не указано') {
                responsibleField = `<p><strong>ФИО ответственного:</strong> ${record.RESPONSIBLE_USER_FULLNAME}</p>`;
            } else {
                responsibleField = `<p><strong>Ответственный в Bitrix:</strong> Не указан</p>`;
            }

            content.innerHTML = `
                <p><strong>ID записи:</strong> ${record.ID || 'Не указано'}</p>
                <p><strong>Пользователь (начал активность):</strong> ${record.userName} (ID: ${record.USER_ID || 'Не указано'})</p>
                <p><strong>Операция:</strong> ${record.operationName} (ID: ${record.OPERATION_ID || 'Не указано'})</p>
                <p><strong>Дата начала активности:</strong> ${formatActivityStartDate(record.ACTIVITY_START)}</p>
                <p><strong>Дата начала активности техподдержки:</strong> ${formatDate(record.SUPPORT_ACTIVITY_START)}</p>
                <p><strong>Дата завершения активности техподдержки:</strong> ${formatDate(record.SUPPORT_ACTIVITY_END)}</p>
                <p><strong>Статус операции:</strong> ${record.OPERATION_ACTIVE_STATUS == 1 ? 'Активна' : 'Не активна'}</p>
                <p><strong>Локация инвентаря:</strong> ${record.locationName}</p>
                ${responsibleField}
                <p><strong>Ссылка на инвентарь:</strong> ${record.INVENTORY_LINK || 'Не указано'}</p>
                <p><strong>Модель инвентаря:</strong> ${record.INVENTORY_MODEL || 'Не указано'}</p>
                <p><strong>Комментарий:</strong> ${record.COMMENT || 'Не указано'}</p>
                <p><strong>Шаблон операции:</strong> ${
                    record.templateBase64
                        ? '<button class="btn btn-primary btn-sm" onclick="downloadBase64File(\'' + record.templateBase64 + '\', \'operation_template_' + record.OPERATION_ID + '.docx\')">Скачать шаблон (.docx)</button>'
                        : 'Шаблон отсутствует'
                }</p>
                <p><strong>Заполненный шаблон:</strong> ${
                    record.filledTemplateBase64
                        ? '<button class="btn btn-success btn-sm" onclick="downloadBase64File(\'' + record.filledTemplateBase64 + '\', \'filled_template_' + record.ID + '.docx\')">Скачать заполненный шаблон (.docx)</button>'
                        : 'Заполненный шаблон отсутствует'
                }</p>
            `;
            historyDetailModal.show();
        }

        window.downloadBase64File = downloadBase64File;
        window.resetFilter = resetFilter;
    });
</script>
</body>
</html>