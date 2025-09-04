<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Мои операции</title>
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
      color: #1D1954;
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
      max-width: 1200px;
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
      padding: 15px;
      margin-bottom: 10px;
      font-size: 16px;
      cursor: pointer;
      gap: 10px;
    }
    .record-item:hover {
      background-color: #f8f9fa;
    }
    .record-item.pending {
      border-left: 5px solid red;
    }
    .record-item.in-progress {
      border-left: 5px solid #ffcc00;
    }
    .record-item span {
      flex: 1;
      text-align: left;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    @media (max-width: 768px) {
      .controls {
        padding: 0 10px;
      }
      .back-button,
      .filter-button {
        font-size: 12px;
        padding: 6px 12px;
        min-width: 60px;
        margin: 0 5px;
      }
      .container {
        width: 95%;
        margin-top: 80px;
        padding: 10px;
      }
      .record-item {
        font-size: 14px;
        flex-wrap: wrap;
      }
      .record-item span {
        flex: 1 1 100%;
        text-align: left;
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
    .btn-accept {
      background-color: #28a745;
      border: none;
      color: white;
    }
    .btn-accept:hover {
      background-color: #218838;
    }
    .btn-reject {
      background-color: rgb(220, 0, 67);
      border: none;
      color: white;
    }
    .btn-reject:hover {
      background-color: rgb(200, 0, 57);
    }
    .btn-complete {
      background-color: #00ced1;
      border: none;
      color: white;
    }
    .btn-complete:hover {
      background-color: #00b7b9;
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
      <h1>Мои операции</h1>
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
              <label for="filter-operation" class="form-label">Тип операции</label>
              <select class="form-select" id="filter-operation">
                <option value="">Все операции</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="filter-status" class="form-label">Статус операции</label>
              <select class="form-select" id="filter-status">
                <option value="">Все статусы</option>
                <option value="Waiting">Ожидает подтверждения</option>
                <option value="At work">В работе</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="filter-inventory-status" class="form-label">Статус инвентаря</label>
              <select class="form-select" id="filter-inventory-status">
                <option value="">Все статусы</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="filter-location" class="form-label">Локация</label>
              <select class="form-select" id="filter-location">
                <option value="">Все локации</option>
              </select>
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

      const filterOperationSelect = document.getElementById('filter-operation');
      const filterStatusSelect = document.getElementById('filter-status');
      const filterInventoryStatus = document.getElementById('filter-inventory-status');
      const filterLocation = document.getElementById('filter-location');

      let allRecords = [];
      let usersCache = {};
      let operationsCache = {};
      let locationsCache = {};
      let statusesCache = {};
      let currentUserId = null;

      if (typeof BX24 === 'undefined') {
        historyRecordsList.innerHTML = '<li>Ошибка: BX24 не загружен. Откройте страницу через Bitrix24.</li>';
        return;
      }

      // Получение ID текущего пользователя
      async function getCurrentUserInfo() {
        return new Promise((resolve, reject) => {
          BX24.callMethod('user.current', {}, function(userResult) {
            if (userResult.error()) {
              console.error('Ошибка получения текущего пользователя:', userResult.error());
              reject(userResult.error());
            } else {
              const user = userResult.data();
              currentUserId = user.ID;
              console.log('Текущий пользователь ID:', currentUserId);
              resolve();
            }
          });
        });
      }

      BX24.init(async function() {
        try {
          await getCurrentUserInfo();
          await fetchUsers();
          await fetchAllOperations();
          await fetchLocations();
          await fetchStatuses();
          await fetchHistory();
          setupFilterDropdowns();
          applyFilterButton.addEventListener('click', filterRecords);
        } catch (err) {
          console.error('Ошибка инициализации:', err);
          historyRecordsList.innerHTML = '<li>Ошибка инициализации: ' + err.message + '</li>';
        }
      });

      function fetchUsers() {
        return new Promise((resolve, reject) => {
          BX24.callMethod('user.get', { ACTIVE: true }, function(result) {
            if (result.error()) {
              reject(result.error());
            } else {
              const users = result.data();
              users.forEach(user => {
                usersCache[user.ID] = `${user.LAST_NAME || ''} ${user.NAME || ''} ${user.SECOND_NAME || ''}`.trim();
              });
              resolve();
            }
          });
        });
      }

      function fetchAllOperations() {
        return new Promise((resolve, reject) => {
          BX24.callMethod('custom.getiplusreferenceoperations', {}, function(result) {
            if (result.error()) {
              reject(result.error());
            } else {
              const operations = result.data().result || [];
              operations.forEach(op => {
                operationsCache[op.ID] = {
                  NAME_OPERATION: op.NAME_OPERATION || '',
                  DIRECT_OPERATION_NAME: op.DIRECT_OPERATION_NAME || 'Неизвестная операция',
                  REVERSE_OPERATION_NAME: op.REVERSE_OPERATION_NAME || 'Неизвестная операция',
                  PRINTED_FORM_TEMPLATE: op.PRINTED_FORM_TEMPLATE || null
                };
              });
              console.log('Кэш операций:', operationsCache);
              resolve();
            }
          });
        });
      }

      function fetchLocations() {
        return new Promise((resolve, reject) => {
          BX24.callMethod('custom.getlocations', {}, function(result) {
            if (result.error()) {
              reject(result.error());
            } else {
              const locations = result.data().result || [];
              locations.forEach(loc => {
                locationsCache[loc.ID] = loc.NAME || 'Не указана';
              });
              console.log('Кэш локаций:', locationsCache);
              resolve();
            }
          });
        });
      }

      function fetchStatuses() {
        return new Promise((resolve, reject) => {
          BX24.callMethod('custom.getiplusreferencestatus', {}, function(result) {
            if (result.error()) {
              reject(result.error());
            } else {
              const statuses = result.data().result || [];
              statuses.forEach(status => {
                statusesCache[status.ID] = status.STATUS_NAME || 'Не указан';
              });
              console.log('Кэш статусов:', statusesCache);
              resolve();
            }
          });
        });
      }

      function fetchHistory() {
        return new Promise((resolve, reject) => {
          BX24.callMethod(
            'custom.getfulloperationshistory',
            { limit: 1000, offset: 0, _t: new Date().getTime() },
            async function(result) {
              if (result.error()) {
                console.error('Ошибка получения истории:', result.error());
                reject(result.error());
              } else {
                let records = result.data().result || [];
                console.log('Сырые данные от сервера:', records);

                // Фильтруем записи: только где пользователь — ответственный (NEW_RESPONSIBLE_USER_ID) и статус активный
                records = records.filter(record => {
                  const isResponsible = record.NEW_RESPONSIBLE_USER_ID == currentUserId;
                  const isTaskActive = ['Waiting', 'At work'].includes(mapExecutionStatus(record.EXECUTION_STATUS));
                  return isResponsible && isTaskActive;
                });
                console.log('Отфильтрованные записи для пользователя:', currentUserId, records);

                allRecords = records;

                for (let record of allRecords) {
                  if (record.USER_ID && !usersCache[record.USER_ID]) {
                    await new Promise((resolveUser) => {
                      BX24.callMethod(
                        'user.get',
                        { ID: record.USER_ID },
                        function(userResult) {
                          if (!userResult.error()) {
                            const user = userResult.data()[0];
                            usersCache[record.USER_ID] = `${user.LAST_NAME || ''} ${user.NAME || ''} ${user.SECOND_NAME || ''}`.trim();
                          }
                          resolveUser();
                        }
                      );
                    });
                  }
                  if (record.NEW_RESPONSIBLE_USER_ID && !usersCache[record.NEW_RESPONSIBLE_USER_ID]) {
                    await new Promise((resolveUser) => {
                      BX24.callMethod(
                        'user.get',
                        { ID: record.NEW_RESPONSIBLE_USER_ID },
                        function(userResult) {
                          if (!userResult.error()) {
                            const user = userResult.data()[0];
                            usersCache[record.NEW_RESPONSIBLE_USER_ID] = `${user.LAST_NAME || ''} ${user.NAME || ''} ${user.SECOND_NAME || ''}`.trim();
                          }
                          resolveUser();
                        }
                      );
                    });
                  }

                  record.userName = usersCache[record.USER_ID] || 'Неизвестный пользователь';
                  record.operationName = record.NAME_OPERATION && record.NAME_OPERATION.trim() 
                    ? record.NAME_OPERATION 
                    : operationsCache[record.OPERATION_ID]?.REVERSE_OPERATION_NAME || 'Неизвестная операция';
                  record.locationName = record.INVENTORY_LOCATION_NAME || locationsCache[record.INVENTORY_LOCATION_ID] || 'Не указана';
                  record.statusName = record.INVENTORY_STATUS_NAME || statusesCache[record.INVENTORY_RECORD_STATUS] || 'Не указан';
                  record.responsibleUserName = usersCache[record.NEW_RESPONSIBLE_USER_ID] || record.RESPONSIBLE_USER_FULLNAME || 'Не указано';
                  record.executionStatusMapped = mapExecutionStatus(record.EXECUTION_STATUS);
                  record.templateBase64 = record.PRINTED_FORM_TEMPLATE || null;
                  record.filledTemplateBase64 = record.FILLED_TEMPLATE_BASE64 || null;
                }
                console.log('Обработанные записи:', allRecords);
                displayRecords(allRecords);
                resolve();
              }
            }
          );
        });
      }

      function setupFilterDropdowns() {
        filterOperationSelect.innerHTML = '<option value="">Все операции</option>' + 
          Object.entries(operationsCache).map(([id, op]) => `<option value="${id}">${op.REVERSE_OPERATION_NAME}</option>`).join('');
        filterLocation.innerHTML = '<option value="">Все локации</option>' + 
          Object.entries(locationsCache).map(([id, name]) => `<option value="${id}">${name}</option>`).join('');
        filterInventoryStatus.innerHTML = '<option value="">Все статусы</option>' + 
          Object.entries(statusesCache).map(([id, name]) => `<option value="${id}">${name}</option>`).join('');
      }

      function parseFormattedDate(dateInput) {
        if (!dateInput || dateInput === '' || (typeof dateInput === 'object' && Object.keys(dateInput).length === 0)) return null;

        let date;
        if (typeof dateInput === 'string' && dateInput.match(/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}$/)) {
          const parts = dateInput.split(/[\.\s:]/);
          date = new Date(parts[2], parts[1] - 1, parts[0], parts[3], parts[4], parts[5]);
        } else if (typeof dateInput === 'string' && dateInput.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/)) {
          date = new Date(dateInput.replace(' ', 'T') + 'Z');
        } else if (typeof dateInput === 'string' && dateInput.match(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/)) {
          date = new Date(dateInput);
        } else {
          date = new Date(dateInput);
        }

        return isNaN(date.getTime()) ? null : date;
      }

      function formatDate(dateInput) {
        const date = parseFormattedDate(dateInput);
        if (!date) return 'Дата не определена';
        return `${String(date.getDate()).padStart(2, '0')}.${String(date.getMonth() + 1).padStart(2, '0')}.${date.getFullYear()} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}:${String(date.getSeconds()).padStart(2, '0')}`;
      }

      function getCurrentDate() {
        const now = new Date();
        return `${String(now.getDate()).padStart(2, '0')}.${String(now.getMonth() + 1).padStart(2, '0')}.${now.getFullYear()} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`;
      }

      function mapExecutionStatus(status) {
        if (!status) return 'Waiting';
        switch (status.trim()) {
          case 'Ожидает подтверждения':
          case 'Waiting':
            return 'Waiting';
          case 'В работе':
          case 'At work':
            return 'At work';
          case 'Отказано':
          case 'Refused':
            return 'Refused';
          case 'Выполнена':
          case 'Completed':
            return 'Completed';
          default:
            console.warn(`Неизвестный статус: "${status}". Устанавливаем по умолчанию 'Waiting'`);
            return 'Waiting';
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

      function updateInventoryStatus(inventoryId, newStatusId) {
        console.log('Обновление статуса инвентаря:', { inventoryId, newStatusId });
        return new Promise((resolve, reject) => {
          BX24.callMethod(
            'custom.updateiplusinventorystatus',
            { id: inventoryId, status_id: newStatusId },
            result => result.error() ? reject(result.error()) : resolve(result.data())
          );
        });
      }

      async function refreshRecord(recordId) {
        try {
          const result = await new Promise((resolve, reject) => {
            BX24.callMethod('custom.getfulloperationshistory', { limit: 1000, offset: 0 }, result => result.error() ? reject(result.error()) : resolve(result.data()));
          });

          const updatedRecord = result.result.find(r => r.ID == recordId);
          if (!updatedRecord) {
            throw new Error('Запись не найдена');
          }

          updatedRecord.userName = usersCache[updatedRecord.USER_ID] || 'Неизвестный пользователь';
          updatedRecord.operationName = updatedRecord.NAME_OPERATION && updatedRecord.NAME_OPERATION.trim() 
            ? updatedRecord.NAME_OPERATION 
            : operationsCache[updatedRecord.OPERATION_ID]?.REVERSE_OPERATION_NAME || 'Неизвестная операция';
          updatedRecord.locationName = updatedRecord.INVENTORY_LOCATION_NAME || locationsCache[updatedRecord.INVENTORY_LOCATION_ID] || 'Не указана';
          updatedRecord.statusName = updatedRecord.INVENTORY_STATUS_NAME || statusesCache[updatedRecord.INVENTORY_RECORD_STATUS] || 'Не указан';
          updatedRecord.responsibleUserName = usersCache[updatedRecord.NEW_RESPONSIBLE_USER_ID] || updatedRecord.RESPONSIBLE_USER_FULLNAME || 'Не указано';
          updatedRecord.executionStatusMapped = mapExecutionStatus(updatedRecord.EXECUTION_STATUS);
          updatedRecord.templateBase64 = updatedRecord.PRINTED_FORM_TEMPLATE || null;
          updatedRecord.filledTemplateBase64 = updatedRecord.FILLED_TEMPLATE_BASE64 || null;

          const recordIndex = allRecords.findIndex(r => r.ID == recordId);
          if (recordIndex !== -1) {
            if (['Completed', 'Refused'].includes(updatedRecord.executionStatusMapped)) {
              allRecords.splice(recordIndex, 1);
            } else {
              allRecords[recordIndex] = updatedRecord;
            }
          } else if (['Waiting', 'At work'].includes(updatedRecord.executionStatusMapped)) {
            allRecords.push(updatedRecord);
          }

          return updatedRecord;
        } catch (error) {
          console.error('Ошибка обновления записи:', error);
          throw error;
        }
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

      function displayRecords(recordsToShow) {
        historyRecordsList.innerHTML = '';
        if (!recordsToShow || !recordsToShow.length) {
          historyRecordsList.innerHTML = '<li>Нет активных операций</li>';
          return;
        }
        recordsToShow.forEach(record => {
          const li = document.createElement('li');
          li.className = 'record-item';
          li.setAttribute('data-id', record.ID);
          li.classList.add(record.executionStatusMapped === 'Waiting' ? 'pending' : 'in-progress');
          li.innerHTML = `
            <span>${record.userName}</span>
            <span>${record.operationName}</span>
            <span>${formatDate(record.ACTIVITY_START)}</span>
            <span>${record.locationName}</span>
            <span>${record.statusName}</span>
            <span>${record.responsibleUserName}</span>
          `;
          historyRecordsList.appendChild(li);
          li.addEventListener('click', function() {
            openDetailModal(record);
          });
        });
      }

      function openDetailModal(record) {
        const content = document.getElementById('history-detail-content');
        const executionStatusElement = document.getElementById('execution-status');
        const modalFooter = document.getElementById('history-detail-footer');

        const status = record.executionStatusMapped || 'Waiting';
        executionStatusElement.textContent = status === 'Waiting' ? 'Ожидает подтверждения' : 'В работе';
        executionStatusElement.className = 'execution-status ' + (status === 'Waiting' ? 'pending' : 'in-progress');

        let responsibleField = '';
        if (record.NEW_RESPONSIBLE_USER_ID && record.NEW_RESPONSIBLE_USER_ID != 0) {
          responsibleField = `
            <p><strong>Ответственный в Bitrix:</strong> ${usersCache[record.NEW_RESPONSIBLE_USER_ID] || 'Неизвестный'} (ID: ${record.NEW_RESPONSIBLE_USER_ID})</p>
          `;
        } else if (record.RESPONSIBLE_USER_FULLNAME && record.RESPONSIBLE_USER_FULLNAME !== 'Не указано') {
          responsibleField = `
            <p><strong>ФИО ответственного:</strong> ${record.RESPONSIBLE_USER_FULLNAME}</p>
            <p><strong>Email:</strong> ${record.RESPONSIBLE_USER_EMAIL || 'Не указано'}</p>
            <p><strong>Номер телефона:</strong> ${record.RESPONSIBLE_USER_NUMBER || 'Не указано'}</p>
          `;
        } else {
          responsibleField = `<p><strong>Ответственный:</strong> Не указан</p>`;
        }

        content.innerHTML = `
          <p><strong>ID записи:</strong> ${record.ID || 'Не указано'}</p>
          <p><strong>Инициатор:</strong> ${record.userName} (ID: ${record.USER_ID || 'Не указано'})</p>
          <p><strong>Операция:</strong> ${record.operationName} (ID: ${record.OPERATION_ID || 'Не указано'})</p>
          <p><strong>Дата начала активности:</strong> ${formatDate(record.ACTIVITY_START)}</p>
          <p><strong>Дата начала активности техподдержки:</strong> ${formatDate(record.SUPPORT_ACTIVITY_START)}</p>
          <p><strong>Дата завершения активности техподдержки:</strong> ${formatDate(record.SUPPORT_ACTIVITY_END)}</p>
          <p><strong>Статус операции:</strong> ${record.OPERATION_ACTIVE_STATUS == 1 ? 'Активна' : 'Не активна'}</p>
          <p><strong>Статус инвентаря:</strong> ${record.statusName}</p>
          <p><strong>Локация инвентаря:</strong> ${record.locationName}</p>
          ${responsibleField}
          <p><strong>Ссылка на инвентарь:</strong> ${record.INVENTORY_LINK || 'Не указано'}</p>
          <p><strong>Модель инвентаря:</strong> ${record.MODEL || record.INVENTORY_MODEL || 'Не указано'}</p>
          <p><strong>Комментарий:</strong> ${record.COMMENT || 'Не указано'}</p>
          <p><strong>Шаблон операции:</strong> ${record.templateBase64 ? '<button class="btn btn-primary btn-sm" onclick="downloadBase64File(\'' + record.templateBase64 + '\', \'task_template_' + record.OPERATION_ID + '.docx\')">Скачать шаблон (.docx)</button>' : 'Шаблон отсутствует'}</p>
          <p><strong>Заполненный шаблон:</strong> ${record.filledTemplateBase64 ? '<button class="btn btn-success btn-sm" onclick="downloadBase64File(\'' + record.filledTemplateBase64 + '\', \'filled_template_' + record.ID + '.docx\')">Скачать заполненный шаблон (.docx)</button>' : 'Заполненный шаблон отсутствует'}</p>
        `;

        let buttonsHtml = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>';
        if (record.NEW_RESPONSIBLE_USER_ID == currentUserId) {
          if (status === 'Waiting') {
            buttonsHtml += `
              <button type="button" class="btn btn-accept" id="acceptBtn">Принять</button>
              <button type="button" class="btn btn-reject" id="rejectBtn">Отказать</button>
            `;
          } else if (status === 'At work') {
            buttonsHtml += `
              <button type="button" class="btn btn-complete" id="completeBtn">Завершить</button>
              <button type="button" class="btn btn-reject" id="rejectBtn">Отказать</button>
            `;
          }
        }

        modalFooter.innerHTML = buttonsHtml;

        const acceptBtn = document.getElementById('acceptBtn');
        const rejectBtn = document.getElementById('rejectBtn');
        const completeBtn = document.getElementById('completeBtn');

        if (acceptBtn) {
          acceptBtn.addEventListener('click', async () => {
            try {
              const currentDate = getCurrentDate();
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
        }

        if (rejectBtn) {
          rejectBtn.addEventListener('click', async () => {
            try {
              const currentDate = getCurrentDate();
              await updateOperationStatus(record.ID, 'Refused', record.SUPPORT_ACTIVITY_START, currentDate);
              await refreshRecord(record.ID);
              displayRecords(allRecords);
              historyDetailModal.hide();
            } catch (err) {
              console.error('Ошибка отказа:', err);
              alert('Ошибка: ' + err.message);
            }
          });
        }

        if (completeBtn) {
          completeBtn.addEventListener('click', async () => {
            try {
              const currentDate = getCurrentDate();
              await updateOperationStatus(record.ID, 'Completed', record.SUPPORT_ACTIVITY_START, currentDate);

              if (record.REQUIRE_CONFIRMATION == '1' && record.AFTER_OPERATION_STATUS) {
                const inventoryId = record.INVENTORY_ID;
                const newStatusId = parseInt(record.AFTER_OPERATION_STATUS, 10);
                if (inventoryId && !isNaN(newStatusId)) {
                  await updateInventoryStatus(inventoryId, newStatusId);
                }
              }

              await refreshRecord(record.ID);
              displayRecords(allRecords);
              historyDetailModal.hide();
            } catch (err) {
              console.error('Ошибка завершения:', err);
              alert('Ошибка: ' + err.message);
            }
          });
        }

        historyDetailModal.show();
      }

      function filterRecords() {
        const operationValue = filterOperationSelect.value;
        const statusValue = filterStatusSelect.value;
        const inventoryStatusValue = filterInventoryStatus.value.trim();
        const locationValue = filterLocation.value.trim();

        let filtered = allRecords.filter(record => {
          return (
            (!operationValue || record.OPERATION_ID.toString() === operationValue) &&
            (!statusValue || record.executionStatusMapped === statusValue) &&
            (!inventoryStatusValue || record.INVENTORY_RECORD_STATUS.toString() === inventoryStatusValue) &&
            (!locationValue || record.INVENTORY_LOCATION_ID.toString() === locationValue)
          );
        });
        displayRecords(filtered);
        bootstrap.Modal.getInstance(document.getElementById('filterModal')).hide();
      }

      function resetFilter() {
        document.querySelectorAll('#filterModal select').forEach(element => {
          element.value = '';
        });
        displayRecords(allRecords);
      }

      window.downloadBase64File = downloadBase64File;
      window.resetFilter = resetFilter;
    });
  </script>
</body>
</html>