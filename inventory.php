<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Инвентарь</title><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Инвентарь</title>
    <script src="https://api.bitrix24.com/api/v1/"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <style>
        @font-face {
            font-family: 'Gilroy-Light';
            src: url('Gilroy-Light.otf') format('opentype');
            font-weight: normal;
            font-style: normal;
        }
        * {
            scroll-behavior: auto;
            box-sizing: border-box;
        }
        body {
            font-family: 'Gilroy-Light', Arial, sans-serif;
            color: rgb(29, 25, 84);
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        h1 {
            text-align: center;
            color: rgb(29, 25, 84);
        }
        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px auto;
            max-width: 800px;
        }
        .add-button, .columns-button {
            height: 47.78px;
            background-color: #e50045;
            color: #fff;
            border: none;
            padding: 15px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100px;
            text-align: center;
            justify-content: center;
            align-items: center;
        }
        .columns-button {
            background-color: #007bff;
        }
        .add-button:hover {
            background-color: #d0003f;
        }
        .columns-button:hover {
            background-color: #0056b3;
        }
        @media (max-width: 768px) {
            #reportButton {
                display: none !important;
            }
            .controls {
                flex-wrap: wrap;
                gap: 10px;
            }
            .add-button, .columns-button {
                width: 80px;
                font-size: 14px;
                padding: 10px;
            }
        }
        #printControls {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: #f5f5f5;
            padding: 10px;
            z-index: 1000;
            justify-content: space-around;
        }
        .table-container {
            margin: 20px;
            max-width: 100%;
            overflow-x: auto;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #d3d3d3;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        th {
            background-color: #f2f2f2;
            color: rgb(29, 25, 84);
            position: sticky;
            top: 0;
            z-index: 10;
            cursor: pointer;
        }
        tr {
            background-color: white;
            transition: background-color 0.2s;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        td {
            max-width: 200px;
            user-select: text;
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
        }
        .record-checkbox-cell {
            width: 40px;
            text-align: center;
        }
        .arrow-cell {
            width: 40px;
            text-align: center;
            cursor: pointer;
        }
        .arrow {
            font-size: 20px;
        }
        .record-checkbox {
            margin-right: 10px;
        }
        #columnsPopup {
            max-width: 500px;
        }
        #columnsList {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .column-item {
            display: flex;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #ccc;
            cursor: move;
            background-color: #f9f9f9;
        }
        .column-item:hover {
            background-color: #f0f0f0;
        }
        .column-item input[type="checkbox"] {
            margin-right: 10px;
        }
        .column-item.dragging {
            opacity: 0.5;
        }
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000; /* Base z-index for overlays */
        }
        /* Ограничение размеров попапов */
        .popup {
            display: none;
            max-width: 80% !important;
            max-height: 80% !important;
            width: auto;
            height: auto;
            overflow-y: auto;
            overflow-x: auto;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            z-index: 1001;
        }

        /* Адаптация содержимого попапа */
        .popup-content {
            max-height: 100%;
            overflow-y: auto;
        }

        /* Убедимся, что iframe не мешает */
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        #operationsPopupOverlay {
            z-index: 2000; /* Higher z-index to ensure it covers all other overlays */
        }

        #operationsPopup {
            z-index: 2001; /* Highest z-index for the operations popup */
        }

        /* Ensure other popups are below operations popup when it is open */
        #inventoryPopupOverlay,
        #cameraPopupOverlay,
        #filterPopupOverlay,
        #columnsPopupOverlay,
        #mappingPopupOverlay,
        #commentPopupOverlay,
        #recipientPopupOverlay {
            z-index: 1500; /* Lower than operations popup overlay */
        }

        #inventoryPopup,
        #cameraPopup,
        #filterPopup,
        #columnsPopup,
        #mappingPopup,
        #commentPopup,
        #recipientPopup {
            z-index: 1501; /* Lower than operations popup */
        }
        .popup h2 {
            color: rgb(29, 25, 84);
            margin-bottom: 10px;
            font-size: 18px;
        }
        .popup .field-group {
            margin-bottom: 10px;
        }
        .popup .field-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .popup .field-group input[type="text"],
        .popup .field-group input[type="email"],
        .popup .field-group input[type="tel"],
        .popup .field-group textarea,
        .popup .field-group select {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
            box-sizing: border-box;
        }
        .popup .field-group textarea {
            white-space: pre-wrap;
            resize: vertical;
            min-height: 60px;
        }
        .popup .record-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .popup .record-buttons button {
            flex: 1;
            padding: 10px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            background-color: #e50045;
            color: #fff;
            cursor: pointer;
            text-align: center;
            box-sizing: border-box;
        }
        .popup .record-buttons button:hover {
            background-color: #d0003f;
        }
        .qr-code {
            margin-top: 10px;
            margin-bottom: 10px;
            text-align: center;
        }
        #cameraPopup {
            max-width: 350px;
            max-height: 500px;
            overflow-y: auto;
        }
        #cameraPopup video {
            width: 100%;
            height: 250px;
            object-fit: cover;
            margin-bottom: 15px;
        }
        #cameraPopup .camera-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        #cameraPopup h2, #cameraPopup h3 {
            margin-bottom: 10px;
        }
        #cameraPopup button {
            width: 100%;
            background-color: #e50045;
            color: #fff;
            border: none;
            padding: 10px 0;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        .filter-container {
            text-align: center;
            margin: 20px auto;
            max-width: 800px;
        }
        .filter-search-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        .filter-half, .search-half {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .search-half input[type="text"] {
            width: 100%;
            max-width: 200px;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
            box-sizing: border-box;
            height: 38px; /* Совпадает с высотой кнопок для выравнивания */
        }
        @media (max-width: 768px) {
            .filter-search-wrapper {
                flex-direction: column;
                gap: 10px;
            }
            #filterButton, #resetFilterBtn {
                width: 100%;
                max-width: 200px;
            }
            .search-half {
                width: 100%;
                max-width: 300px;
            }
            .search-half input[type="text"] {
                max-width: 100%;
            }
        }
        #operationsList {
            list-style: none;
            padding: 0;
            margin: 10px 0;
        }
        #operationsList li {
            padding: 8px;
            border-bottom: 1px solid #ccc;
            cursor: pointer;
        }
        #operationsList li:hover {
            background-color: #f0f0f0;
        }
        #roleSelection {
            display: none;
            margin-top: 10px;
        }
        #roleSelection select {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        .filter-button-inactive {
            background-color: #ccc;
        }
        .filter-button-inactive:hover {
            background-color: #b3b3b3;
        }
        .filter-button-active {
            background-color: #e50045;
            color: #fff;
        }
        .filter-button-active:hover {
            background-color: #d0003f;
        }
        #filterButton, #resetFilterBtn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s;
            height: 38px; /* Совпадает с высотой input для выравнивания */
        }
        .excel-button {
            background-color: #28a745;
            width: 47.78px;
            height: 47.78px;
            padding: 10px;
            border: none;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: absolute;
            right: 0;
            top: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .excel-button:hover {
            background-color: #218838;
        }
        @media (max-width: 768px) {
            .excel-button {
                display: none;
            }
        }
        .loader-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        .loader {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .loader-bar {
            width: 200px;
            height: 10px;
            background-color: #e0e0e0;
            border-radius: 5px;
            margin: 10px auto;
            overflow: hidden;
        }
        .loader-progress {
            height: 100%;
            background-color: #e50045;
            width: 0;
            transition: width 0.3s;
        }
        .loader-text {
            font-size: 14px;
            color: rgb(29, 25, 84);
        }
        .import-button {
            width: 47.78px;
            height: 47.78px;
            background-color: #17a2b8;
            color: #fff;
            border: none;
            padding: 20px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: absolute;
            right: 60px; /* Adjusted to move left with spacing */
            top: 0;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }
        .import-button:hover {
            background-color: #138496;
        }
        #mappingPopup {
            max-width: 500px;
            width: 95%;
            max-height: 80vh;
            overflow-y: auto;
        }
        #mappingFields {
            margin-bottom: 15px;
        }
        .mapping-field-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .mapping-field-group label {
            flex: 1;
            font-size: 14px;
            margin-right: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .mapping-field-group select {
            flex: 1;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        #resetFilterBtn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        #resetFilterBtn.inactive {
            background-color: #ff0000;
            color: white;
        }

        #resetFilterBtn.active {
            background-color: #008000;
            color: white;
        }
    </style>
</head>
<body>
<h1>Инвентарь</h1>
<div class="controls">
    <button id="backButton" class="add-button">Назад</button>
    <button id="addButton" class="add-button" style="display: none;">+</button>
    <button id="cameraButton" class="add-button">Камера</button>
    <button id="columnsButton" class="columns-button">Столбцы</button>
    <button id="reportButton" class="add-button">Печать QR</button>
    <button id="excelButton" class="excel-button" onclick="exportToExcel()">📊</button>
    <button id="importButton" class="import-button" onclick="document.getElementById('importFileInput').click()">📥</button>
    <input type="file" id="importFileInput" accept=".xlsx,.xls" style="display: none;">
</div>
<div class="filter-container">
    <div class="filter-search-wrapper">
        <button id="filterButton" class="filter-button-inactive">Фильтр</button>
        <button id="resetFilterBtn" class="inactive" onclick="resetFilter()">Сброс фильтра</button>
        <div class="search-half">
            <label for="searchInput">Поиск</label>
            <input type="text" id="searchInput" placeholder="Поиск по инвентарю...">
        </div>
    </div>
</div>
<div id="printControls">
    <button id="cancelPrint" class="add-button">Отмена</button>
    <button id="selectAllButton" class="add-button">Выбрать все</button>
    <button id="confirmPrint" class="add-button">Печать</button>
</div>
<div class="table-container">
    <table id="inventoryTable">
        <thead>
        <tr id="tableHeader"></tr>
        </thead>
        <tbody id="inventoryTableBody"></tbody>
    </table>
</div>

<div class="popup-overlay" id="inventoryPopupOverlay"></div>
<div class="popup" id="inventoryPopup">
    <h2 id="inventoryPopupTitle">Добавление записи</h2>
    <div class="field-group">
        <label for="invModel">Модель</label>
        <input type="text" id="invModel" placeholder="Введите модель" />
    </div>
    <div class="field-group">
        <label for="invSerial">Серийный номер</label>
        <input type="text" id="invSerial" placeholder="Введите серийный номер" />
    </div>
    <div class="field-group">
        <label for="invInventoryCode">Инвентарный номер</label>
        <input type="text" id="invInventoryCode" placeholder="Введите инвентарный номер" />
    </div>
    <div class="field-group">
        <label for="invPcName">Имя ПК</label>
        <input type="text" id="invPcName" placeholder="Введите имя ПК" />
    </div>
    <div class="field-group">
        <label for="invIp">IP-адрес</label>
        <input type="text" id="invIp" placeholder="Введите IP-адрес" />
    </div>
    <div class="field-group" id="responsibleSearchGroup">
        <label for="invResponsibleSearch">Ответственный пользователь</label>
        <input type="text" id="invResponsibleSearch" placeholder="Введите ФИО (Фамилия Имя Отчество)..." oninput="searchResponsibleUser(this.value)" />
        <select id="invResponsibleUserSelect" style="display: none;">
            <option value="">Выберите пользователя...</option>
        </select>
    </div>

    <div class="field-group" id="nonBitrixDetailsGroup">
        <label for="invSurname">Фамилия</label>
        <input type="text" id="invSurname" placeholder="Введите фамилию" />
        <label for="invName">Имя</label>
        <input type="text" id="invName" placeholder="Введите имя" />
        <label for="invPatronymic">Отчество</label>
        <input type="text" id="invPatronymic" placeholder="Введите отчество" />
        <label for="invEmail">Email</label>
        <input type="email" id="invEmail" placeholder="Введите email" oninput="validateEmail(this)" />
        <label for="invNumber">Номер телефона</label>
        <input type="tel" id="invNumber" placeholder="Введите номер телефона" oninput="validatePhoneNumber(this)" />
    </div>
    <div class="field-group">
        <label for="invComment">Комментарий</label>
        <textarea id="invComment" rows="2" placeholder="Введите комментарий"></textarea>
    </div>
    <div class="field-group">
        <label for="invType">Тип</label>
        <select id="invType">
            <option value="">Выберите тип...</option>
        </select>
    </div>
    <div class="field-group">
        <label for="invCompany">Компания</label>
        <select id="invCompany">
            <option value="">Выберите компанию...</option>
        </select>
    </div>
    <div class="field-group">
        <label for="invLocation">Локация</label>
        <select id="invLocation">
            <option value="">Выберите локацию...</option>
        </select>
    </div>
    <div class="field-group">
        <label for="invStatus">Статус</label>
        <select id="invStatus">
            <option value="">Выберите статус...</option>
        </select>
    </div>
    <div class="record-buttons" id="inventoryButtons">
        <button id="operationsButton" onclick="openOperationsList()">Операции</button>
    </div>
    <div class="qr-code" id="popupQrCode" style="display: none;"></div>
    <div class="record-buttons">
        <button onclick="closeInventoryPopup()">Закрыть</button>
    </div>
</div>

<div class="popup-overlay" id="operationsPopupOverlay"></div>
<div class="popup" id="operationsPopup">
    <h2>Выберите операцию</h2>
    <ul id="operationsList"></ul>
    <div id="roleSelection">
        <label for="executionRole">Тип операции:</label>
        <select id="executionRole">
            <option value="direct">Прямая операция</option>
            <option value="reverse">Обратная операция</option>
        </select>
    </div>
    <div class="record-buttons">
        <button onclick="confirmOperationSelection()">Подтвердить</button>
        <button onclick="closeOperationsPopup()">Закрыть</button>
    </div>
</div>

<div class="popup-overlay" id="commentPopupOverlay"></div>
<div class="popup" id="commentPopup">
    <h2>Комментарий к операции</h2>
    <div class="field-group">
        <label for="operationComment">Опишите необходимость операции</label>
        <textarea id="operationComment" rows="4" placeholder="Введите комментарий..."></textarea>
    </div>
    <div class="record-buttons">
        <button onclick="confirmOperation()">Подтвердить</button>
        <button onclick="closeCommentPopup()">Отменить</button>
    </div>
</div>

<div class="popup-overlay" id="recipientPopupOverlay"></div>
<div class="popup" id="recipientPopup">
    <h2>Выберите реципиента</h2>
    <div class="field-group" id="bitrixRecipientSelectGroup">
        <label for="recipientUserSelect">Пользователь Bitrix</label>
        <select id="recipientUserSelect">
            <option value="">Выберите пользователя...</option>
        </select>
    </div>
    <div class="field-group">
        <label>
            <input type="checkbox" id="noBitrixRecipient" />
            Нет в Битрикс
        </label>
    </div>
    <div class="field-group" id="nonBitrixRecipientDetailsGroup" style="display: none;">
        <label for="recipientSurname">Фамилия</label>
        <input type="text" id="recipientSurname" placeholder="Введите фамилию" />
        <label for="recipientName">Имя</label>
        <input type="text" id="recipientName" placeholder="Введите имя" />
        <label for="recipientPatronymic">Отчество</label>
        <input type="text" id="recipientPatronymic" placeholder="Введите отчество" />
        <label for="recipientEmail">Email</label>
        <input type="email" id="recipientEmail" placeholder="Введите email" oninput="validateEmail(this)" />
        <label for="recipientNumber">Номер телефона</label>
        <input type="tel" id="recipientNumber" placeholder="Введите номер телефона" oninput="validatePhoneNumber(this)" />
    </div>
    <div class="record-buttons">
        <button onclick="confirmRecipientSelection()">Подтвердить</button>
        <button onclick="closeRecipientPopup()">Отменить</button>
    </div>
</div>

<div class="popup-overlay" id="cameraPopupOverlay"></div>
<div class="popup" id="cameraPopup">
    <h2>Камера</h2>
    <video id="video" autoplay></video>
    <div class="camera-buttons">
        <button onclick="closeCameraPopup()">Закрыть</button>
    </div>
</div>

<div class="popup-overlay" id="filterPopupOverlay"></div>
<div class="popup" id="filterPopup">
    <h2>Фильтр инвентаря</h2>
    <div class="field-group">
        <label for="filterCompany">Компания</label>
        <select id="filterCompany">
            <option value="">Выберите компанию...</option>
        </select>
    </div>
    <div class="field-group">
        <label for="filterLocation">Местоположение</label>
        <select id="filterLocation">
            <option value="">Выберите местоположение...</option>
        </select>
    </div>
    <div class="field-group">
        <label for="filterType">Тип</label>
        <select id="filterType">
            <option value="">Выберите тип...</option>
        </select>
    </div>
    <div class="field-group">
        <label for="filterStatus">Статус инвентаря</label>
        <select id="filterStatus">
            <option value="">Выберите статус...</option>
        </select>
    </div>
    <div class="field-group">
        <label for="filterResponsibleUser">Ответственный пользователь</label>
        <select id="filterResponsibleUser">
            <option value="">Выберите пользователя...</option>
        </select>
    </div>
    <div class="record-buttons">
        <button onclick="applyFilter()">Применить</button>
        <button onclick="cancelFilter()">Отменить</button>
    </div>
</div>

<div class="popup-overlay" id="columnsPopupOverlay"></div>
<div class="popup" id="columnsPopup">
    <h2>Настройка столбцов</h2>
    <ul id="columnsList"></ul>
    <div class="record-buttons">
        <button onclick="saveColumnsConfig()">Сохранить</button>
        <button onclick="closeColumnsPopup()">Закрыть</button>
    </div>
</div>

<canvas id="canvas" style="display: none;"></canvas>
<div id="qr-output"></div>

<script>
    let sortColumn = null;
    let sortDirection = 'asc';

    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }
    const appFields = [
        { excel: 'Тип', app: 'type_id', required: false },
        { excel: 'Модель', app: 'model', required: true }, // Исправлено на 'model'
        { excel: 'Серийный номер', app: 'serial_code', required: true }, // Исправлено на 'serial_code'
        { excel: 'Инвентарный номер', app: 'inventory_code', required: true }, // Уникальное поле
        { excel: 'Имя ПК', app: 'pc_name', required: false },
        { excel: 'IP-адрес', app: 'ip', required: false },
        { excel: 'ФИО', app: 'responsible', required: false },
        { excel: 'Эл.почта', app: 'email', required: false },
        { excel: 'Вн.номер', app: 'number', required: false },
        { excel: 'Компания', app: 'company_id', required: false },
        { excel: 'Местоположение', app: 'location_id', required: false },
        { excel: 'Статус', app: 'status_id', required: false },
        { excel: 'Комментарий', app: 'comment', required: false }
    ];
    const availableColumns = [
        { id: 'model', label: 'Модель', visible: true },
        { id: 'serial', label: 'Серийный номер', visible: true },
        { id: 'inventoryCode', label: 'Инвентарный номер', visible: true },
        { id: 'pcName', label: 'Имя ПК', visible: false },
        { id: 'ip', label: 'IP-адрес', visible: false },
        { id: 'responsible', label: 'Ответственный', visible: true },
        { id: 'comment', label: 'Комментарий', visible: true },
        { id: 'type', label: 'Тип', visible: true },
        { id: 'company', label: 'Компания', visible: true },
        { id: 'location', label: 'Локация', visible: true },
        { id: 'status', label: 'Статус', visible: true }
    ];

    let columnsConfig = [];

    function loadColumnsConfig() {
        try {
            const savedConfig = localStorage.getItem('inventoryColumnsConfig');
            if (savedConfig) {
                const parsedConfig = JSON.parse(savedConfig);
                if (!Array.isArray(parsedConfig)) {
                    console.warn('Некорректный формат сохраненной конфигурации колонок, используется стандартная');
                    return [...availableColumns];
                }

                // Обновляем конфигурацию, сохраняя порядок и видимость
                const updatedConfig = parsedConfig
                    .filter(col => availableColumns.some(ac => ac.id === col.id)) // Фильтруем несуществующие колонки
                    .map(col => {
                        const defaultCol = availableColumns.find(ac => ac.id === col.id);
                        return { ...defaultCol, visible: col.visible };
                    });

                // Добавляем недостающие колонки из availableColumns
                const missingColumns = availableColumns.filter(
                    ac => !parsedConfig.some(c => c.id === ac.id)
                );
                return [...updatedConfig, ...missingColumns];
            }
            return [...availableColumns];
        } catch (error) {
            console.error('Ошибка при загрузке конфигурации колонок:', error);
            return [...availableColumns];
        }
    }

    // Загрузка конфигурации колонок из localStorage
    function loadColumnsConfig() {
        try {
            const savedConfig = localStorage.getItem('inventoryColumnsConfig');
            if (savedConfig) {
                const parsedConfig = JSON.parse(savedConfig);
                if (!Array.isArray(parsedConfig) || parsedConfig.length === 0) {
                    console.warn('Некорректный формат сохраненной конфигурации, используется стандартная');
                    return [...availableColumns];
                }

                // Проверяем, что все колонки из parsedConfig существуют в availableColumns
                const validConfig = parsedConfig.filter(col => availableColumns.some(ac => ac.id === col.id));
                if (validConfig.length === 0) {
                    console.warn('Сохранённая конфигурация пуста или содержит неверные колонки, используется стандартная');
                    return [...availableColumns];
                }

                // Сохраняем порядок и видимость из savedConfig
                const updatedConfig = validConfig.map(col => {
                    const defaultCol = availableColumns.find(ac => ac.id === col.id);
                    return { id: col.id, label: col.label, visible: col.visible ?? defaultCol.visible };
                });

                // Добавляем недостающие колонки из availableColumns
                const missingColumns = availableColumns.filter(
                    ac => !validConfig.some(c => c.id === ac.id)
                );
                const finalConfig = [...updatedConfig, ...missingColumns];

                console.log('Loaded columnsConfig:', finalConfig.map(col => ({ id: col.id, visible: col.visible }))); // Отладка
                return finalConfig;
            }
            console.log('No saved config, using default:', availableColumns.map(col => col.id));
            return [...availableColumns];
        } catch (error) {
            console.error('Ошибка при загрузке конфигурации колонок:', error);
            return [...availableColumns];
        }
    }

    // Открытие попапа для настройки колонок
    function openColumnsPopup() {
        const columnsList = document.getElementById('columnsList');
        columnsList.innerHTML = '';

        columnsConfig.forEach((col, index) => {
            const li = document.createElement('li');
            li.className = 'column-item';
            li.setAttribute('draggable', 'true');
            li.setAttribute('data-index', index);
            li.innerHTML = `
                <input type="checkbox" ${col.visible ? 'checked' : ''} onchange="updateColumnVisibility(${index}, this.checked)">
                <span>${col.label}</span>
            `;
            columnsList.appendChild(li);
        });

        setupDragAndDrop(columnsList);

        document.getElementById('columnsPopupOverlay').style.display = 'block';
        document.getElementById('columnsPopup').style.display = 'block';
    }



    function updateColumnVisibility(index, isVisible) {
        try {
            columnsConfig[index].visible = isVisible;
            // Не вызываем saveColumnsConfig здесь, чтобы попап не закрывался
        } catch (error) {
            console.error('Ошибка при обновлении видимости колонки:', error);
            alert('Не удалось обновить видимость колонки.');
        }
    }

    // Настройка перетаскивания для изменения порядка колонок
    // Настройка перетаскивания для изменения порядка колонок
    function setupDragAndDrop(list) {
        let draggedItem = null;

        list.addEventListener('dragstart', (e) => {
            draggedItem = e.target.closest('.column-item');
            if (draggedItem) {
                draggedItem.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', draggedItem.getAttribute('data-index')); // Для кроссбраузерной совместимости
                console.log('Drag started:', draggedItem.getAttribute('data-index')); // Отладка
            }
        });

        list.addEventListener('dragend', (e) => {
            if (draggedItem) {
                draggedItem.classList.remove('dragging');
                draggedItem = null;
                list.querySelectorAll('.column-item').forEach(item => item.classList.remove('drag-over'));
            }
        });

        list.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const targetItem = e.target.closest('.column-item');
            if (targetItem && targetItem !== draggedItem) {
                targetItem.classList.add('drag-over'); // Подсветка целевого элемента
            }
        });

        list.addEventListener('dragleave', (e) => {
            const targetItem = e.target.closest('.column-item');
            if (targetItem) {
                targetItem.classList.remove('drag-over');
            }
        });

        list.addEventListener('drop', (e) => {
            e.preventDefault();
            const targetItem = e.target.closest('.column-item');
            if (!targetItem || targetItem === draggedItem || !draggedItem) {
                list.querySelectorAll('.column-item').forEach(item => item.classList.remove('drag-over'));
                console.log('Drop ignored: invalid target or same item');
                return;
            }

            const draggedIndex = parseInt(draggedItem.getAttribute('data-index'));
            const targetIndex = parseInt(targetItem.getAttribute('data-index'));

            // Перемещение элемента в DOM для немедленной визуальной обратной связи
            if (draggedIndex < targetIndex) {
                targetItem.after(draggedItem); // Вставляем после целевого элемента
            } else {
                targetItem.before(draggedItem); // Вставляем перед целевым элементом
            }

            // Обновляем массив columnsConfig
            const [movedItem] = columnsConfig.splice(draggedIndex, 1);
            columnsConfig.splice(targetIndex, 0, movedItem);

            // Обновляем атрибуты data-index в DOM
            const items = list.querySelectorAll('.column-item');
            items.forEach((item, index) => {
                item.setAttribute('data-index', index);
                const checkbox = item.querySelector('input[type="checkbox"]');
                checkbox.setAttribute('onchange', `updateColumnVisibility(${index}, this.checked)`);
            });

            // Удаляем подсветку
            list.querySelectorAll('.column-item').forEach(item => item.classList.remove('drag-over'));

            console.log('Dropped:', { draggedIndex, targetIndex, newOrder: columnsConfig.map(col => col.id) }); // Отладка
        });
    }

    // Сохранение конфигурации колонок
    function saveColumnsConfig() {
        try {
            const configToSave = columnsConfig.map(col => ({
                id: col.id,
                label: col.label,
                visible: col.visible
            }));
            localStorage.setItem('inventoryColumnsConfig', JSON.stringify(configToSave));
            console.log('Saved columnsConfig:', configToSave.map(col => ({ id: col.id, visible: col.visible }))); // Отладка
            if (allInventoryRecords && allInventoryRecords.length > 0) {
                displayInventoryList(allInventoryRecords);
                console.log('Table updated with columns:', columnsConfig.map(col => col.id));
            } else {
                console.warn('allInventoryRecords is empty or not defined');
            }
            closeColumnsPopup();
        } catch (error) {
            console.error('Ошибка при сохранении конфигурации колонок:', error);
            alert('Не удалось сохранить настройки колонок. Попробуйте снова.');
        }
    }

    // Закрытие попапа
    function closeColumnsPopup() {
        document.getElementById('columnsPopupOverlay').style.display = 'none';
        document.getElementById('columnsPopup').style.display = 'none';
        // Сбрасываем до последней сохранённой конфигурации
        columnsConfig = loadColumnsConfig();
        if (allInventoryRecords && allInventoryRecords.length > 0) {
            displayInventoryList(allInventoryRecords);
            console.log('Table reset with columns:', columnsConfig.map(col => col.id)); // Отладка
        }
        console.log('Popup closed, columnsConfig:', columnsConfig.map(col => ({ id: col.id, visible: col.visible }))); // Отладка
    }
    columnsConfig = loadColumnsConfig();
    document.addEventListener('DOMContentLoaded', function() {
        const reportButton = document.getElementById('reportButton');
        updateFilterButtonStyle();
        if (isMobileDevice()) {
            reportButton.style.display = 'none';
        }

        const columnsButton = document.getElementById('columnsButton');
        if (columnsButton) {
            columnsButton.addEventListener('click', openColumnsPopup);
        }
        // Обновляем таблицу с учетом сохраненной конфигурации
        if (typeof displayInventoryList === 'function') {
            displayInventoryList(allInventoryRecords);
        }

        if (typeof BX24 === 'undefined') {
            document.getElementById('inventoryTableBody').innerHTML = '<tr><td colspan="12">Ошибка: BX24 не загружен. Откройте страницу через Bitrix24.</td></tr>';
            console.error('BX24 не загружен');
            return;
        }

        BX24.init(function() {
            if (BX24.user && typeof BX24.user.getId === 'function') {
                currentUserId = BX24.user.getId();
                fetchToken().then(access_token => {
                    setupPermissionsAndData(currentUserId, access_token);
                }).catch(err => {
                    console.error('Ошибка получения токена:', err);
                    showTokenExpiredMessage();
                });
            } else {
                BX24.callMethod('user.current', {}, function(result) {
                    if (result.error()) {
                        console.error('Ошибка получения текущего пользователя:', result.error());
                        showTokenExpiredMessage();
                        return;
                    }
                    currentUserId = result.data().ID;
                    fetchToken().then(access_token => {
                        setupPermissionsAndData(currentUserId, access_token);
                    }).catch(err => {
                        console.error('Ошибка получения токена:', err);
                        showTokenExpiredMessage();
                    });
                });
            }
        });
        
        // Добавляем обработчик для закрытия попапа при клике на overlay
        const inventoryPopupOverlay = document.getElementById('inventoryPopupOverlay');
        const inventoryPopup = document.getElementById('inventoryPopup');

        inventoryPopupOverlay.addEventListener('click', () => {
            closeInventoryPopup();
        });

        inventoryPopup.addEventListener('click', (e) => {
            e.stopPropagation(); // Предотвращаем всплытие события, чтобы попап не закрывался при клике внутри него
        });
    });

    let currentInventoryId = null;
    let currentInventoryResponsibleId = null;
    let isFilterApplied = false;
    let isPrintMode = false;
    let userPermissions = null;
    let currentUserId = null;
    let checkboxStates = {};
    let references = {
        types: [],
        companies: [],
        locations: [],
        statuses: [],
        users: []
    };
    let allInventoryRecords = [];
    let selectedOperationId = null;
    let selectedAfterStatus = null;
    let executionRole = null;

    function updateFilterButtonStyle() {
        const filterButton = document.getElementById('filterButton');
        if (isFilterApplied) {
            filterButton.classList.remove('filter-button-inactive');
            filterButton.classList.add('filter-button-active');
        } else {
            filterButton.classList.remove('filter-button-active');
            filterButton.classList.add('filter-button-inactive');
        }
    }

    function setupPermissionsAndData(userId, access_token) {
        getUserPermissions(userId, access_token).then(permissions => {
            userPermissions = permissions;
            setupInterfaceBasedOnPermissions(permissions);

            const addButton = document.getElementById('addButton');
            const backButton = document.getElementById('backButton');
            const cameraButton = document.getElementById('cameraButton');
            const reportButton = document.getElementById('reportButton');
            const cancelPrint = document.getElementById('cancelPrint');
            const confirmPrint = document.getElementById('confirmPrint');
            const noBitrixUserCheckbox = document.getElementById('noBitrixUser');
            const filterButton = document.getElementById('filterButton');

            if (addButton) {
                addButton.addEventListener('click', function() {
                    if (userPermissions === 'view') {
                        alert('У вас нет прав на добавление записи');
                        return;
                    }
                    openAddInventoryPopup();
                });
            }
            if (filterButton) {
                filterButton.addEventListener('click', function() {
                    openFilterPopup();
                });
            }
            if (backButton) {
                backButton.addEventListener('click', function() {
                    window.location.href = 'https://predprod.reforma-sk.ru/local-pril/pril.php';
                });
            }
            if (cameraButton) {
                cameraButton.addEventListener('click', function() {
                    openCameraPopup();
                });
            }
            if (reportButton) {
                reportButton.addEventListener('click', function() {
                    isPrintMode = true;
                    backButton.style.display = 'none';
                    if (userPermissions !== 'view') addButton.style.display = 'none';
                    cameraButton.style.display = 'none';
                    reportButton.style.display = 'none';
                    document.getElementById('columnsButton').style.display = 'none';
                    document.getElementById('excelButton').style.display = 'none';
                    document.getElementById('printControls').style.display = 'flex';
                    document.querySelectorAll('.record-checkbox').forEach(cb => cb.style.display = 'inline');
                    displayInventoryList(allInventoryRecords);
                });
            }
            if (cancelPrint) {
                cancelPrint.addEventListener('click', function() {
                    isPrintMode = false;
                    backButton.style.display = 'flex';
                    if (userPermissions !== 'view') addButton.style.display = 'flex';
                    cameraButton.style.display = 'flex';
                    reportButton.style.display = 'flex';
                    document.getElementById('columnsButton').style.display = 'flex';
                    document.getElementById('excelButton').style.display = 'flex';
                    document.getElementById('printControls').style.display = 'none';
                    document.querySelectorAll('.record-checkbox').forEach(cb => {
                        cb.style.display = 'none';
                        cb.checked = false;
                    });
                    checkboxStates = {};
                    displayInventoryList(allInventoryRecords);
                });
            }
            if (confirmPrint) {
                const selectAllButton = document.getElementById('selectAllButton');
                if (selectAllButton) {
                    selectAllButton.addEventListener('click', function() {
                        const checkboxes = document.querySelectorAll('.record-checkbox');
                        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                        checkboxes.forEach(cb => {
                            cb.checked = !allChecked;
                            checkboxStates[cb.getAttribute('data-id')] = cb.checked;
                        });
                    });
                }
                confirmPrint.addEventListener('click', function() {
                    const selectedIds = [];
                    document.querySelectorAll('.record-checkbox:checked').forEach(cb => {
                        selectedIds.push(cb.getAttribute('data-id'));
                    });
                    if (selectedIds.length === 0) {
                        alert('Выберите хотя бы одну запись.');
                        return;
                    }
                    if (window.innerWidth < 768) {
                        alert('Эта функция доступна только на компьютере.');
                        return;
                    }
                    const skipCells = prompt('Сколько ячеек пропустить? (по умолчанию 0)', '0');
                    if (skipCells === null) return;
                    const skip = parseInt(skipCells, 10) || 0;
                    fetch('/local-pril/generate_report.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ record_ids: selectedIds.join(','), skip_cells: skip })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                alert(data.message + ' Файл: ' + data.file_path);
                                window.location.href = data.file_path;
                                document.getElementById('cancelPrint').click();
                            } else {
                                alert('Ошибка: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Ошибка:', error);
                            alert('Произошла ошибка при выполнении запроса.');
                        });
                });
            }
            if (noBitrixUserCheckbox) {
                const bitrixUserSelectGroup = document.getElementById('bitrixUserSelectGroup');
                const nonBitrixDetailsGroup = document.getElementById('nonBitrixDetailsGroup');
                noBitrixUserCheckbox.addEventListener('change', () => {
                    if (noBitrixUserCheckbox.checked) {
                        bitrixUserSelectGroup.style.display = 'none';
                        nonBitrixDetailsGroup.style.display = 'block';
                        document.getElementById('invResponsibleUserSelect').value = '';
                    } else {
                        bitrixUserSelectGroup.style.display = 'block';
                        nonBitrixDetailsGroup.style.display = 'none';
                        document.getElementById('invSurname').value = '';
                        document.getElementById('invName').value = '';
                        document.getElementById('invPatronymic').value = '';
                        document.getElementById('invEmail').value = '';
                        document.getElementById('invNumber').value = '';
                    }
                });
            }
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchText = searchInput.value.trim().toLowerCase();
                    applySearchFilter(searchText, access_token);
                });
            }

            Promise.all([
                loadBitrixUsers(access_token),
                loadReferences(access_token)
            ]).then(([users, _]) => {
                references.users = users;
                fillBitrixUsersDropdown(document.getElementById('invResponsibleUserSelect'), users);
                loadInventoryList(access_token);
            }).catch(err => {
                console.error('Ошибка загрузки пользователей или справочных данных:', err);
                showTokenExpiredMessage();
            });
        }).catch(err => {
            console.error('Ошибка получения прав пользователя:', err);
            showTokenExpiredMessage();
        });
    }

    function applySearchFilter(searchText, access_token) {
        let filters = {};
        if (isFilterApplied) {
            filters = {
                company: document.getElementById('filterCompany').value,
                location: document.getElementById('filterLocation').value,
                type: document.getElementById('filterType').value,
                status: document.getElementById('filterStatus').value,
                responsibleUser: document.getElementById('filterResponsibleUser').value
            };
        }

        let filteredRecords = allInventoryRecords;

        if (userPermissions === 'view') {
            filteredRecords = filteredRecords.filter(item => {
                const responsibleUserId = item.RESPONSIBLE_USER_ID ? item.RESPONSIBLE_USER_ID.toString() : null;
                return responsibleUserId === currentUserId.toString();
            });
        }

        if (!Object.values(filters).every(val => !val)) {
            filteredRecords = filteredRecords.filter(item => {
                return (
                    (!filters.company || (item.COMPANY_ID && item.COMPANY_ID.toString() === filters.company)) &&
                    (!filters.location || (item.LOCATION_ID && item.LOCATION_ID.toString() === filters.location)) &&
                    (!filters.type || (item.TYPE_ID && item.TYPE_ID.toString() === filters.type)) &&
                    (!filters.status || (item.STATUS_ID && item.STATUS_ID.toString() === filters.status)) &&
                    (!filters.responsibleUser || (item.RESPONSIBLE_USER_ID && item.RESPONSIBLE_USER_ID.toString() === filters.responsibleUser))
                );
            });
        }

        if (searchText) {
            filteredRecords = filteredRecords.filter(item => {
                const fieldsToSearch = [
                    item.MODEL || '',
                    item.SERIAL_CODE || '',
                    item.INVENTORY_CODE || '',
                    item.PC_NAME || '',
                    item.IP || '',
                    item.COMMENT || '',
                    item.SURNAME || '',
                    item.NAME || '',
                    item.PATRONYMIC || '',
                    item.EMAIL || '',
                    item.NUMBER || '',
                    getReferenceName(references.companies, item.COMPANY_ID, 'COMPANY_NAME'),
                    getReferenceName(references.locations, item.LOCATION_ID, 'LOCATION_NAME'),
                    getReferenceName(references.types, item.TYPE_ID, 'TYPE_NAME'),
                    getReferenceName(references.statuses, item.STATUS_ID, 'STATUS_NAME'),
                    getUserName(references.users, item.RESPONSIBLE_USER_ID)
                ];
                return fieldsToSearch.some(field =>
                    field.toLowerCase().includes(searchText)
                );
            });
        }

        if (sortColumn) {
            filteredRecords = sortData(filteredRecords, sortColumn, sortDirection);
        }

        displayInventoryList(filteredRecords);
    }

    function sortData(data, columnId, direction) {
        const column = columnsConfig.find(col => col.id === columnId);
        if (!column) return data;

        const sortedData = [...data];

        sortedData.sort((a, b) => {
            let valueA, valueB;

            switch (columnId) {
                case 'model':
                    valueA = a.MODEL || '';
                    valueB = b.MODEL || '';
                    break;
                case 'serial':
                    valueA = a.SERIAL_CODE || '';
                    valueB = b.SERIAL_CODE || '';
                    break;
                case 'inventoryCode':
                    valueA = a.INVENTORY_CODE || '';
                    valueB = b.INVENTORY_CODE || '';
                    if (!isNaN(valueA) && !isNaN(valueB)) {
                        valueA = parseFloat(valueA);
                        valueB = parseFloat(valueB);
                    }
                    break;
                case 'pcName':
                    valueA = a.PC_NAME || '';
                    valueB = b.PC_NAME || '';
                    break;
                case 'ip':
                    valueA = a.IP || '';
                    valueB = b.IP || '';
                    break;
                case 'responsible':
                    if (a.RESPONSIBLE_USER_ID) {
                        valueA = getUserName(references.users, a.RESPONSIBLE_USER_ID);
                    } else if (a.SURNAME || a.NAME) {
                        valueA = `${a.SURNAME || ''} ${a.NAME || ''} ${a.PATRONYMIC || ''}`.trim();
                    } else {
                        valueA = 'Не указано';
                    }
                    if (b.RESPONSIBLE_USER_ID) {
                        valueB = getUserName(references.users, b.RESPONSIBLE_USER_ID);
                    } else if (b.SURNAME || b.NAME) {
                        valueB = `${b.SURNAME || ''} ${b.NAME || ''} ${b.PATRONYMIC || ''}`.trim();
                    } else {
                        valueB = 'Не указано';
                    }
                    break;
                case 'comment':
                    valueA = a.COMMENT || '';
                    valueB = b.COMMENT || '';
                    break;
                case 'type':
                    valueA = getReferenceName(references.types, a.TYPE_ID, 'TYPE_NAME');
                    valueB = getReferenceName(references.types, b.TYPE_ID, 'TYPE_NAME');
                    break;
                case 'company':
                    valueA = getReferenceName(references.companies, a.COMPANY_ID, 'COMPANY_NAME');
                    valueB = getReferenceName(references.companies, b.COMPANY_ID, 'COMPANY_NAME');
                    break;
                case 'location':
                    valueA = getReferenceName(references.locations, a.LOCATION_ID, 'LOCATION_NAME');
                    valueB = getReferenceName(references.locations, b.LOCATION_ID, 'LOCATION_NAME');
                    break;
                case 'status':
                    valueA = getReferenceName(references.statuses, a.STATUS_ID, 'STATUS_NAME');
                    valueB = getReferenceName(references.statuses, b.STATUS_ID, 'STATUS_NAME');
                    break;
                default:
                    valueA = '';
                    valueB = '';
            }

            if (typeof valueA === 'string' && typeof valueB === 'string') {
                return direction === 'asc' ? valueA.localeCompare(valueB) : valueB.localeCompare(valueA);
            } else {
                return direction === 'asc' ? valueA - valueB : valueB - valueA;
            }
        });

        return sortedData;
    }

    function getReferenceName(referenceArray, id, nameField) {
        if (!id || !referenceArray) return '';
        const item = referenceArray.find(ref => ref.ID.toString() === id.toString());
        return item ? (item[nameField] || '') : '';
    }

    function getUserName(users, userId) {
        if (!userId || !users) return '';
        const user = users.find(u => u.ID.toString() === userId.toString());
        if (!user) return '';
        return `${user.LAST_NAME || ''} ${user.NAME || ''} ${user.SECOND_NAME || ''}`.trim() || `Пользователь ID ${userId}`;
    }

    function setupInterfaceBasedOnPermissions(permissions) {
        const addButton = document.getElementById('addButton');
        const cameraButton = document.getElementById('cameraButton');
        const reportButton = document.getElementById('reportButton');
        const backButton = document.getElementById('backButton');

        if (backButton) {
            backButton.style.display = 'flex';
        }

        if (permissions === 'edit' || permissions === 'full') {
            if (addButton) addButton.style.display = 'flex';
            if (cameraButton) cameraButton.style.display = 'flex';
            if (reportButton) reportButton.style.display = 'flex';
        } else if (permissions === 'view') {
            if (addButton) addButton.style.display = 'none';
            if (cameraButton) cameraButton.style.display = 'none';
            if (reportButton) reportButton.style.display = 'none';
        } else {
            console.error('Неизвестный тип прав:', permissions);
            if (addButton) addButton.style.display = 'none';
            if (cameraButton) cameraButton.style.display = 'none';
            if (reportButton) reportButton.style.display = 'none';
        }

        if (isMobileDevice() && reportButton) {
            reportButton.style.display = 'none';
        }
    }

    function fetchToken() {
        return fetch('/local-pril/tokens.json')
            .then(resp => {
                if (!resp.ok) throw new Error('Ошибка загрузки tokens.json: ' + resp.status);
                return resp.json();
            })
            .then(tokens => {
                if (!tokens.access_token) throw new Error('Токен не найден');
                return tokens.access_token;
            });
    }

    function loadReferences(access_token) {
        const promises = [
            callRest('custom.getiplusreferenceinventorytypes', {}, access_token).then(data => { references.types = data; }),
            callRest('custom.getiplusreferencecompany', {}, access_token).then(data => { references.companies = data; }),
            callRest('custom.getiplusreferencelocation', {}, access_token).then(data => { references.locations = data; }),
            callRest('custom.getiplusreferencestatus', {}, access_token).then(data => { references.statuses = data; })
        ];

        return Promise.all(promises).then(() => {
            fillSelect(document.getElementById('invType'), references.types, 'TYPE_NAME');
            fillSelect(document.getElementById('invCompany'), references.companies, 'COMPANY_NAME');
            fillSelect(document.getElementById('invLocation'), references.locations, 'LOCATION_NAME');
            fillSelect(document.getElementById('invStatus'), references.statuses, 'STATUS_NAME');
            fillSelect(document.getElementById('filterCompany'), references.companies, 'COMPANY_NAME');
            fillSelect(document.getElementById('filterLocation'), references.locations, 'LOCATION_NAME');
            fillSelect(document.getElementById('filterType'), references.types, 'TYPE_NAME');
            fillSelect(document.getElementById('filterStatus'), references.statuses, 'STATUS_NAME');
        }).catch(err => console.error('Ошибка загрузки справочных данных:', err));
    }

    function loadBitrixUsers(access_token) {
        return new Promise((resolve, reject) => {
            BX24.callMethod(
                'user.get',
                {},
                (result) => {
                    if (result.error()) {
                        reject(result.error());
                    } else {
                        resolve(result.data());
                    }
                },
                { auth: access_token }
            );
        });
    }

    function fillBitrixUsersDropdown(selectEl, users) {
        const currentResponsibleId = selectEl.value || '';
        selectEl.innerHTML = '<option value="">Выберите пользователя...</option>';

        let userFound = false;
        users.forEach(user => {
            if (user.ACTIVE) {
                const option = document.createElement('option');
                option.value = user.ID;
                const fullName = `${user.LAST_NAME || ''} ${user.NAME || ''} ${user.SECOND_NAME || ''}`.trim();
                option.textContent = fullName || `Пользователь ID ${user.ID}`;
                selectEl.appendChild(option);
                if (user.ID.toString() === currentResponsibleId.toString()) {
                    userFound = true;
                }
            }
        });

        if (currentResponsibleId && !userFound) {
            const placeholderOption = document.createElement('option');
            placeholderOption.value = currentResponsibleId;
            placeholderOption.textContent = `Пользователь не найден (ID: ${currentResponsibleId})`;
            selectEl.appendChild(placeholderOption);
        }

        if (currentResponsibleId) {
            selectEl.value = currentResponsibleId;
        }
    }

    function callRest(method, params, access_token) {
        return new Promise((resolve, reject) => {
            BX24.callMethod(method, params, function(result) {
                if (result.error()) {
                    reject(result.error());
                } else {
                    resolve(result.data().result || []);
                }
            }, { auth: access_token });
        });
    }

    function fillSelect(selectEl, dataArr, labelField) {
        selectEl.innerHTML = '<option value="">Выберите...</option>';
        dataArr.forEach(item => {
            const option = document.createElement('option');
            option.value = item.ID;
            option.textContent = item[labelField];
            selectEl.appendChild(option);
        });
    }
    function resetFilter() {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterCompany').value = '';
        document.getElementById('filterLocation').value = '';
        document.getElementById('filterType').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterResponsibleUser').value = '';
        const resetBtn = document.getElementById('resetFilterBtn');
        resetBtn.classList.remove('active');
        resetBtn.classList.add('inactive');
        applyFilter();
    }
    function loadInventoryList(access_token, filters = {}) {
        BX24.callMethod('custom.getiplusinventory', {}, function(result) {
            if (result.error()) {
                document.getElementById('inventoryTableBody').innerHTML = '<tr><td colspan="12">Ошибка загрузки данных</td></tr>';
                console.error('Ошибка REST:', result.error());
                return;
            }

            allInventoryRecords = result.data().result || [];

            let filteredRecords = allInventoryRecords;

            if (userPermissions === 'view') {
                filteredRecords = filteredRecords.filter(item => {
                    const responsibleUserId = item.RESPONSIBLE_USER_ID ? item.RESPONSIBLE_USER_ID.toString() : null;
                    return responsibleUserId === currentUserId.toString();
                });
            }

            if (!Object.values(filters).every(val => !val)) {
                filteredRecords = filteredRecords.filter(item => {
                    return (
                        (!filters.company || (item.COMPANY_ID && item.COMPANY_ID.toString() === filters.company)) &&
                        (!filters.location || (item.LOCATION_ID && item.LOCATION_ID.toString() === filters.location)) &&
                        (!filters.type || (item.TYPE_ID && item.TYPE_ID.toString() === filters.type)) &&
                        (!filters.status || (item.STATUS_ID && item.STATUS_ID.toString() === filters.status)) &&
                        (!filters.responsibleUser || (item.RESPONSIBLE_USER_ID && item.RESPONSIBLE_USER_ID.toString() === filters.responsibleUser))
                    );
                });
            }

            const searchInput = document.getElementById('searchInput');
            const searchText = searchInput ? searchInput.value.trim().toLowerCase() : '';
            applySearchFilter(searchText, access_token);
        }, { auth: access_token });
    }

    function displayInventoryList(data) {
        const tableHeader = document.getElementById('tableHeader');
        const tableBody = document.getElementById('inventoryTableBody');
        tableHeader.innerHTML = '';
        tableBody.innerHTML = '';

        if (!data.length) {
            const colCount = columnsConfig.filter(col => col.visible).length + (isPrintMode ? 2 : 1);
            tableBody.innerHTML = `<tr><td colspan="${colCount}">${
                userPermissions === 'view' ? 'У вас нет инвентаря, за который вы ответственны.' : 'Нет данных об инвентаре.'
            }</td></tr>`;
            return;
        }

        if (isPrintMode) {
            const thCheckbox = document.createElement('th');
            thCheckbox.className = 'record-checkbox-cell';
            thCheckbox.textContent = '';
            tableHeader.appendChild(thCheckbox);
        }

        columnsConfig.forEach(col => {
            if (col.visible) {
                const th = document.createElement('th');
                th.textContent = col.label;
                th.style.cursor = 'pointer';

                if (sortColumn === col.id) {
                    const arrow = document.createElement('span');
                    arrow.textContent = sortDirection === 'asc' ? ' ↑' : ' ↓';
                    th.appendChild(arrow);
                }

                th.addEventListener('click', () => {
                    if (sortColumn === col.id) {
                        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        sortColumn = col.id;
                        sortDirection = 'asc';
                    }
                    const sortedData = sortData(data, sortColumn, sortDirection);
                    displayInventoryList(sortedData);
                });

                tableHeader.appendChild(th);
            }
        });

        const thArrow = document.createElement('th');
        thArrow.className = 'arrow-cell';
        thArrow.textContent = '';
        tableHeader.appendChild(thArrow);

        data.forEach(item => {
            const tr = document.createElement('tr');
            const isChecked = checkboxStates[item.ID] || false;

            if (isPrintMode) {
                const tdCheckbox = document.createElement('td');
                tdCheckbox.className = 'record-checkbox-cell';
                tdCheckbox.innerHTML = `<input type="checkbox" class="record-checkbox" data-id="${item.ID}" ${isChecked ? 'checked' : ''}>`;
                tr.appendChild(tdCheckbox);

                const checkbox = tdCheckbox.querySelector('.record-checkbox');
                checkbox.addEventListener('change', () => {
                    checkboxStates[item.ID] = checkbox.checked;
                });
            }

            columnsConfig.forEach(col => {
                if (col.visible) {
                    const td = document.createElement('td');
                    switch (col.id) {
                        case 'model':
                            td.textContent = item.MODEL || '';
                            break;
                        case 'serial':
                            td.textContent = item.SERIAL_CODE || '';
                            break;
                        case 'inventoryCode':
                            td.textContent = item.INVENTORY_CODE || '';
                            break;
                        case 'pcName':
                            td.textContent = item.PC_NAME || '';
                            break;
                        case 'ip':
                            td.textContent = item.IP || '';
                            break;
                        case 'responsible':
                            if (item.RESPONSIBLE_USER_ID) {
                                td.textContent = getUserName(references.users, item.RESPONSIBLE_USER_ID);
                            } else if (item.SURNAME || item.NAME) {
                                td.textContent = `${item.SURNAME || ''} ${item.NAME || ''} ${item.PATRONYMIC || ''}`.trim();
                            } else {
                                td.textContent = 'Не указано';
                            }
                            break;
                        case 'comment':
                            td.textContent = item.COMMENT || '';
                            break;
                        case 'type':
                            td.textContent = getReferenceName(references.types, item.TYPE_ID, 'TYPE_NAME');
                            break;
                        case 'company':
                            td.textContent = getReferenceName(references.companies, item.COMPANY_ID, 'COMPANY_NAME');
                            break;
                        case 'location':
                            td.textContent = getReferenceName(references.locations, item.LOCATION_ID, 'LOCATION_NAME');
                            break;
                        case 'status':
                            td.textContent = getReferenceName(references.statuses, item.STATUS_ID, 'STATUS_NAME');
                            break;
                    }
                    tr.appendChild(td);
                }
            });

            const tdArrow = document.createElement('td');
            tdArrow.className = 'arrow-cell';
            tdArrow.innerHTML = '<span class="arrow">→</span>';
            tdArrow.addEventListener('click', () => {
                if (!isPrintMode) openEditInventoryPopup(item.ID);
            });
            tr.appendChild(tdArrow);

            let isSelectingText = false;
            let mouseDownTime = 0;

            tr.addEventListener('mousedown', (e) => {
                mouseDownTime = Date.now();
                isSelectingText = false;
            });

            tr.addEventListener('mouseup', (e) => {
                const mouseUpTime = Date.now();
                const timeDiff = mouseUpTime - mouseDownTime;

                const selection = window.getSelection();
                if (selection.toString().length > 0) {
                    isSelectingText = true;
                }

                if (timeDiff > 200) {
                    isSelectingText = true;
                }
            });

            tr.addEventListener('click', (e) => {
                if (isSelectingText || e.target.className === 'arrow') {
                    e.preventDefault();
                    return;
                }

                if (isPrintMode) {
                    const checkbox = tr.querySelector('.record-checkbox');
                    if (e.target !== checkbox && e.target.className !== 'arrow') {
                        checkbox.checked = !checkbox.checked;
                        checkboxStates[item.ID] = checkbox.checked;
                    }
                } else {
                    openEditInventoryPopup(item.ID);
                }
            });

            if (isPrintMode) {
                const checkbox = tr.querySelector('.record-checkbox');
                checkbox.addEventListener('click', (e) => {
                    e.stopPropagation();
                });
            }

            tableBody.appendChild(tr);
        });
    }

    function openAddInventoryPopup() {
        if (userPermissions === 'view') {
            alert('У вас нет прав на добавление записи');
            return;
        }
        currentInventoryId = null;
        currentInventoryResponsibleId = null;
        document.getElementById('inventoryPopupTitle').textContent = 'Добавление записи';
        const fields = [
            'invModel', 'invSerial', 'invInventoryCode', 'invPcName', 'invIp',
            'invResponsibleSearch', 'invComment', 'invType', 'invCompany', 'invLocation', 'invStatus',
            'invSurname', 'invName', 'invPatronymic', 'invEmail', 'invNumber'
        ];
        fields.forEach(id => {
            const field = document.getElementById(id);
            if (field) field.value = '';
        });

        document.getElementById('responsibleSearchGroup').style.display = 'block';
        document.getElementById('nonBitrixDetailsGroup').style.display = 'none';
        document.getElementById('invResponsibleSearch').value = '';
        document.getElementById('invResponsibleUserSelect').style.display = 'none';

        document.getElementById('popupQrCode').style.display = 'none';
        document.getElementById('inventoryButtons').innerHTML = `
            <button id="saveInventoryBtn" onclick="saveInventory()">Сохранить</button>
        `;

        document.getElementById('inventoryPopupOverlay').style.display = 'block';
        document.getElementById('inventoryPopup').style.display = 'block';

        fetchToken().then(access_token => {
            loadBitrixUsers(access_token).then(users => {
                references.users = users;
                searchResponsibleUser(''); // Инициализация списка
            }).catch(err => console.error('Ошибка обновления списка пользователей:', err));
        }).catch(err => console.error('Ошибка получения токена:', err));
    }
    function searchResponsibleUser(searchText) {
        const responsibleSearch = document.getElementById('invResponsibleSearch');
        const userSelect = document.getElementById('invResponsibleUserSelect');
        const nonBitrixDetailsGroup = document.getElementById('nonBitrixDetailsGroup');

        if (!responsibleSearch || !userSelect || !nonBitrixDetailsGroup) {
            console.error('Отсутствуют элементы формы:', { responsibleSearch, userSelect, nonBitrixDetailsGroup });
            return;
        }

        if (!searchText) {
            userSelect.innerHTML = '<option value="">Выберите пользователя...</option>';
            userSelect.style.display = 'none';
            nonBitrixDetailsGroup.style.display = 'none';
            return;
        }

        const normalizedSearch = searchText.trim().toLowerCase();
        const users = references.users || [];
        if (!users.length) {
            console.warn('Список пользователей пуст:', references.users);
            userSelect.innerHTML = '<option value="not_in_bitrix">Нет в Битрикс</option>';
            userSelect.style.display = 'block';
            return;
        }

        const matches = users.filter(user => {
            const fullName = `${user.LAST_NAME || ''} ${user.NAME || ''} ${user.SECOND_NAME || ''}`.trim().toLowerCase();
            return fullName.includes(normalizedSearch);
        });

        userSelect.innerHTML = '<option value="">Выберите пользователя...</option>';
        if (matches.length > 0) {
            matches.forEach(user => {
                const fullName = `${user.LAST_NAME} ${user.NAME} ${user.SECOND_NAME}`.trim() || `Пользователь ID ${user.ID}`;
                const option = document.createElement('option');
                option.value = user.ID;
                option.textContent = fullName;
                userSelect.appendChild(option);
            });
            userSelect.style.display = 'block';
        } else {
            userSelect.innerHTML += '<option value="not_in_bitrix">Нет в Битрикс</option>';
            userSelect.style.display = 'block';
        }

        userSelect.addEventListener('change', function() {
            const selectedValue = this.value;
            if (selectedValue === 'not_in_bitrix') {
                userSelect.style.display = 'none';
                nonBitrixDetailsGroup.style.display = 'block';
                splitFIO(responsibleSearch.value);
            } else if (selectedValue) {
                nonBitrixDetailsGroup.style.display = 'none';
            } else {
                nonBitrixDetailsGroup.style.display = 'none';
            }
        });
    }

    function splitFIO(fullName) {
        const parts = fullName.trim().split(/\s+/).filter(part => part);
        document.getElementById('invSurname').value = parts[0] || '';
        document.getElementById('invName').value = parts[1] || '';
        document.getElementById('invPatronymic').value = parts[2] || '';
        document.getElementById('invEmail').value = '';
        document.getElementById('invNumber').value = '';
    }

    function splitFIO(fullName) {
        const parts = fullName.trim().split(/\s+/).filter(part => part);
        document.getElementById('invSurname').value = parts[0] || '';
        document.getElementById('invName').value = parts[1] || '';
        document.getElementById('invPatronymic').value = parts[2] || '';
        document.getElementById('invEmail').value = '';
        document.getElementById('invNumber').value = '';
    }
    function openEditInventoryPopup(id) {
        fetchToken().then(access_token => {
            BX24.callMethod('custom.getiplusinventory', { id: id }, function(result) {
                if (result.error()) {
                    alert('Ошибка загрузки записи: ' + result.error());
                    console.error('Ошибка REST:', result.error());
                    return;
                }

                const item = result.data().result[0];
                if (!item) {
                    alert('Запись не найдена');
                    return;
                }

                currentInventoryId = item.ID;
                currentInventoryResponsibleId = item.RESPONSIBLE_USER_ID ? item.RESPONSIBLE_USER_ID.toString() : null;
                document.getElementById('inventoryPopupTitle').textContent = 'Редактирование записи #' + currentInventoryId;

                document.getElementById('invModel').value = item.MODEL || '';
                document.getElementById('invSerial').value = item.SERIAL_CODE || '';
                document.getElementById('invInventoryCode').value = item.INVENTORY_CODE || '';
                document.getElementById('invPcName').value = item.PC_NAME || '';
                document.getElementById('invIp').value = item.IP || '';
                document.getElementById('invComment').value = item.COMMENT || '';
                document.getElementById('invType').value = item.TYPE_ID || '';
                document.getElementById('invCompany').value = item.COMPANY_ID || '';
                document.getElementById('invLocation').value = item.LOCATION_ID || '';
                document.getElementById('invStatus').value = item.STATUS_ID || '';

                const responsibleSearch = document.getElementById('invResponsibleSearch');
                const nonBitrixDetailsGroup = document.getElementById('nonBitrixDetailsGroup');

                responsibleSearch.value = '';
                nonBitrixDetailsGroup.style.display = 'none';

                loadBitrixUsers(access_token).then(users => {
                    references.users = users;
                    if (currentInventoryResponsibleId) {
                        const user = users.find(u => u.ID.toString() === currentInventoryResponsibleId);
                        responsibleSearch.value = user ? `${user.LAST_NAME} ${user.NAME} ${user.SECOND_NAME}`.trim() : '';
                        searchResponsibleUser(responsibleSearch.value); // Инициализация селекта
                    } else if (item.SURNAME || item.NAME) {
                        responsibleSearch.value = `${item.SURNAME || ''} ${item.NAME || ''} ${item.PATRONYMIC || ''}`.trim();
                        nonBitrixDetailsGroup.style.display = 'block';
                        document.getElementById('invSurname').value = item.SURNAME || '';
                        document.getElementById('invName').value = item.NAME || '';
                        document.getElementById('invPatronymic').value = item.PATRONYMIC || '';
                        document.getElementById('invEmail').value = item.EMAIL || '';
                        document.getElementById('invNumber').value = item.NUMBER || '';
                    }

                    document.getElementById('popupQrCode').style.display = 'block';
                    const inventoryButtons = document.getElementById('inventoryButtons');
                    if (!inventoryButtons) {
                        console.error('Элемент inventoryButtons не найден');
                        return;
                    }
                    if (userPermissions === 'view') {
                        inventoryButtons.innerHTML = `
                            <button onclick="showQRCode()">Показать QR-код</button>
                            <button onclick="goToOperationHistory(${currentInventoryId})">История операций</button>
                        `;
                        makePopupFieldsReadOnly();
                    } else if (userPermissions === 'edit' || userPermissions === 'full') {
                        inventoryButtons.innerHTML = `
                            <button id="saveInventoryBtn" onclick="saveInventory()">Сохранить</button>
                            <button id="deleteInventoryBtn" onclick="deleteInventory()">Удалить</button>
                            <button id="operationsButton" onclick="openOperationsList()">Операции</button>
                            <button onclick="showQRCode()">Показать QR-код</button>
                            <button onclick="goToOperationHistory(${currentInventoryId})">История операций</button>
                        `;
                        makePopupFieldsEditable();
                    }

                    document.getElementById('inventoryPopupOverlay').style.display = 'block';
                    document.getElementById('inventoryPopup').style.display = 'block';
                }).catch(err => {
                    console.error('Ошибка загрузки списка пользователей:', err);
                    alert('Ошибка загрузки списка пользователей: ' + err);
                    nonBitrixDetailsGroup.style.display = item.SURNAME || item.NAME ? 'block' : 'none';
                    if (item.SURNAME || item.NAME) {
                        responsibleSearch.value = `${item.SURNAME || ''} ${item.NAME || ''} ${item.PATRONYMIC || ''}`.trim();
                        document.getElementById('invSurname').value = item.SURNAME || '';
                        document.getElementById('invName').value = item.NAME || '';
                        document.getElementById('invPatronymic').value = item.PATRONYMIC || '';
                        document.getElementById('invEmail').value = item.EMAIL || '';
                        document.getElementById('invNumber').value = item.NUMBER || '';
                    }
                });
            }, { auth: access_token });
        }).catch(err => console.error('Ошибка получения токена:', err));
    }

    function closeInventoryPopup() {
        currentInventoryId = null;
        currentInventoryResponsibleId = null;
        document.getElementById('popupQrCode').innerHTML = '';
        document.getElementById('popupQrCode').style.display = 'none';
        document.getElementById('inventoryPopupOverlay').style.display = 'none';
        document.getElementById('inventoryPopup').style.display = 'none';
    }

    function saveInventory() {
        if (userPermissions === 'view') {
            alert('У вас нет прав на сохранение записи');
            return;
        }
        const model = document.getElementById('invModel').value.trim();
        const serial = document.getElementById('invSerial').value.trim();
        const inventoryCode = document.getElementById('invInventoryCode').value.trim();
        const pcName = document.getElementById('invPcName').value.trim();
        const ip = document.getElementById('invIp').value.trim();
        const comment = document.getElementById('invComment').value.trim();
        const typeId = parseInt(document.getElementById('invType').value) || 0;
        const companyId = parseInt(document.getElementById('invCompany').value) || 0;
        const locationId = parseInt(document.getElementById('invLocation').value) || 0;
        const statusId = parseInt(document.getElementById('invStatus').value) || 0;

        const responsibleSearch = document.getElementById('invResponsibleSearch');
        const userSelect = document.getElementById('invResponsibleUserSelect');
        const nonBitrixDetailsGroup = document.getElementById('nonBitrixDetailsGroup');
        const selectedUserId = userSelect.value;
        const surname = document.getElementById('invSurname').value.trim();
        const name = document.getElementById('invName').value.trim();
        const patronymic = document.getElementById('invPatronymic').value.trim();
        const email = document.getElementById('invEmail').value.trim();
        const number = document.getElementById('invNumber').value.trim();

        let responsibleUserId = null;

        if (selectedUserId === 'not_in_bitrix' || nonBitrixDetailsGroup.style.display !== 'none') {
            responsibleUserId = null;
            if (!surname || !name) {
                alert('Фамилия и имя обязательны, если пользователь не в Битрикс.');
                return;
            }
            const emailInput = document.getElementById('invEmail');
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                alert('Введите корректный email.');
                emailInput.setCustomValidity('Введите корректный email');
                emailInput.reportValidity();
                return;
            }
            const phoneRegex = /^\+?\d{10,15}$/;
            if (number && !phoneRegex.test(number)) {
                alert('Введите корректный номер телефона (10-15 цифр, может начинаться с "+").');
                document.getElementById('invNumber').setCustomValidity('Некорректный номер телефона');
                document.getElementById('invNumber').reportValidity();
                return;
            }
        } else if (selectedUserId) {
            responsibleUserId = parseInt(selectedUserId);
        } else {
            alert('Выберите пользователя или введите ФИО для "Нет в Битрикс".');
            return;
        }

        if (!model || !serial || !inventoryCode) {
            alert('Поля "Модель", "Серийный номер" и "Инвентарный номер" обязательны');
            return;
        }

        fetchToken().then(access_token => {
            const params = {
                model: model,
                serial_code: serial,
                inventory_code: inventoryCode,
                pc_name: pcName,
                ip: ip,
                responsible_user_id: responsibleUserId,
                surname: (selectedUserId === 'not_in_bitrix' || nonBitrixDetailsGroup.style.display !== 'none') ? surname : null,
                name: (selectedUserId === 'not_in_bitrix' || nonBitrixDetailsGroup.style.display !== 'none') ? name : null,
                patronymic: (selectedUserId === 'not_in_bitrix' || nonBitrixDetailsGroup.style.display !== 'none') ? patronymic : null,
                email: (selectedUserId === 'not_in_bitrix' || nonBitrixDetailsGroup.style.display !== 'none') ? email : null,
                number: (selectedUserId === 'not_in_bitrix' || nonBitrixDetailsGroup.style.display !== 'none') ? number : null,
                comment: comment,
                type_id: typeId,
                company_id: companyId,
                location_id: locationId,
                status_id: statusId
            };

            if (currentInventoryId) {
                params.id = currentInventoryId;
                BX24.callMethod('custom.updateiplusinventory', params, function(result) {
                    if (result.error()) {
                        alert('Ошибка обновления: ' + result.error());
                        return;
                    }
                    alert(result.data().message || 'Запись обновлена');
                    closeInventoryPopup();
                    loadInventoryList(access_token);
                }, { auth: access_token });
            } else {
                BX24.callMethod('custom.addiplusinventory', params, function(result) {
                    if (result.error()) {
                        alert('Ошибка добавления: ' + result.error());
                        console.error('Ошибка REST:', result.error());
                        return;
                    }

                    const data = result.data();
                    let newInventoryId;
                    if (data.result && data.result.id) {
                        newInventoryId = data.result.id;
                    } else if (data.id) {
                        newInventoryId = data.id;
                    } else {
                        alert('Ошибка: сервер не вернул ID новой записи');
                        console.error('Некорректный формат ответа:', data);
                        return;
                    }

                    generateAndSaveQRCode(newInventoryId, access_token).then(() => {
                        alert(data.message || 'Запись добавлена');
                        closeInventoryPopup();
                        loadInventoryList(access_token);
                    }).catch(err => {
                        console.error('Ошибка при генерации QR-кода:', err);
                        alert('Запись добавлена, но QR-код не удалось сохранить');
                        closeInventoryPopup();
                        loadInventoryList(access_token);
                    });
                }, { auth: access_token });
            }
        }).catch(err => console.error('Ошибка получения токена:', err));
    }

    function generateAndSaveQRCode(inventoryId, access_token) {
        return new Promise((resolve, reject) => {
            const qrCodeData = `https://predprod.reforma-sk.ru/local-pril/inventory.php?id=${inventoryId}`;
            const canvas = document.createElement('canvas');

            QRCode.toCanvas(canvas, qrCodeData, { width: 200 }, (error) => {
                if (error) {
                    console.error('Ошибка генерации QR-кода:', error);
                    reject(error);
                    return;
                }

                const qrCodeDataURL = canvas.toDataURL("image/png");
                BX24.callMethod('custom.saveqrforiplusinventory', {
                    id: inventoryId,
                    qr_code: qrCodeDataURL
                }, function(result) {
                    if (result.error()) {
                        reject(new Error('Ошибка сохранения QR-кода: ' + result.error()));
                        return;
                    }
                    resolve();
                }, { auth: access_token });
            });
        });
    }

    function showQRCode() {
        if (!currentInventoryId) {
            alert('ID записи не определен');
            return;
        }

        fetchToken().then(access_token => {
            BX24.callMethod('custom.getiplusinventory', { id: currentInventoryId }, function(result) {
                if (result.error()) {
                    alert('Ошибка загрузки записи: ' + result.error());
                    console.error('Ошибка REST:', result.error());
                    return;
                }

                const item = result.data().result[0];
                if (!item) {
                    alert('Запись не найдена');
                    return;
                }

                const qrCodeBase64 = item.QR;
                const qrCodeElement = document.getElementById('popupQrCode');

                if (qrCodeBase64) {
                    qrCodeElement.innerHTML = `<img src="${qrCodeBase64}" width="200" />`;
                } else {
                    qrCodeElement.innerHTML = '<p>QR-код не найден в базе данных</p>';
                }
            }, { auth: access_token });
        }).catch(err => {
            console.error('Ошибка получения данных:', err);
            alert('Ошибка получения данных');
        });
    }

    function deleteInventory() {
        if (userPermissions === 'view') {
            alert('У вас нет прав на удаление записи');
            return;
        }
        if (!currentInventoryId) return;
        if (!confirm('Действительно удалить эту запись?')) return;
        fetchToken().then(access_token => {
            BX24.callMethod('custom.deleteiplusinventory', { id: currentInventoryId }, function(result) {
                if (result.error()) {
                    alert('Ошибка удаления: ' + result.error());
                    return;
                }
                alert(result.data().message || 'Запись удалена');
                closeInventoryPopup();
                loadInventoryList(access_token);
            }, { auth: access_token });
        }).catch(err => console.error('Ошибка получения токена:', err));
    }

    function showTokenExpiredMessage() {
        document.getElementById('inventoryTableBody').innerHTML = `
            <tr><td colspan="12">
                <p class="text-danger">Токен авторизации истёк. Пожалуйста, обновите токены через веб-версию приложения:</p>
                <ul>
                    <li>Откройте <a href="https://predprod.reforma-sk.ru/local-pril/install.html" class="text-primary">веб-версию приложения</a> в браузере на компьютере.</li>
                    <li>Пройдите авторизацию заново, чтобы обновить токены.</li>
                </ul>
                <p>После обновления токены станут доступны в мобильной/веб-версии.</p>
            </td></tr>
        `;
    }

    function openCameraPopup() {
        document.getElementById('cameraPopupOverlay').style.display = 'block';
        document.getElementById('cameraPopup').style.display = 'block';
        startCamera('environment');
    }

    function closeCameraPopup() {
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
        }
        document.getElementById('cameraPopupOverlay').style.display = 'none';
        document.getElementById('cameraPopup').style.display = 'none';
        qrOutput.innerHTML = '';
    }

    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const photoPreview = document.getElementById('photo-preview');
    const capturePhotoButton = document.getElementById('capture-photo');
    const qrOutput = document.getElementById('qr-output');

    let currentStream = null;
    let useFrontCamera = false;
    let scanningQR = false;

    async function startCamera(facingMode = 'environment') {
        const video = document.getElementById('video');
        const cameraPopup = document.getElementById('cameraPopup');

        const loadingMessage = document.createElement('p');
        loadingMessage.id = 'camera-loading-message';
        loadingMessage.textContent = 'Запрашиваем доступ к камере...';
        loadingMessage.style.color = '#e50045';
        loadingMessage.style.textAlign = 'center';
        cameraPopup.insertBefore(loadingMessage, video);

        try {
            if (navigator.permissions && navigator.permissions.query) {
                const permissionStatus = await navigator.permissions.query({ name: 'camera' });

                if (permissionStatus.state === 'denied') {
                    throw new Error('Доступ к камере запрещен. Пожалуйста, разрешите доступ в настройках устройства.');
                }

                if (permissionStatus.state === 'prompt') {
                    console.log('Ожидаем разрешения пользователя на доступ к камере...');
                }

                permissionStatus.onchange = () => {
                    if (permissionStatus.state === 'denied') {
                        loadingMessage.textContent = 'Доступ к камере запрещен. Пожалуйста, разрешите доступ в настройках устройства.';
                        loadingMessage.style.color = 'red';
                        video.style.display = 'none';
                    } else if (permissionStatus.state === 'granted') {
                        loadingMessage.textContent = 'Доступ к камере получен.';
                        loadingMessage.style.color = 'green';
                        setTimeout(() => loadingMessage.remove(), 1000);
                        video.style.display = 'block';
                    }
                };
            }

            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
            }

            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode }
            });

            video.srcObject = stream;
            currentStream = stream;

            loadingMessage.textContent = 'Камера готова.';
            loadingMessage.style.color = 'green';
            setTimeout(() => loadingMessage.remove(), 1000);

            video.style.display = 'block';

            startQRScanner();
        } catch (error) {
            console.error('Ошибка доступа к камере:', error);
            loadingMessage.textContent = error.message || 'Не удалось получить доступ к камере. Проверьте настройки устройства.';
            loadingMessage.style.color = 'red';
            video.style.display = 'none';
        }
    }

    function sendPhotoToServer(photoDataUrl) {
        fetch('save_photo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ photo: photoDataUrl })
        })
            .then(response => response.json())
            .then(data => console.log('Фото сохранено:', data))
            .catch(error => console.error('Ошибка сохранения фото:', error));
    }

    function startQRScanner() {
        const canvasElement = document.createElement('canvas');
        const canvasContext = canvasElement.getContext('2d');

        function scanQR() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvasElement.width = video.videoWidth;
                canvasElement.height = video.videoHeight;
                canvasContext.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
                const imageData = canvasContext.getImageData(0, 0, canvasElement.width, canvasElement.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);

                if (code) {
                    handleQRCode(code.data);
                }
            }
            if (scanningQR) {
                requestAnimationFrame(scanQR);
            }
        }

        scanningQR = true;
        scanQR();
    }

    function handleQRCode(data) {
        if (data.startsWith('https://bitrix24.com') || data.startsWith('https://predprod.reforma-sk.ru')) {
            qrOutput.innerHTML = `Ссылка на Битрикс24: <a href="${data}" target="_blank">${data}</a>`;
            closeCameraPopup();
            const recordId = new URL(data).searchParams.get('id');

            if (recordId) {
                fetchToken().then(access_token => {
                    BX24.callMethod('custom.getiplusinventory', { id: recordId }, function(result) {
                        if (result.error()) {
                            console.error('Ошибка загрузки записи:', result.error());
                            return;
                        }
                        const record = result.data().result[0];
                        if (record) {
                            openEditInventoryPopup(record.ID);
                        } else {
                            console.error('Запись не найдена');
                        }
                    }, { auth: access_token });
                }).catch(error => console.error('Ошибка получения токена:', error));
            }
        } else {
            qrOutput.innerHTML = `
                <p style="color: red;">Внимание! Внешняя ссылка:</p>
                <p>${data}</p>
                <p>Будьте осторожны.</p>
            `;
        }
        scanningQR = true;
    }

    function getUserPermissions(userId, access_token) {
        return new Promise((resolve, reject) => {
            BX24.callMethod(
                'custom.userrules',
                { action: 'get_permissions', user_id: userId },
                function(result) {
                    if (result.error()) {
                        reject(result.error());
                    } else {
                        const permission = result.data().result.permission || 'view';
                        resolve(permission);
                    }
                },
                { auth: access_token }
            );
        });
    }

    function makePopupFieldsReadOnly() {
        const fields = [
            'invModel', 'invSerial', 'invInventoryCode', 'invPcName', 'invIp',
            'invComment', 'invType', 'invCompany', 'invLocation', 'invStatus',
            'invResponsibleUserSelect', 'invSurname', 'invName', 'invPatronymic', 'invEmail', 'invNumber'
        ];
        fields.forEach(id => {
            const field = document.getElementById(id);
            if (field) {
                field.setAttribute('readonly', 'readonly');
                if (field.tagName === 'SELECT') {
                    field.setAttribute('disabled', 'disabled');
                }
            }
        });
    }

    function makePopupFieldsEditable() {
        const fields = [
            'invModel', 'invSerial', 'invInventoryCode', 'invPcName', 'invIp',
            'invComment', 'invType', 'invCompany', 'invLocation', 'invStatus',
            'invResponsibleUserSelect', 'invSurname', 'invName', 'invPatronymic', 'invEmail', 'invNumber'
        ];
        fields.forEach(id => {
            const field = document.getElementById(id);
            if (field) {
                field.removeAttribute('readonly');
                if (field.tagName === 'SELECT') {
                    field.removeAttribute('disabled');
                }
            }
        });
    }

    function waitForElement(id, callback) {
        const element = document.getElementById(id);
        if (element) {
            callback(element);
        } else {
            setTimeout(() => waitForElement(id, callback), 100);
        }
    }

    function openOperationsList() {
        if (userPermissions === 'view') {
            alert('У вас нет прав на выполнение операций');
            return;
        }
        if (!currentInventoryId) {
            alert('Сначала сохраните запись');
            return;
        }

        const currentStatusId = document.getElementById('invStatus').value;
        if (!currentStatusId) {
            alert('Выберите статус инвентаря');
            return;
        }

        fetchToken().then(access_token => {
            const params = { status_id: currentStatusId, user_id: currentUserId };
            BX24.callMethod('custom.getIplusReferenceOperations', params, function(result) {
                if (result.error()) {
                    alert('Ошибка загрузки операций: ' + result.error());
                    console.error('Ошибка REST:', result.error());
                    return;
                }

                const operations = result.data().result || [];
                if (!Array.isArray(operations)) {
                    console.error('Некорректный формат данных операций:', operations);
                    alert('Ошибка: данные операций имеют некорректный формат.');
                    return;
                }

                let allOperations = operations;
                const filteredOperations = filterOperationsByPermissions(allOperations);

                document.getElementById('operationsPopupOverlay').style.display = 'block';
                document.getElementById('operationsPopup').style.display = 'block';

                waitForElement('executionRole', () => {
                    setupRoleSelection(filteredOperations, allOperations);
                    displayOperationsList(filteredOperations);
                });
            }, { auth: access_token });
        }).catch(err => console.error('Ошибка получения токена:', err));
    }

    function setupRoleSelection(operations, allOperations) {
        const roleSelection = document.getElementById('roleSelection');
        const executionRoleSelect = document.getElementById('executionRole');

        if (!executionRoleSelect) {
            console.error('Элемент с id="executionRole" не найден в DOM');
            return;
        }

        if (userPermissions === 'full') {
            roleSelection.style.display = 'block';
            executionRole = 'direct';
            executionRoleSelect.value = executionRole;
            executionRoleSelect.addEventListener('change', (e) => {
                executionRole = e.target.value;
                const filteredOperations = filterOperationsByPermissions(allOperations);
                displayOperationsList(filteredOperations);
            });
        } else {
            roleSelection.style.display = 'none';
            executionRole = 'direct';
        }
    }

    function filterOperationsByPermissions(operations) {
        if (!Array.isArray(operations)) {
            console.error('Ожидался массив операций, получено:', operations);
            return [];
        }

        if (userPermissions === 'full') {
            return operations;
        }

        if (userPermissions === 'edit') {
            return operations.filter(op => {
                try {
                    const isAvailableToAll = op.IS_AVAILABLE_TO_ALL === '1';
                    const allowedUsers = op.ALLOWED_USERS ? JSON.parse(op.ALLOWED_USERS || '[]') : [];
                    return isAvailableToAll || allowedUsers.includes(currentUserId.toString());
                } catch (e) {
                    console.error('Ошибка парсинга ALLOWED_USERS для операции:', op, e);
                    return false;
                }
            });
        }

        return [];
    }

    function displayOperationsList(operations) {
        const operationsList = document.getElementById('operationsList');
        operationsList.innerHTML = '';

        const currentStatusId = document.getElementById('invStatus').value;
        const validOperations = operations.filter(op => {
            let initialStatuses;
            try {
                initialStatuses = JSON.parse(op.INITIAL_STATUSES || '[]');
            } catch (e) {
                console.error(`Ошибка парсинга INITIAL_STATUSES для операции ID ${op.ID}:`, e);
                return false;
            }
            return initialStatuses.includes(currentStatusId);
        });

        let filteredOperations = filterOperationsByPermissions(validOperations);
        filteredOperations = filteredOperations.filter(op => {
            if (executionRole === 'direct') {
                return op.IS_DIRECT === '1';
            } else if (executionRole === 'reverse') {
                return op.IS_REVERSE === '1';
            }
            return false;
        });

        if (!filteredOperations.length) {
            operationsList.innerHTML = '<li>Нет доступных операций для текущего статуса и типа операции</li>';
            return;
        }

        filteredOperations.forEach(op => {
            const afterStatus = parseInt(op.AFTER_OPERATION_STATUS, 10);
            if (!afterStatus || isNaN(afterStatus)) {
                console.error(`Некорректный AFTER_OPERATION_STATUS для операции ID ${op.ID}:`, op.AFTER_OPERATION_STATUS);
                return;
            }

            const li = document.createElement('li');
            const displayName = op.NAME_OPERATION || `${op.DIRECT_OPERATION_NAME} / ${op.REVERSE_OPERATION_NAME}` || 'Операция #' + op.ID;
            li.textContent = displayName;
            li.setAttribute('data-operation-id', op.ID);
            li.setAttribute('data-after-status', afterStatus);
            li.setAttribute('data-requires-confirmation', op.REQUIRES_CONFIRMATION || '0');
            li.addEventListener('click', () => {
                operationsList.querySelectorAll('li').forEach(item => item.style.backgroundColor = '');
                li.style.backgroundColor = '#e0e0e0';
                selectedOperationId = op.ID;
                selectedAfterStatus = afterStatus;
                selectedRequiresConfirmation = op.REQUIRES_CONFIRMATION || '0';
            });
            operationsList.appendChild(li);
        });
    }

    function closeOperationsPopup() {
        selectedOperationId = null;
        selectedAfterStatus = null;
        executionRole = null;
        document.getElementById('operationsPopupOverlay').style.display = 'none';
        document.getElementById('operationsPopup').style.display = 'none';
        document.getElementById('roleSelection').style.display = 'none';
    }

    function confirmOperationSelection() {
        console.log('confirmOperationSelection: selectedOperationId =', selectedOperationId, 'selectedAfterStatus =', selectedAfterStatus);
        if (!selectedOperationId || !selectedAfterStatus) {
            alert('Выберите операцию');
            return;
        }
        if (executionRole === 'direct') {
            openRecipientSelectionPopup();
        } else {
            openCommentPopup({});
        }
    }

    function openRecipientSelectionPopup() {
        const recipientPopup = document.getElementById('recipientPopup');
        const overlay = document.getElementById('recipientPopupOverlay');
        const bitrixRecipientSelectGroup = document.getElementById('bitrixRecipientSelectGroup');
        const nonBitrixRecipientDetailsGroup = document.getElementById('nonBitrixRecipientDetailsGroup');
        const noBitrixRecipientCheckbox = document.getElementById('noBitrixRecipient');
        const recipientUserSelect = document.getElementById('recipientUserSelect');

        // Сброс значений полей
        recipientUserSelect.value = '';
        document.getElementById('recipientSurname').value = '';
        document.getElementById('recipientName').value = '';
        document.getElementById('recipientPatronymic').value = '';
        document.getElementById('recipientEmail').value = '';
        document.getElementById('recipientNumber').value = '';
        noBitrixRecipientCheckbox.checked = false;
        bitrixRecipientSelectGroup.style.display = 'block';
        nonBitrixRecipientDetailsGroup.style.display = 'none';

        // Заполнение выпадающего списка пользователей
        fillBitrixUsersDropdown(recipientUserSelect, references.users);

        // Замена чекбокса для корректной обработки событий
        const newCheckbox = noBitrixRecipientCheckbox.cloneNode(true);
        noBitrixRecipientCheckbox.parentNode.replaceChild(newCheckbox, noBitrixRecipientCheckbox);

        newCheckbox.addEventListener('change', () => {
            if (newCheckbox.checked) {
                bitrixRecipientSelectGroup.style.display = 'none';
                nonBitrixRecipientDetailsGroup.style.display = 'block';
                recipientUserSelect.value = '';
            } else {
                bitrixRecipientSelectGroup.style.display = 'block';
                nonBitrixRecipientDetailsGroup.style.display = 'none';
                document.getElementById('recipientSurname').value = '';
                document.getElementById('recipientName').value = '';
                document.getElementById('recipientPatronymic').value = '';
                document.getElementById('recipientEmail').value = '';
                document.getElementById('recipientNumber').value = '';
            }
        });

        // Увеличение z-index для отображения поверх operationsPopup
        overlay.style.zIndex = '2500';
        recipientPopup.style.zIndex = '2501';

        // Отображение попапа
        overlay.style.display = 'block';
        recipientPopup.style.display = 'block';
    }

    function closeRecipientPopup() {
        const overlay = document.getElementById('recipientPopupOverlay');
        const recipientPopup = document.getElementById('recipientPopup');

        // Восстановление исходных значений z-index
        overlay.style.zIndex = '1500';
        recipientPopup.style.zIndex = '1501';

        // Скрытие попапа
        overlay.style.display = 'none';
        recipientPopup.style.display = 'none';
    }

    function confirmRecipientSelection() {
        const noBitrixRecipientChecked = document.getElementById('noBitrixRecipient').checked;
        const recipientId = noBitrixRecipientChecked ? null : document.getElementById('recipientUserSelect').value;
        const manualRecipient = noBitrixRecipientChecked ? {
            surname: document.getElementById('recipientSurname').value.trim(),
            name: document.getElementById('recipientName').value.trim(),
            patronymic: document.getElementById('recipientPatronymic').value.trim(),
            email: document.getElementById('recipientEmail').value.trim(),
            number: document.getElementById('recipientNumber').value.trim()
        } : null;

        if (noBitrixRecipientChecked) {
            if (!manualRecipient.surname || !manualRecipient.name) {
                alert('Фамилия и имя обязательны для пользователя не из Bitrix.');
                return;
            }
            const emailInput = document.getElementById('recipientEmail');
            if (manualRecipient.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(manualRecipient.email)) {
                alert('Введите корректный email.');
                emailInput.setCustomValidity('Введите корректный email');
                emailInput.reportValidity();
                return;
            }
            const phoneRegex = /^\+?\d{10,15}$/;
            if (manualRecipient.number && !phoneRegex.test(manualRecipient.number)) {
                alert('Введите корректный номер телефона (10-15 цифр, может начинаться с "+").');
                document.getElementById('recipientNumber').setCustomValidity('Некорректный номер телефона');
                document.getElementById('recipientNumber').reportValidity();
                return;
            }
        } else if (!recipientId) {
            alert('Выберите пользователя Bitrix.');
            return;
        }

        // Сохраняем данные реципиента для дальнейшей обработки
        window.currentRecipient = { recipientId, manualRecipient };

        closeRecipientPopup();
        openCommentPopup();
    }

    function openCommentPopup() {
        const commentPopup = document.getElementById('commentPopup');
        const overlay = document.getElementById('commentPopupOverlay');
        let commentInput = document.getElementById('commentInput');

        // Если элемента нет, создаём его
        if (!commentInput) {
            commentInput = document.createElement('input');
            commentInput.id = 'commentInput';
            commentInput.type = 'text';
            commentInput.placeholder = 'Введите комментарий';
            commentPopup.appendChild(commentInput);
            console.warn('Created commentInput dynamically because it was missing.');
        }

        // Сброс значения поля комментария
        commentInput.value = '';

        // Увеличение z-index для отображения поверх других попапов
        overlay.style.zIndex = '3000';
        commentPopup.style.zIndex = '3001';

        // Отображение попапа
        overlay.style.display = 'block';
        commentPopup.style.display = 'block';
    }

    function closeCommentPopup() {
        const overlay = document.getElementById('commentPopupOverlay');
        const commentPopup = document.getElementById('commentPopup');

        // Восстановление исходных значений z-index
        overlay.style.zIndex = '1000';
        commentPopup.style.zIndex = '1001';

        // Скрытие попапа
        overlay.style.display = 'none';
        commentPopup.style.display = 'none';
    }

    function confirmOperation() {
        console.log('confirmOperation: selectedOperationId =', selectedOperationId, 'selectedAfterStatus =', selectedAfterStatus, executionRole, window.currentRecipient);
        const comment = document.getElementById('operationComment').value.trim();
        if (!comment) {
            alert('Пожалуйста, введите комментарий, описывающий необходимость операции.');
            return;
        }
        performOperation(selectedOperationId, selectedAfterStatus, comment, executionRole, window.currentRecipient);
        closeCommentPopup();
        closeOperationsPopup();
    }

    function performOperation(operationId, afterOperationStatus, comment, role, recipientData = {}) {
    if (typeof BX24 === 'undefined') {
        console.error('BX24 не определён. Проверьте подключение модуля.');
        alert('Ошибка: BX24 не инициализирован. Перезагрузите страницу или свяжитесь с поддержкой.');
        return;
    }

    if (!currentInventoryId || isNaN(currentInventoryId) || currentInventoryId <= 0) {
        console.error('Некорректный currentInventoryId:', currentInventoryId);
        alert('Ошибка: ID инвентаря некорректен.');
        return;
    }

    if (!afterOperationStatus || isNaN(afterOperationStatus) || afterOperationStatus <= 0) {
        console.error('Некорректный afterOperationStatus:', afterOperationStatus);
        alert('Ошибка: Новый статус инвентаря обязателен и должен быть положительным числом.');
        return;
    }

    checkActiveOperations(currentInventoryId).then(result => {
        if (!result?.success) {
            alert(result?.message || 'Ошибка проверки активных операций');
            return;
        }

        if (result.hasActiveOperations) {
            alert('Нельзя выполнить операцию: активная операция уже существует (статус "В ожидании" или "В работе"). Завершите или отмените её.');
            return;
        }

        fetchToken().then(access_token => {
            const params = {
                inventory_id: Number(currentInventoryId),
                operation_id: operationId,
                new_status_id: afterOperationStatus,
                comment: comment || '',
                user_id: currentUserId,
                operation_type: role || 'direct'
            };

            let isRecipientBitrixUser = false;
            if (role === 'direct' && (!recipientData || (!recipientData.recipientId && !recipientData.manualRecipient))) {
                const responsibleUserId = document.getElementById('invResponsibleUserSelect')?.value;
                if (responsibleUserId && !isNaN(responsibleUserId)) {
                    params.responsible_user_id = Number(responsibleUserId);
                    isRecipientBitrixUser = true;
                } else {
                    params.recipient_info = {
                        surname: document.getElementById('invSurname')?.value?.trim() || 'Unknown',
                        name: document.getElementById('invName')?.value?.trim() || 'Unknown',
                        patronymic: document.getElementById('invPatronymic')?.value?.trim() || null,
                        email: document.getElementById('invEmail')?.value?.trim() || null,
                        phone: document.getElementById('invNumber')?.value?.trim() || null
                    };
                    isRecipientBitrixUser = false;
                }
            } else if (role === 'direct' && recipientData) {
                if (recipientData.recipientId && !isNaN(recipientData.recipientId)) {
                    params.responsible_user_id = Number(recipientData.recipientId);
                    isRecipientBitrixUser = true;
                } else if (recipientData.manualRecipient) {
                    params.recipient_info = {
                        surname: recipientData.manualRecipient.surname?.trim() || null,
                        name: recipientData.manualRecipient.name?.trim() || null,
                        patronymic: recipientData.manualRecipient.patronymic?.trim() || null,
                        email: recipientData.manualRecipient.email?.trim() || null,
                        phone: recipientData.manualRecipient.number?.trim() || null
                    };
                    isRecipientBitrixUser = false;
                }
            }

            console.log('Отправляемые параметры:', params); // Логирование параметров

            BX24.callMethod(
                'custom.performinventoryoperation',
                params,
                (result) => {
                    if (result.error()) {
                        const errorMsg = result.error().ex?.error_description || result.error();
                        console.error('Ошибка REST:', errorMsg);
                        alert('Ошибка операции: ' + errorMsg);
                        return;
                    }

                    const data = result.data();
                    if (!data || typeof data !== 'object') {
                        console.error('Некорректные данные от сервера:', data);
                        alert('Ошибка: сервер вернул некорректные данные.');
                        return;
                    }

                    const operationResult = data.result || data;

                    if (operationResult.success) {
                        const executionStatus = operationResult.execution_status || 'В работе';
                        let message = operationResult.message || 'Операция выполнена успешно';
                        if (executionStatus === 'В работе') {
                            message += '\nЗадача добавлена в "Задачи" со статусом "В работе".';
                        } else if (executionStatus === 'Завершена') {
                            message += '\nОперация завершена, статус инвентаря обновлён.';
                            const statusElement = document.getElementById('invStatus');
                            if (statusElement) statusElement.value = afterOperationStatus;
                        }
                        alert(message);
                        fetchToken().then(loadInventoryList);
                        closeInventoryPopup();
                        selectedOperationId = null;
                        selectedAfterStatus = null;
                        executionRole = null;
                        window.currentRecipient = null; // Исправлено с window.recipientData
                    } else {
                        console.error('Операция не выполнена:', operationResult);
                        alert('Операция не выполнена: ' + (operationResult.message || 'Неизвестная ошибка'));
                    }
                },
                { auth: access_token }
            );
        }).catch(err => {
            console.error('Ошибка получения токена:', err);
            alert('Ошибка получения токена: ' + err.message);
        });
    }).catch(err => {
        console.error('Ошибка проверки активных операций:', err);
        alert('Ошибка проверки активных операций: ' + err.message);
    });
}

    function checkActiveOperations(inventoryId) {
        return new Promise((resolve, reject) => {
            fetchToken().then(access_token => {
                BX24.callMethod(
                    'custom.checkActiveOperationsAction',
                    { inventory_id: inventoryId },
                    function(result) {
                        if (result.error()) {
                            console.error('Ошибка проверки активных операций:', result.error());
                            reject(new Error('Ошибка проверки активных операций: ' + result.error()));
                        } else {
                            resolve(result.data());
                        }
                    },
                    { auth: access_token }
                );
            }).catch(err => {
                console.error('Ошибка получения токена:', err);
                reject(err);
            });
        });
    }

    function goToOperationHistory(inventoryId) {
        window.location.href = `https://predprod.reforma-sk.ru/local-pril/history_operation_completed.php?inventory_id=${inventoryId}`;
    }

    function openFilterPopup() {
        const filterResponsibleUserSelect = document.getElementById('filterResponsibleUser');
        fillBitrixUsersDropdown(filterResponsibleUserSelect, references.users);

        document.getElementById('filterPopupOverlay').style.display = 'block';
        document.getElementById('filterPopup').style.display = 'block';
    }

    function closeFilterPopup() {
        document.getElementById('filterPopupOverlay').style.display = 'none';
        document.getElementById('filterPopup').style.display = 'none';
        document.getElementById('filterCheckbox').checked = false;
    }

    function applyFilter() {
        const filters = {
            company: document.getElementById('filterCompany').value,
            location: document.getElementById('filterLocation').value,
            type: document.getElementById('filterType').value,
            status: document.getElementById('filterStatus').value,
            responsibleUser: document.getElementById('filterResponsibleUser').value,
            search: document.getElementById('searchInput').value.trim().toLowerCase()
        };

        const isFilterApplied = !Object.values(filters).every(val => !val);
        updateFilterButtonStyle(); // Предполагаем, что эта функция обновляет стиль кнопки "Фильтр"

        const resetBtn = document.getElementById('resetFilterBtn');
        if (resetBtn) {
            resetBtn.classList.toggle('active', isFilterApplied);
            resetBtn.classList.toggle('inactive', !isFilterApplied);
        }

        fetchToken().then(access_token => {
            loadInventoryList(access_token, filters);
        }).catch(err => console.error('Ошибка получения токена:', err));

        document.getElementById('filterPopupOverlay').style.display = 'none';
        document.getElementById('filterPopup').style.display = 'none';
    }

    function cancelFilter() {
        document.getElementById('filterPopupOverlay').style.display = 'none';
        document.getElementById('filterPopup').style.display = 'none';

        document.getElementById('filterCompany').value = '';
        document.getElementById('filterLocation').value = '';
        document.getElementById('filterType').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterResponsibleUser').value = '';

        isFilterApplied = false;
        updateFilterButtonStyle();

        fetchToken().then(access_token => {
            loadInventoryList(access_token);
        }).catch(err => console.error('Ошибка получения токена:', err));
    }

    function validateEmail(input) {
        const email = input.value.trim();
        if (email === '') {
            input.setCustomValidity('');
            return;
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            input.setCustomValidity('Введите корректный email');
        } else {
            input.setCustomValidity('');
        }
    }

    function validatePhoneNumber(input) {
        const phone = input.value.trim();
        if (phone === '') {
            input.setCustomValidity('');
            return;
        }
        const phoneRegex = /^\+?\d{10,15}$/;
        if (!phoneRegex.test(phone)) {
            input.setCustomValidity('Введите корректный номер телефона (10-15 цифр, может начинаться с "+")');
        } else {
            input.setCustomValidity('');
        }
    }

    function exportToExcel() {
        const table = document.getElementById('inventoryTable');
        const rows = table.querySelectorAll('tr');
        const data = [];

        const headers = [];
        const headerRow = rows[0];
        const headerCells = headerRow.querySelectorAll('th');
        headerCells.forEach((th, index) => {
            if (index !== headerCells.length - 1) {
                headers.push(th.textContent.trim());
            }
        });
        data.push(headers);

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.querySelectorAll('td');
            const rowData = [];
            cells.forEach((td, index) => {
                if (index !== cells.length - 1) {
                    rowData.push(td.textContent.trim());
                }
            });
            data.push(rowData);
        }

        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, 'Инвентарь');

        XLSX.writeFile(wb, 'inventory.xlsx');
    }
    document.getElementById('importFileInput').addEventListener('change', handleFileImport);
    let currentMapping = null;
    let excelHeaders = [];
    let importData = [];
    let importAccessToken = null;

    function openMappingPopup(headers) {
        excelHeaders = headers;
        const mappingFields = document.getElementById('mappingFields');
        mappingFields.innerHTML = '';

        headers.forEach((header, index) => {
            const fieldGroup = document.createElement('div');
            fieldGroup.className = 'mapping-field-group';
            fieldGroup.innerHTML = `
            <label for="mapping-${index}">${header}</label>
            <select id="mapping-${index}" data-column-index="${index}">
                <option value="">Не импортировать</option>
                ${appFields.map(field => `
                    <option value="${field.app}">${field.excel}</option>
                `).join('')}
            </select>
        `;
            mappingFields.appendChild(fieldGroup);
        });

        document.getElementById('mappingPopupOverlay').style.display = 'block';
        document.getElementById('mappingPopup').style.display = 'block';
    }

    function confirmMapping() {
        const mapping = {};
        excelHeaders.forEach((header, index) => {
            const select = document.getElementById(`mapping-${index}`);
            const appField = select.value;
            if (appField) {
                mapping[header] = appField;
            }
        });

        if (Object.values(mapping).length === 0) {
            alert('Выберите хотя бы одно поле для сопоставления.');
            return;
        }

        const requiredFields = appFields.filter(f => f.required).map(f => f.app);
        const mappedFields = Object.values(mapping);
        const missingRequired = requiredFields.filter(f => !mappedFields.includes(f));
        if (missingRequired.length > 0) {
            alert(`Обязательные поля не сопоставлены: ${missingRequired.map(f => appFields.find(af => af.app === f).excel).join(', ')}`);
            return;
        }

        currentMapping = mapping;
        document.getElementById('mappingPopupOverlay').style.display = 'none';
        document.getElementById('mappingPopup').style.display = 'none';
        showLoader(0); // Запускаем лоадер перед обработкой
        processImportedData(importData, importAccessToken);
    }

    function cancelMapping() {
        currentMapping = null;
        excelHeaders = [];
        importData = [];
        importAccessToken = null;
        document.getElementById('mappingPopupOverlay').style.display = 'none';
        document.getElementById('mappingPopup').style.display = 'none';
        hideLoader();
        document.getElementById('importFileInput').value = '';
    }

    async function handleFileImport(event) {
        const file = event.target.files[0];
        if (!file) return;
        try {
            // Удаляем вызов showLoader здесь
            importAccessToken = await fetchToken();
            await updateInventoryRecords(importAccessToken);
            const data = await readExcelFile(file);
            if (!data || data.length < 1) {
                throw new Error('Файл пустой');
            }
            excelHeaders = data[0].map(h => String(h).trim());
            importData = data.slice(1).filter(row => row.some(cell => cell != null && String(cell).trim() !== ''));
            if (!importData.length) {
                throw new Error('Нет данных для обработки');
            }
            openMappingPopup(excelHeaders);
        } catch (error) {
            console.error('Ошибка импорта:', error);
            alert('Ошибка при импорте данных: ' + error.message);
            hideLoader();
            event.target.value = '';
        }
    }


    let startTime = Date.now();

    // Загружаем сохраненное сопоставление при старте
    document.addEventListener('DOMContentLoaded', () => {
        const savedMapping = localStorage.getItem('currentMapping');
        if (savedMapping) {
            currentMapping = JSON.parse(savedMapping);
            console.log('Загружено сохраненное сопоставление:', currentMapping);
        }
    });

    async function processImportedData(data, access_token) {
        startTime = Date.now();
        try {
            if (!currentMapping) {
                throw new Error('Сопоставление полей не задано');
            }

            // Сохраняем текущее сопоставление
            localStorage.setItem('currentMapping', JSON.stringify(currentMapping));

            const rows = data;
            const errors = []; // Определяем errors здесь
            const tasks = rows.map((row, index) => async () => {
                try {
                    const record = {};
                    appFields.forEach(field => {
                        record[field.app] = '';
                    });

                    excelHeaders.forEach((header, colIndex) => {
                        const appField = currentMapping[header];
                        if (appField) {
                            let value = colIndex != null ? String(row[colIndex] || '').trim() : '';
                            record[appField] = value === '' ? null : value;
                        }
                    });

                    console.log(`Обработка строки ${index + 2}:`, record);
                    const hasAnyData = Object.values(record).some(val => val !== null && val !== '');
                    if (!hasAnyData) {
                        console.log(`Строка ${index + 2} пропущена: нет данных`);
                        return;
                    }
                    await processSingleRecord(record, access_token, errors, index);
                } catch (error) {
                    errors.push(`Строка ${index + 2}: ${error.message || 'Неизвестная ошибка'}`);
                    console.error(`Ошибка в строке ${index + 2}:`, error);
                }
                const progress = ((index + 1) / rows.length) * 100;
                showLoader(progress);
            });

            await limitConcurrency(tasks, 2);
            console.log('Все задачи завершены, проверка ошибок...');

            if (errors.length > 0) {
                console.error('Ошибки импорта:', errors);
                alert('Импорт завершен с ошибками:\n' + errors.join('\n'));
            } else {
                console.log('Импорт завершен без ошибок');
                alert('Импорт успешно завершен');
                await loadInventoryList(access_token);
            }
        } catch (error) {
            console.error(`Ошибка импорта (время: ${Date.now() - startTime} мс):`, error);
            alert('Ошибка при импорте данных: ' + (error.message || 'Неизвестная ошибка'));
        } finally {
            console.log(`Финализация импорта за ${Date.now() - startTime} мс`);
            currentMapping = null;
            excelHeaders = [];
            importData = [];
            importAccessToken = null;
            hideLoader();
            document.getElementById('importFileInput').value = '';
        }
    }

    async function processSingleRecord(record, access_token, errors, index) {
        try {
            let responsibleUserId = null;
            let noBitrixUser = false;
            if (record.email) {
                responsibleUserId = await findUserByEmail(record.email, access_token);
            }
            if (!responsibleUserId && record.responsible) {
                noBitrixUser = true;
                const fioParts = (record.responsible || '').split(/\s+/).filter(part => part);
                record.surname = fioParts[0] || null;
                record.name = fioParts[1] || null;
                record.patronymic = fioParts[2] || null;
            }

            // Создание или получение ID для справочных данных
            const typeId = await getOrCreateReferenceId('types', 'TYPE_NAME', record.type_id, access_token);
            const companyId = await getOrCreateReferenceId('companies', 'COMPANY_NAME', record.company_id, access_token);
            const locationId = await getOrCreateReferenceId('locations', 'LOCATION_NAME', record.location_id, access_token);
            const statusId = await getOrCreateReferenceId('statuses', 'STATUS_NAME', record.status_id, access_token);

            // Проверка и установка обязательных полей
            const params = {
                model: record.model || 'DefaultModel',
                serial_code: record.serial_code || 'DefaultSerial',
                inventory_code: record.inventory_code || 'DefaultInventory',
                pc_name: record.pc_name || null,
                ip: record.ip || null,
                responsible_user_id: responsibleUserId || null,
                surname: noBitrixUser ? record.surname : null,
                name: noBitrixUser ? record.name : null,
                patronymic: noBitrixUser ? record.patronymic : null,
                email: noBitrixUser ? record.email : null,
                number: noBitrixUser ? record.number : null,
                comment: record.comment || null,
                type_id: typeId,
                company_id: companyId,
                location_id: locationId,
                status_id: statusId
            };

            // Проверка, что обязательные поля заполнены
            if (!params.model || !params.serial_code || !params.inventory_code) {
                errors.push(`Строка ${index + 2}: Обязательные поля (Model, Serial number, Inventory number) не заполнены`);
                return;
            }

            console.log(`Отправляемые параметры для inventory (строка ${index + 2}):`, params);
            const existingRecord = allInventoryRecords.find(item =>
                item.INVENTORY_CODE === record.inventory_code
            );

            return new Promise((resolve) => {
                const method = existingRecord ? 'custom.updateiplusinventory' : 'custom.addiplusinventory';
                const callParams = existingRecord ? { ...params, id: existingRecord.ID } : params;
                if (existingRecord && !existingRecord.ID) {
                    console.error('Отсутствует ID для обновления:', existingRecord);
                    errors.push(`Строка ${index + 2}: Отсутствует валидный ID для обновления`);
                    resolve();
                    return;
                }
                BX24.callMethod(method, callParams, function(result) {
                    if (result.error()) {
                        const errorMsg = result.error();
                        console.error(`Ошибка ${method}:`, errorMsg);
                        errors.push(`Строка ${index + 2}: Ошибка ${method}: ${errorMsg}`);
                    } else {
                        const data = result.data();
                        if (data && data.ID) {
                            console.log(`Успешно ${method === 'custom.addiplusinventory' ? 'добавлено' : 'обновлено'} с ID:`, data.ID);
                        } else if (data && data.success) {
                            console.log(`Успешно ${method === 'custom.addiplusinventory' ? 'добавлено' : 'обновлено'} без ID:`, data.message);
                        } else {
                            console.warn(`Успешный ответ для ${method}, но ID и success отсутствуют:`, data);
                            errors.push(`Строка ${index + 2}: Успешный ответ, но ID отсутствует для ${method}`);
                        }
                    }
                    resolve();
                }, { auth: access_token });
            });
        } catch (error) {
            console.error('Ошибка в processSingleRecord:', error);
            throw error;
        }
    }

    // Новая вспомогательная функция для получения или создания ID справочника
    async function getOrCreateReferenceId(referenceType, fieldName, value, access_token) {
        if (!value || value.trim() === '') {
            console.log(`Значение для ${referenceType} пустое или отсутствует, возвращаем 0`);
            return 0;
        }

        // Поиск существующего значения
        const existing = references[referenceType].find(item =>
            item[fieldName] && item[fieldName].toLowerCase() === value.toLowerCase()
        );
        if (existing) {
            console.log(`Найден существующий ${referenceType} с ID: ${existing.ID}`);
            return parseInt(existing.ID);
        }

        // Создание нового значения, если не найдено
        const methodMap = {
            types: 'custom.addiplusreferenceinventorytypes',
            companies: 'custom.addiplusreferencecompany',
            locations: 'custom.addiplusreferencelocation',
            statuses: 'custom.addiplusreferencestatus'
        };
        const params = { [fieldName]: value };
        return new Promise((resolve) => {
            BX24.callMethod(methodMap[referenceType], params, function(result) {
                if (result.error()) {
                    console.error(`Ошибка создания ${referenceType}:`, result.error());
                    resolve(0); // Игнорируем ошибку и возвращаем 0
                } else {
                    const data = result.data();
                    if (data && data.ID) {
                        console.log(`Создан новый ${referenceType} с ID:`, data.ID);
                        references[referenceType].push({
                            ID: data.ID,
                            [fieldName]: value
                        });
                        resolve(data.ID);
                    } else {
                        console.warn(`Успешный ответ для ${referenceType}, но ID отсутствует:`, data);
                        resolve(0);
                    }
                }
            }, { auth: access_token });
        });
    }

    async function findOrCreateReference(entity, field, value, access_token) {
        try {
            if (!value || value.trim() === '') {
                console.log(`Значение для ${entity} пустое или отсутствует, возвращаем 0`);
                return 0;
            }
            const params = { [field]: value };
            console.log(`Попытка создания ссылки ${entity} с параметрами:`, params);
            const result = await new Promise((resolve) => {
                BX24.callMethod(`custom.addiplusreference${entity}`, params, function(result) {
                    if (result.error()) {
                        const errorMsg = result.error();
                        console.error(`Ошибка добавления ссылки ${entity}:`, errorMsg);
                        resolve({ ID: 0 }); // Игнорируем ошибку и возвращаем 0
                    } else {
                        const data = result.data();
                        if (data && data.ID) {
                            console.log(`Ссылка ${entity} создана с ID:`, data.ID);
                            resolve(data);
                        } else {
                            console.warn(`Успешный ответ для ${entity}, но ID отсутствует:`, data);
                            resolve({ ID: 0 });
                        }
                    }
                }, { auth: access_token });
            });
            return result.ID || 0;
        } catch (error) {
            console.error(`Ошибка при создании ссылки ${entity}:`, error);
            return 0;
        }
    }

    function hideLoader() {
        const loaderOverlay = document.getElementById('loaderOverlay');
        if (loaderOverlay) {
            loaderOverlay.style.display = 'none';
        } else {
            console.warn('Элемент loaderOverlay не найден в DOM');
        }
    }

    // Ограничение параллельных запросов для избежания ошибок API
    async function limitConcurrency(tasks, maxConcurrency) {
        const results = [];
        const executing = new Set();
        for (const task of tasks) {
            const promise = Promise.resolve().then(() => task()).catch(error => {
                console.error('Ошибка в задаче:', error);
                return Promise.resolve(); // Продолжаем выполнение других задач
            });
            results.push(promise);
            executing.add(promise);
            promise.finally(() => executing.delete(promise));
            if (executing.size >= maxConcurrency) {
                await Promise.race(executing);
            }
        }
        return Promise.allSettled(results); // Используем allSettled для обработки всех задач
    }

    // Показать лоадер с прогрессом
    function showLoader(progress) {
        const loaderOverlay = document.getElementById('loaderOverlay');
        const loaderText = document.getElementById('loaderText');
        const loaderProgress = document.getElementById('loaderProgress');
        loaderText.textContent = `Обработка: ${Math.round(progress)}%`;
        loaderProgress.style.width = `${progress}%`;
        loaderOverlay.style.display = 'flex';
    }

    // Скрыть лоадер
    function hideLoader() {
        document.getElementById('loaderOverlay').style.display = 'none';
    }

    // Обновление текущего списка инвентаря
    async function updateInventoryRecords(access_token) {
        return new Promise((resolve, reject) => {
            BX24.callMethod('custom.getiplusinventory', {}, function(result) {
                if (result.error()) {
                    reject(new Error('Ошибка загрузки инвентаря: ' + result.error()));
                } else {
                    allInventoryRecords = result.data().result || [];
                    resolve();
                }
            }, { auth: access_token });
        });
    }

    // Чтение Excel-файла
    function readExcelFile(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                    const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });
                    resolve(jsonData);
                } catch (error) {
                    reject(error);
                }
            };
            reader.onerror = reject;
            reader.readAsArrayBuffer(file);
        });
    }


    // Поиск пользователя по email
    async function findUserByEmail(email, access_token) {
        if (!email) return null;
        const users = await loadBitrixUsers(access_token);
        const user = users.find(u => u.email && u.email.toLowerCase() === email.toLowerCase());
        return user ? user.ID : null;
    }

    // Поиск или создание справочника
    async function findOrCreateReference(referenceType, fieldName, value, access_token) {
        if (!value) return 0;
        const reference = references[referenceType].find(item =>
            item[fieldName] && item[fieldName].toLowerCase() === value.toLowerCase()
        );
        if (reference) {
            return parseInt(reference.ID);
        }
        const methodMap = {
            types: 'custom.addiplusreferenceinventorytypes',
            companies: 'custom.addiplusreferencecompany',
            locations: 'custom.addiplusreferencelocation',
            statuses: 'custom.addiplusreferencestatus'
        };
        const params = { [fieldName]: value };
        return new Promise((resolve) => {
            BX24.callMethod(methodMap[referenceType], params, function(result) {
                if (result.error()) {
                    console.error(`Ошибка создания ${referenceType}:`, result.error());
                    resolve(0);
                } else {
                    const newId = result.data().result?.id || result.data().id || 0;
                    if (newId) {
                        references[referenceType].push({
                            ID: newId,
                            [fieldName]: value
                        });
                    }
                    resolve(newId);
                }
            }, { auth: access_token });
        });
    }
</script>
<div class="loader-overlay" id="loaderOverlay">
    <div class="loader">
        <div class="loader-text" id="loaderText">Обработка: 0%</div>
        <div class="loader-bar">
            <div class="loader-progress" id="loaderProgress"></div>
        </div>
    </div>
</div>
<div class="popup-overlay" id="mappingPopupOverlay"></div>
<div class="popup" id="mappingPopup">
    <h2>Сопоставление полей</h2>
    <p>Выберите, какие столбцы из Excel соответствуют полям приложения.</p>
    <div id="mappingFields"></div>
    <div class="record-buttons">
        <button onclick="confirmMapping()">Подтвердить</button>
        <button onclick="cancelMapping()">Отменить</button>
    </div>
</div>
</body>
</html>