<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Задачи</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://api.bitrix24.com/api/v1/"></script>
    <style>
        * { scroll-behavior: auto; }
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
        .btn-reject {
            background-color: rgb(220, 0, 67);
            border: none;
            color: white;
        }
        .btn-reject:hover {
            background-color: rgb(200, 0, 57);
        }
        .btn-accept {
            background-color: #28a745;
            border: none;
            color: white;
        }
        .btn-accept:hover {
            background-color: #218838;
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
        .execution-status.pending {
            color: red;
        }
        .execution-status.in-progress {
            color: #ffcc00;
        }
        .execution-status.completed {
            color: green;
        }
        .execution-status.rejected {
            color: #1a1a1a;
        }
        .btn-complete {
            background-color: #00ced1;
            border: none;
            color: white;
        }
        .btn-complete:hover {
            background-color: #00b7b9;
        }
        .record-item.pending {
            border-left: 5px solid red;
        }
        .record-item.in-progress {
            border-left: 5px solid #ffcc00;
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
        <h1>Задачи</h1>
    </div>
    <ul class="records" id="history-records"></ul>
</div>

<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Фильтр задач</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="filter-id" class="form-label">ID задачи</label>
                        <input type="text" class="form-control" id="filter-id" placeholder="Введите ID">
                    </div>
                    <div class="col-md-6">
                        <label for="filter-user" class="form-label">Пользователь</label>
                        <select class="form-select" id="filter-user">
                            <option value="">Все пользователи</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="filter-operation" class="form-label">Тип задачи</label>
                        <select class="form-select" id="filter-operation">
                            <option value="">Все задачи</option>
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
                <h5 class="modal-title" id="historyDetailModalLabel">Детали задачи</h5>
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
        let hasFullPermissions = false;

        if (typeof BX24 === 'undefined') {
            historyRecordsList.innerHTML = '<li>Ошибка: BX24 не загружен. Откройте страницу через Bitrix24.</li>';
            return;
        }

        BX24.init(async function() {
            try {
                const userResult = await new Promise((resolve, reject) => {
                    BX24.callMethod('user.current', {}, result => result.error() ? reject(result.error()) : resolve(result.data()));
                });
                const currentUserId = userResult.ID;

                const permissionsResult = await new Promise((resolve, reject) => {
                    BX24.callMethod('custom.userrules', { action: 'get_permissions', user_id: currentUserId }, result => result.error() ? reject(result.error()) : resolve(result.data()));
                });
                hasFullPermissions = permissionsResult?.result?.permission === 'full';

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

        async function fetchHistory() {
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
                            const rawRecords = result.data().result || [];

                            allRecords = rawRecords.filter(record => {
                                const status = mapExecutionStatus(record.EXECUTION_STATUS);
                                return status === 'В работе' || status === 'At work' || status === 'In Progress';
                            });

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
                                    : hasFullPermissions 
                                        ? `${record.DIRECT_OPERATION_NAME || 'Неизвестная операция'} / ${record.REVERSE_OPERATION_NAME || 'Неизвестная операция'}`
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

        async function refreshRecord(recordId) {
            try {
                const result = await new Promise((resolve, reject) => {
                    BX24.callMethod('custom.getfulloperationshistory', { inventory_id: null, limit: 1000, offset: 0 }, result => result.error() ? reject(result.error()) : resolve(result.data()));
                });

                const updatedRecord = result.result.find(r => r.ID == recordId);
                if (!updatedRecord) {
                    console.error('Запись не найдена:', recordId);
                    throw new Error('Запись не найдена');
                }

                updatedRecord.userName = usersCache[updatedRecord.USER_ID] || 'Неизвестный пользователь';
                updatedRecord.operationName = updatedRecord.NAME_OPERATION && updatedRecord.NAME_OPERATION.trim() 
                    ? updatedRecord.NAME_OPERATION 
                    : hasFullPermissions 
                        ? `${updatedRecord.DIRECT_OPERATION_NAME || 'Неизвестная операция'} / ${updatedRecord.REVERSE_OPERATION_NAME || 'Неизвестная операция'}`
                        : updatedRecord.REVERSE_OPERATION_NAME || updatedRecord.DIRECT_OPERATION_NAME || 'Неизвестная операция';
                updatedRecord.templateBase64 = updatedRecord.PRINTED_FORM_TEMPLATE || null;
                updatedRecord.filledTemplateBase64 = updatedRecord.FILLED_TEMPLATE_BASE64 || null;
                updatedRecord.locationName = updatedRecord.INVENTORY_LOCATION_NAME || 'Не указана';
                updatedRecord.responsibleUserName = updatedRecord.RESPONSIBLE_USER_FULLNAME || 'Не указано';
                updatedRecord.executionStatusMapped = mapExecutionStatus(updatedRecord.EXECUTION_STATUS);

                const recordIndex = allRecords.findIndex(r => r.ID == recordId);
                if (recordIndex !== -1) {
                    if (updatedRecord.executionStatusMapped === 'Completed' || updatedRecord.executionStatusMapped === 'Refused') {
                        allRecords.splice(recordIndex, 1);
                    } else {
                        allRecords[recordIndex] = updatedRecord;
                    }
                } else if (updatedRecord.executionStatusMapped === 'Waiting' || updatedRecord.executionStatusMapped === 'At work') {
                    allRecords.push(updatedRecord);
                }

                return updatedRecord;
            } catch (error) {
                console.error('Ошибка обновления записи:', error);
                throw error;
            }
        }

        function setupFilterDropdowns() {
            filterUserSelect.innerHTML = '<option value="">Все пользователи</option>' + Object.entries(usersCache).map(([id, name]) => `<option value="${id}">${name}</option>`).join('');
            filterOperationSelect.innerHTML = '<option value="">Все задачи</option>' + Object.entries(operationsCache).map(([id, op]) => `<option value="${id}">${op.NAME_OPERATION || op.REVERSE_OPERATION_NAME}</option>`).join('');
            filterLocation.innerHTML = '<option value="">Все локации</option>' + Object.entries(locationsCache).map(([id, name]) => `<option value="${id}">${name}</option>`).join('');
        }

        function mapExecutionStatus(status) {
            if (!status) return 'At work';
            switch (status.trim()) {
                case 'В работе':
                case 'At work':
                case 'In Progress':
                    return 'At work';
                case 'Отказано':
                case 'Refused':
                    return 'Refused';
                case 'Выполнена':
                case 'Completed':
                    return 'Completed';
                default:
                    return 'Waiting';
            }
        }

        function parseFormattedDate(dateInput) {
            if (!dateInput || dateInput === '' || (typeof dateInput === 'object' && Object.keys(dateInput).length === 0)) return null;

            let date;

            if (typeof dateInput === 'string' && dateInput.match(/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}$/)) {
                const parts = dateInput.split(/[\.\s:]/);
                date = new Date(parts[2], parts[1] - 1, parts[0], parts[3], parts[4], parts[5]);
            }
            else if (typeof dateInput === 'string' && dateInput.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/)) {
                date = new Date(dateInput.replace(' ', 'T') + 'Z');
            }
            else if (typeof dateInput === 'string' && dateInput.match(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/)) {
                date = new Date(dateInput);
            }
            else {
                date = new Date(dateInput);
            }

            return isNaN(date.getTime()) ? null : date;
        }

        function formatDate(dateInput) {
            const date = parseFormattedDate(dateInput);
            if (!date) return 'Дата не определена';
            return `${String(date.getDate()).padStart(2, '0')}.${String(date.getMonth() + 1).padStart(2, '0')}.${date.getFullYear()} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}:${String(date.getSeconds()).padStart(2, '0')}`;
        }

        function formatActivityStartDate(dateInput, addHours = 0) {
            const date = parseFormattedDate(dateInput);
            if (!date) return 'Дата не определена';
            if (addHours) {
                date.setHours(date.getHours() + addHours);
            }
            return `${String(date.getDate()).padStart(2, '0')}.${String(date.getMonth() + 1).padStart(2, '0')}.${date.getFullYear()} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}:${String(date.getSeconds()).padStart(2, '0')}`;
        }

        function getCurrentDatePlus9Hours() {
            const now = new Date();
            now.setHours(now.getHours() + 5);
            return `${String(now.getDate()).padStart(2, '0')}.${String(now.getMonth() + 1).padStart(2, '0')}.${now.getFullYear()} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`;
        }

        function filterRecords() {
            let filteredRecords = [...allRecords];
            if (filterIdInput.value.trim()) filteredRecords = filteredRecords.filter(r => r.ID == filterIdInput.value.trim());
            if (filterUserSelect.value) filteredRecords = filteredRecords.filter(r => r.USER_ID == filterUserSelect.value);
            if (filterOperationSelect.value) filteredRecords = filteredRecords.filter(r => r.OPERATION_ID == filterOperationSelect.value);
            if (filterExecutionStatusSelect.value) filteredRecords = filteredRecords.filter(r => r.executionStatusMapped == filterExecutionStatusSelect.value);
            if (filterDateStart.value) filteredRecords = filteredRecords.filter(r => parseFormattedDate(r.ACTIVITY_START)?.getTime() >= new Date(filterDateStart.value).getTime());
            if (filterDateEnd.value) filteredRecords = filteredRecords.filter(r => parseFormattedDate(r.ACTIVITY_START)?.getTime() <= new Date(filterDateEnd.value + 'T23:59:59').getTime());
            if (filterLocation.value) filteredRecords = filteredRecords.filter(r => r.INVENTORY_LOCATION_ID == filterLocation.value);
            if (filterInventoryIdInput.value.trim()) filteredRecords = filteredRecords.filter(r => String(r.INVENTORY_ID) === filterInventoryIdInput.value.trim());

            displayRecords(filteredRecords);
            bootstrap.Modal.getInstance(document.getElementById('filterModal'))?.hide();
        }

        function resetFilter() {
            document.querySelectorAll('#filterModal input, #filterModal select').forEach(el => el.value = '');
            displayRecords(allRecords);
        }

        function displayRecords(recordsToShow) {
            historyRecordsList.innerHTML = '';
            if (!recordsToShow || !recordsToShow.length) {
                historyRecordsList.innerHTML = '<li>Нет активных задач</li>';
                return;
            }
            recordsToShow.forEach(record => {
                const li = document.createElement('li');
                li.className = 'record-item';
                li.setAttribute('data-id', record.ID);
                li.classList.add(record.executionStatusMapped === 'Waiting' ? 'pending' : 'in-progress');
                li.innerHTML = `
                    <span>${record.userName || 'Неизвестный пользователь'}</span>
                    <span>${record.operationName || 'Неизвестная задача'}</span>
                    <span>${formatActivityStartDate(record.ACTIVITY_START, 0)}</span>
                `;
                li.addEventListener('click', () => openDetailModal(record));
                historyRecordsList.appendChild(li);
            });
        }

        function downloadBase64File(base64Data, fileName) {
            if (!base64Data) return;
            try {
                const byteArray = new Uint8Array(atob(base64Data).split('').map(c => c.charCodeAt(0)));
                const blob = new Blob([byteArray], { type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = fileName || 'document.docx';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (e) {
                console.error('Ошибка скачивания файла:', e);
                alert('Ошибка при скачивании файла');
            }
        }

        function updateOperationStatus(recordId, newStatus, startDate = null, endDate = null) {
            return new Promise((resolve, reject) => {
                BX24.callMethod(
                    'custom.updateoperationstatus',
                    { id: recordId, execution_status: newStatus, support_activity_start: startDate || null, support_activity_end: endDate || null },
                    result => result.error() ? reject(result.error()) : resolve(result.data())
                );
            });
        }

        function openDetailModal(record) {
            const content = document.getElementById('history-detail-content');
            const executionStatusElement = document.getElementById('execution-status');
            const modalFooter = document.getElementById('history-detail-footer');

            let status = record.executionStatusMapped || 'Waiting';
            executionStatusElement.textContent = status === 'Waiting' ? 'В ожидании' : 'В работе';
            executionStatusElement.className = 'execution-status ' + (status === 'Waiting' ? 'pending' : 'in-progress');

            let buttonsHtml = '';
            if (hasFullPermissions) {
                if (status === 'Waiting') {
                    buttonsHtml = `
                        <button type="button" class="btn btn-accept" id="acceptBtn">Принять</button>
                        <button type="button" class="btn btn-reject" id="rejectBtn">Отказать</button>
                    `;
                } else if (status === 'At work') {
                    buttonsHtml = `
                        <button type="button" class="btn btn-complete" id="completeBtn">Завершить</button>
                        <button type="button" class="btn btn-reject" id="rejectBtn">Отказать</button>
                    `;
                }
            }
            modalFooter.innerHTML = buttonsHtml;

            const acceptBtn = document.getElementById('acceptBtn');
            const rejectBtn = document.getElementById('rejectBtn');
            const completeBtn = document.getElementById('completeBtn');

            if (acceptBtn) acceptBtn.addEventListener('click', async () => {
                const currentDate = getCurrentDatePlus9Hours();
                try {
                    await updateOperationStatus(record.ID, 'At work', currentDate, null);
                    const updatedRecord = await refreshRecord(record.ID);
                    displayRecords(allRecords);
                    historyDetailModal.hide();
                    openDetailModal(updatedRecord);
                } catch (err) {
                    console.error('Ошибка принятия:', err);
                    alert('Ошибка: ' + err.message);
                }
            });

            if (rejectBtn) rejectBtn.addEventListener('click', async () => {
                const currentDate = getCurrentDatePlus9Hours();
                try {
                    await updateOperationStatus(record.ID, 'Refused', record.SUPPORT_ACTIVITY_START, currentDate);
                    await refreshRecord(record.ID);
                    displayRecords(allRecords);
                    historyDetailModal.hide();
                } catch (err) {
                    console.error('Ошибка отказа:', err);
                    alert('Ошибка: ' + err.message);
                }
            });

            if (completeBtn) completeBtn.addEventListener('click', async () => {
                const currentDate = getCurrentDatePlus9Hours();
                try {
                    await updateOperationStatus(record.ID, 'Completed', record.SUPPORT_ACTIVITY_START, currentDate);

                    if (record.REQUIRE_CONFIRMATION == '1' && record.AFTER_OPERATION_STATUS) {
                        const inventoryId = record.INVENTORY_ID;
                        const newStatusId = parseInt(record.AFTER_OPERATION_STATUS, 10);
                        if (inventoryId && !isNaN(newStatusId)) {
                            await updateInventoryStatus(inventoryId, newStatusId);
                        } else {
                            console.error('Ошибка: Некорректный INVENTORY_ID или AFTER_OPERATION_STATUS', { inventoryId, newStatusId });
                        }
                    }

                    await refreshRecord(record.ID);
                    displayRecords(allRecords);
                    historyDetailModal.hide();
                } catch (err) {
                    console.error('Ошибка завершения:', err);
                    alert('Ошибка: ' + (err.message || 'Неизвестная ошибка'));
                }
            });

            let responsibleField = '';
            if (record.INVENTORY_RESPONSIBLE_USER_ID && record.INVENTORY_RESPONSIBLE_USER_ID != 0) {
                responsibleField = `
                    <p><strong>Ответственный в Bitrix:</strong> ${usersCache[record.INVENTORY_RESPONSIBLE_USER_ID] || 'Неизвестный'} (ID: ${record.INVENTORY_RESPONSIBLE_USER_ID})</p>
                `;
            } else if (record.RESPONSIBLE_USER_FULLNAME && record.RESPONSIBLE_USER_FULLNAME !== 'Не указано') {
                responsibleField = `
                    <p><strong>ФИО ответственного:</strong> ${record.RESPONSIBLE_USER_FULLNAME}</p>
                    <p><strong>Email:</strong> ${record.RESPONSIBLE_USER_EMAIL || 'Не указано'}</p>
                    <p><strong>Номер телефона:</strong> ${record.RESPONSIBLE_USER_NUMBER || 'Не указано'}</p>
                `;
            } else {
                responsibleField = `
                    <p><strong>Ответственный:</strong> Не указан</p>
                `;
            }

            content.innerHTML = `
                <p><strong>ID записи:</strong> ${record.ID || 'Не указано'}</p>
                <p><strong>Пользователь (начал активность):</strong> ${record.userName || 'Неизвестный пользователь'} (ID: ${record.USER_ID || 'Не указано'})</p>
                <p><strong>Задача:</strong> ${record.operationName} (ID: ${record.OPERATION_ID || 'Не указано'})</p>
                <p><strong>Дата начала активности:</strong> ${formatActivityStartDate(record.ACTIVITY_START)}</p>
                <p><strong>Дата начала активности техподдержки:</strong> ${formatDate(record.SUPPORT_ACTIVITY_START)}</p>
                <p><strong>Дата завершения активности техподдержки:</strong> ${formatDate(record.SUPPORT_ACTIVITY_END)}</p>
                <p><strong>Статус операции:</strong> ${record.OPERATION_ACTIVE_STATUS == 1 ? 'Активна' : 'Не активна'}</p>
                <p><strong>Локация инвентаря:</strong> ${record.locationName || 'Не указана'}</p>
                ${responsibleField}
                <p><strong>Ссылка на инвентарь:</strong> ${record.INVENTORY_LINK || 'Не указано'}</p>
                <p><strong>Модель инвентаря:</strong> ${record.INVENTORY_MODEL || 'Не указано'}</p>
                <p><strong>Комментарий:</strong> ${record.COMMENT || 'Не указано'}</p>
                <p><strong>Шаблон задачи:</strong> ${record.templateBase64 ? '<button class="btn btn-primary btn-sm" onclick="downloadBase64File(\'' + record.templateBase64 + '\', \'task_template_' + record.OPERATION_ID + '.docx\')">Скачать шаблон (.docx)</button>' : 'Шаблон отсутствует'}</p>
                <p><strong>Заполненный шаблон:</strong> ${record.filledTemplateBase64 ? '<button class="btn btn-success btn-sm" onclick="downloadBase64File(\'' + record.filledTemplateBase64 + '\', \'filled_template_' + record.ID + '.docx\')">Скачать заполненный шаблон (.docx)</button>' : 'Заполненный шаблон отсутствует'}</p>
            `;
            historyDetailModal.show();
        }

        window.downloadBase64File = downloadBase64File;
        window.resetFilter = resetFilter;
    });

    function updateInventoryStatus(inventoryId, newStatusId) {
        return new Promise((resolve, reject) => {
            BX24.callMethod(
                'custom.updateiplusinventorystatus',
                { id: inventoryId, status_id: newStatusId },
                result => {
                    if (result.error()) {
                        console.error('Ошибка сервера:', result.error());
                        reject(result.error());
                    } else {
                        resolve(result.data());
                    }
                }
            );
        });
    }
</script>
</body>
</html>