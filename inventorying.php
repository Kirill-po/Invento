<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Инвентаризации</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://api.bitrix24.com/api/v1/"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.min.js"></script>
    <style>
    /* Общие стили */
    body {
        font-size: 16px;
        line-height: 1.5;
    }

    .container {
        padding: 15px;
    }

    h1 {
        text-align: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    /* Стили для кнопок */
    .button {
        display: inline-block;
        padding: 8px 12px;
        font-size: 0.9rem;
        font-weight: normal;
        color: #fff;
        background-color: #e50045;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        width: 100%;
        box-sizing: border-box;
    }

    .button:hover {
        background-color: #d0003f;
    }

    /* Стили для модальных окон */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        z-index: 1000;
        overflow-y: auto;
    }

    .modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 15px;
        border: 1px solid #888;
        width: 90%;
        max-width: 600px;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .modal-footer {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 10px;
        margin-top: 15px;
    }

    .modal-footer .button {
        flex: 1;
        padding: 10px 0;
        font-size: 0.9rem;
        font-weight: bold;
    }

    /* Список инвентаризаций */
    .inventorying-item {
        cursor: pointer;
        padding: 8px;
        border-bottom: 1px solid #ccc;
        font-size: 0.9rem;
    }

    .inventorying-item:hover {
        background-color: #f5f5f5;
    }

    /* Хедер с кнопками */
    .header-buttons {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        gap: 10px;
        flex-wrap: nowrap; /* Запрещаем перенос элементов */
    }

    .header-buttons label {
        margin: 0;
        white-space: nowrap;
        font-size: 0.9rem;
    }
    .header-buttons .button {
        flex: 0 1 auto; /* Кнопки занимают только необходимое пространство */
        padding: 6px 10px;
        font-size: 0.8rem;
        max-width: 150px;
    }

    /* Список инвентаря в модальных окнах */
    #selected-inventory label,
    #edit-selected-inventory label {
        padding: 5px 0;
        font-size: 0.9rem;
        color: #333;
    }

    #selected-inventory input[type="checkbox"],
    #edit-selected-inventory input[type="checkbox"] {
        margin-right: 8px;
    }

    #selected-inventory .d-block:hover,
    #edit-selected-inventory .d-block:hover {
        background-color: #f9f9f9;
    }

    .inventory-item {
        padding: 2px 0;
        font-size: 0.9rem;
    }

    .confirmed {
        color: #00FF00;
        font-weight: bold;
    }

    .not-confirmed {
        color: #FF0000;
        font-weight: bold;
    }

    .inventory-record {
        background-color: #f8f8f8;
        padding: 6px;
        margin: 5px 0;
        border-radius: 4px;
        border: 1px solid #ddd;
        font-size: 0.9rem;
    }

    .inventory-record:not(:last-child) {
        border-bottom: 1px solid #ccc;
    }

    /* Попап выбора инвентаря */
    .inventory-selector-popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    .inventory-selector-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 15px;
        border: 1px solid #888;
        width: 90%;
        max-width: 800px;
        border-radius: 4px;
        max-height: 85vh; /* Уменьшаем max-height для лучшей адаптивности */
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
    }

    .inventory-selector-content h2 {
        margin: 0 0 10px;
        font-size: 1.2rem;
    }

    .field-group {
        margin-bottom: 8px;
    }

    .field-group label {
        display: block;
        margin-bottom: 4px;
        font-size: 0.9rem;
    }

    .field-group input[type="text"] {
        width: 100%;
        padding: 6px;
        border-radius: 4px;
        border: 1px solid #ccc;
        font-size: 0.9rem;
        box-sizing: border-box;
    }

    .inventory-list {
        flex: 1; /* Растягиваем список, чтобы он занимал доступное пространство */
        max-height: 40vh; /* Ограничиваем высоту списка */
        overflow-y: auto; /* Добавляем прокрутку только для списка */
        border: 1px solid #ddd;
        padding: 8px;
        border-radius: 4px;
        margin-bottom: 10px; /* Отступ перед кнопками */
    }

    .inventory-list label {
        display: flex;
        align-items: center;
        padding: 4px 0;
        font-size: 0.9rem;
    }

    .inventory-list input[type="checkbox"] {
        margin-right: 8px;
    }

    .select-all-container {
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Камера */
    .popup-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        z-index: 2000;
    }

    .popup {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        z-index: 2001;
        max-width: 90%;
        width: 350px;
        text-align: center;
        box-sizing: border-box;
    }

    #video {
        width: 100%;
        height: 200px;
        object-fit: cover;
        margin-bottom: 10px;
        border-radius: 4px;
    }

    .camera-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .camera-buttons button {
        width: 100%;
        background-color: rgb(220, 0, 67);
        color: white;
        border: none;
        padding: 8px 0;
        font-size: 0.9rem;
        cursor: pointer;
        border-radius: 5px;
    }

    /* Анимация галочки и крестика */
    .checkmark,
    .cross {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 80px;
        height: 80px;
        background-size: contain;
        z-index: 2002;
        animation: fadeInOut 1s ease-in-out;
    }

    .checkmark {
        background: url('checkmark.png') no-repeat center;
    }

    .cross {
        background: url('cross.png') no-repeat center;
    }

    /* Уведомление */
    .notification {
        display: none;
        position: fixed;
        top: 60%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        z-index: 2002;
        max-width: 90%;
        text-align: center;
        font-size: 0.9rem;
    }

    @keyframes fadeInOut {
        0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
        20% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
        80% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
        100% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
    }

    /* Кнопки редактирования */
    .action-buttons {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        margin-left: 8px;
    }

    .action-button {
        cursor: pointer;
        font-size: 1rem;
        margin: 0 4px;
    }

    .check-icon::before {
        content: '✔';
        color: green;
    }

    .cross-icon::before {
        content: '✖';
        color: red;
    }

    .uncheck-icon::before {
        content: '✔';
        color: red;
    }

    /* Радиобаттоны */
    .filter-radio {
        display: flex;
        justify-content: space-around;
        margin: 8px 0;
    }

    .filter-radio label {
        margin: 0;
        font-size: 0.9rem;
    }

    .filter-radio input[type="radio"] {
        margin-right: 4px;
    }

    /* Попап управления пользователями */
    .field-group {
        margin-bottom: 8px;
    }

    .field-group label {
        display: block;
        margin-bottom: 4px;
        font-size: 0.9rem;
    }

    .field-group input[type="text"] {
        width: 100%;
        padding: 6px;
        border-radius: 4px;
        border: 1px solid #ccc;
        font-size: 0.9rem;
        box-sizing: border-box;
    }

    .record-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: auto; /* Кнопки прижимаются к низу */
    }

    .record-buttons button {
        flex: 1;
        padding: 8px;
        font-size: 0.9rem;
        border: none;
        border-radius: 5px;
        background-color: #e50045;
        color: #fff;
        cursor: pointer;
        text-align: center;
        box-sizing: border-box;
    }

    .record-buttons button:hover {
        background-color: #d0003f;
    }

    /* Медиа-запросы для мобильных устройств */
    @media (max-width: 576px) {
        body {
            font-size: 14px;
        }

        h1 {
            font-size: 1.2rem;
        }

        .button {
            padding: 6px 10px;
            font-size: 0.8rem;
        }

        .modal-content {
            margin: 10% auto;
            padding: 10px;
            width: 95%;
        }

        .modal-footer .button {
            padding: 8px 0;
            font-size: 0.8rem;
        }

        .inventorying-item {
            padding: 6px;
            font-size: 0.8rem;
        }

        .header-buttons {
            gap: 8px;
        }

        .header-buttons label {
            font-size: 0.8rem;
        }

        .header-buttons .button {
            padding: 5px 8px;
            font-size: 0.75rem;
        }

        .inventory-item {
            font-size: 0.8rem;
        }

        .inventory-record {
            padding: 4px;
            font-size: 0.8rem;
        }

        .inventory-selector-content {
            margin: 10% auto;
            padding: 10px;
            width: 95%;
        }

        .filter-section input[type="text"] {
            padding: 5px;
            font-size: 0.8rem;
        }

        .inventory-list label {
            font-size: 0.8rem;
        }

        .popup {
            padding: 8px;
            width: 95%;
            max-width: 320px;
        }

        #video {
            height: 180px;
        }

        .camera-buttons button {
            padding: 6px 0;
            font-size: 0.8rem;
        }

        .notification {
            padding: 8px;
            font-size: 0.8rem;
        }

        .action-button {
            font-size: 0.9rem;
            margin: 0 3px;
        }

        .filter-radio label {
            font-size: 0.8rem;
        }
    }
    h3{
        text-align: center;
    }
    /* Стили для сообщения об ошибке */
    .access-denied {
        color: red;
        font-size: clamp(24px, 5vw, 32px);
        font-weight: bold;
        text-align: center;
        margin-top: 50px;
        display: none; /* Скрываем по умолчанию */
    }

    /* Лоадер */
    .loader {
        font-size: clamp(16px, 2vw, 20px);
        color: #333;
        text-align: center;
        margin-top: 50px;
    }
</style>
</head>
<body>
    <!-- Лоадер на время проверки прав -->
    <div class="loader" id="loader">Загрузка...</div>

    <!-- Сообщение для пользователей с правами view -->
    <div class="access-denied" id="accessDenied">Доступ запрещён</div>

    <div class="container" id="mainContainer" style="display: none;">
        <h1 class="my-3">Инвентаризации</h1>
        <div class="header-buttons">
            <a href="pril.php" class="button">Назад</a>
            <label><input type="checkbox" id="show-completed" onchange="loadInventoryingList()"> Показать завершённые</label>
            <button class="button" onclick="openCreateModal()">Создать</button>
        </div>
        <div id="inventorying-list" class="mt-3" style="display: none;"></div>
    </div>

    <!-- Модальное окно создания -->
    <div id="create-modal" class="modal">
        <div class="modal-content">
            <h2>Инвентаризация: создание</h2>
            <div class="mt-3">
                <h3>Сотрудники</h3>
                <div id="users-list"></div>
            </div>
            <div class="mt-3">
                <h3 class="sered">Инвентарь</h3>
                <button class="button" onclick="openInventorySelector()">Выбрать инвентарь</button>
                <div id="selected-inventory" class="mt-2"></div>
            </div>
            <div class="modal-footer">
                <button class="button" onclick="closeModal('create-modal')">Отмена</button>
                <button class="button" onclick="saveInventorying()">Сохранить</button>
            </div>
        </div>
    </div>

    <!-- Попап для выбора инвентаря -->
    <div class="popup-overlay" id="inventory-selector-popup"></div>
    <div class="popup" id="inventory-selector-content">
        <h2>Выбор инвентаря</h2>
        <div class="filters-container">
            <div class="field-group">
                <label for="filterSerial">Серийный номер</label>
                <input type="text" id="filterSerial" placeholder="Введите серийный номер" onkeyup="filterInventoryItems()">
            </div>
            <div class="field-group">
                <label for="filterModel">Модель</label>
                <input type="text" id="filterModel" placeholder="Введите модель" onkeyup="filterInventoryItems()">
            </div>
            <div class="field-group">
                <label for="filterInventoryCode">Инвентарный номер</label>
                <input type="text" id="filterInventoryCode" placeholder="Введите инвентарный номер" onkeyup="filterInventoryItems()">
            </div>
            <div class="field-group">
                <label for="filterComment">Комментарий</label>
                <input type="text" id="filterComment" placeholder="Введите комментарий" onkeyup="filterInventoryItems()">
            </div>
            <div class="field-group">
                <label for="select-all-inventory">
                    <input type="checkbox" id="select-all-inventory" onchange="toggleSelectAllInventory()">
                    Выбрать все
                </label>
            </div>
        </div>
        <div id="inventory-list" class="inventory-list"></div>
        <div class="record-buttons">
            <button class="button" onclick="closeInventorySelector()">Отмена</button>
            <button class="button" onclick="confirmInventorySelection()">Подтвердить</button>
        </div>
    </div>

    <!-- Модальное окно просмотра -->
    <div id="detail-modal" class="modal">
        <div class="modal-content">
            <!-- Содержимое будет добавлено через JavaScript -->
        </div>
    </div>

    <!-- Модальное окно редактирования -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <h2>Инвентаризация: редактирование</h2>
            <div class="mt-3">
                <h3>Сотрудники</h3>
                <button class="button" onclick="openUsersPopup()">Пользователи</button>
            </div>
            <div class="mt-3">
                <h3>Инвентарь</h3>
                <button class="button" onclick="openEditInventorySelector()">Добавить инвентарь</button>
                <div class="filter-radio">
                    <label><input type="radio" name="inventory-filter" value="found" onchange="filterInventoryList()"> Найдены</label>
                    <label><input type="radio" name="inventory-filter" value="all" onchange="filterInventoryList()" checked> Все</label>
                    <label><input type="radio" name="inventory-filter" value="not-found" onchange="filterInventoryList()"> Не найдены</label>
                </div>
                <div id="edit-selected-inventory" class="mt-2"></div>
            </div>
            <div class="modal-footer">
                <button class="button" onclick="closeModal('edit-modal')">Закрыть</button>
                <button class="button" onclick="completeInventorying()">Завершить</button>
            </div>
        </div>
    </div>

    <!-- Попап для управления пользователями -->
    <div id="users-popup" class="modal">
        <div class="modal-content">
            <h2>Управление пользователями</h2>
            <div id="users-popup-list" class="mt-3"></div>
            <div class="modal-footer">
                <button class="button" onclick="closeUsersPopup()">Закрыть</button>
            </div>
        </div>
    </div>

    <!-- Элементы для камеры и уведомлений -->
    <div id="cameraPopupOverlay" class="popup-overlay"></div>
    <div id="cameraPopup" class="popup">
        <h2>Сканирование QR-кода</h2>
        <video id="video" autoplay></video>
        <div class="camera-buttons">
            <button onclick="closeCameraPopup()">Закрыть</button>
        </div>
    </div>
    <div id="checkmark" class="checkmark"></div>
    <div id="cross" class="cross"></div>
    <div id="notification" class="notification"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentInventoryingId = null;
        let currentStream = null;
        let scanningQR = false;
        let currentInventoryingData = null;
        let selectedInventoryItems = [];
        let lastScannedQR = null; // Для отслеживания последнего отсканированного QR-кода
        let isProcessingQR = false; // Флаг для предотвращения параллельной обработки

        // Загрузка списка инвентаризаций
        function loadInventoryingList() {
            const showCompleted = document.getElementById('show-completed').checked;
            BX24.callMethod('custom.getinventorying', { show_completed: showCompleted }, response => {
                if (response.error()) {
                    console.error('Ошибка:', response.error());
                    return;
                }
                const list = document.getElementById('inventorying-list');
                list.innerHTML = '';
                response.data().result.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'inventorying-item';

                    let formattedDate = 'Не указана';
                    if (item.DATE_OF_CREATION) {
                        const creationDate = new Date(item.DATE_OF_CREATION);
                        if (!isNaN(creationDate.getTime())) {
                            formattedDate = creationDate.toLocaleString('ru-RU', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                        }
                    }

                    div.innerHTML = `Инвентаризация от ${formattedDate} | Инвентарь: ${item.TOTAL_INVENTORY} | Проверено: ${item.CONFIRMED_INVENTORY} (Отсканировано: ${item.SCANNED_INVENTORY}, Вручную: ${item.MANUALLY_CONFIRMED_INVENTORY})`;
                    div.onclick = () => openDetailModal(item.ID);
                    list.appendChild(div);
                });
            });
        }

        // Открытие модального окна создания
        function openCreateModal() {
            document.getElementById('create-modal').style.display = 'block';
            BX24.callMethod('user.get', {}, response => {
                const usersList = document.getElementById('users-list');
                usersList.innerHTML = '';
                response.data().forEach(user => {
                    usersList.innerHTML += `<label class="d-block"><input type="checkbox" value="${user.ID}"> ${user.LAST_NAME} ${user.NAME}</label>`;
                });
            });
        }

        // Открытие попапа выбора инвентаря
        function openInventorySelector() {
            const popup = document.getElementById('inventory-selector-popup');
            const content = document.getElementById('inventory-selector-content');
            const inventoryList = document.getElementById('inventory-list');
            inventoryList.innerHTML = ''; // Очищаем список, включая сообщение "Совпадений не найдено"
            selectedInventoryItems = Array.from(document.querySelectorAll('#selected-inventory input:checked'))
                .map(input => input.value);

            BX24.callMethod('custom.getiplusinventory', {}, response => {
                if (response.error()) {
                    console.error('Ошибка при загрузке инвентаря:', response.error());
                    inventoryList.innerHTML = '<p>Ошибка загрузки инвентаря. Проверьте консоль для деталей.</p>';
                    return;
                }

                const inventoryItems = response.data().result;
                if (!Array.isArray(inventoryItems)) {
                    console.error('Данные инвентаря отсутствуют или имеют неверный формат:', response.data());
                    inventoryList.innerHTML = '<p>Нет инвентаря для выбора.</p>';
                    return;
                }

                inventoryItems.forEach(item => {
                    const isChecked = selectedInventoryItems.includes(item.ID.toString()) ? 'checked' : '';
                    inventoryList.innerHTML += `
                        <label class="d-block inventory-item" 
                            data-model="${item.MODEL.toLowerCase()}" 
                            data-serial="${item.SERIAL_CODE.toLowerCase()}" 
                            data-inventory-code="${(item.INVENTORY_CODE || '').toLowerCase()}" 
                            data-comment="${(item.COMMENT || '').toLowerCase()}">
                            <input type="checkbox" value="${item.ID}" ${isChecked} onchange="updateInventorySelection(this)">
                            ${item.MODEL} (${item.SERIAL_CODE}) 
                            - Инв. номер: ${item.INVENTORY_CODE || 'Не указан'} 
                            ${item.COMMENT ? `| Комментарий: ${item.COMMENT}` : ''}
                        </label>`;
                });

                if (inventoryItems.length === 0) {
                    inventoryList.innerHTML = '<p>Нет инвентаря для выбора.</p>';
                }

                popup.style.display = 'block';
                content.style.display = 'block';
                
                // Очистка полей фильтра
                document.getElementById('filterSerial').value = '';
                document.getElementById('filterModel').value = '';
                document.getElementById('filterInventoryCode').value = '';
                document.getElementById('filterComment').value = '';

                // Применяем фильтрацию после загрузки данных
                filterInventoryItems();
                updateSelectAllCheckbox();
            });
        }

        // Обновление выбранного инвентаря
        function updateInventorySelection(checkbox) {
            const inventoryId = checkbox.value;
            if (checkbox.checked) {
                if (!selectedInventoryItems.includes(inventoryId)) {
                    selectedInventoryItems.push(inventoryId);
                }
            } else {
                selectedInventoryItems = selectedInventoryItems.filter(id => id !== inventoryId);
            }
            updateSelectAllCheckbox();
        }

        // Функция массового выбора
        function toggleSelectAllInventory() {
            const selectAllCheckbox = document.getElementById('select-all-inventory');
            const inventoryCheckboxes = document.querySelectorAll('#inventory-list input[type="checkbox"]');
            const visibleCheckboxes = Array.from(inventoryCheckboxes).filter(checkbox => 
                checkbox.closest('.inventory-item').style.display !== 'none'
            );

            if (selectAllCheckbox.checked) {
                visibleCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                    const inventoryId = checkbox.value;
                    if (!selectedInventoryItems.includes(inventoryId)) {
                        selectedInventoryItems.push(inventoryId);
                    }
                });
            } else {
                visibleCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    selectedInventoryItems = selectedInventoryItems.filter(id => id !== checkbox.value);
                });
            }
        }

        // Обновление состояния чекбокса "Выбрать все"
        function updateSelectAllCheckbox() {
            const selectAllCheckbox = document.getElementById('select-all-inventory');
            const visibleItems = Array.from(document.querySelectorAll('#inventory-list .inventory-item'))
                .filter(item => item.style.display !== 'none');
            const checkedItems = visibleItems.filter(item => item.querySelector('input[type="checkbox"]').checked);

            if (visibleItems.length > 0 && visibleItems.length === checkedItems.length) {
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.checked = false;
            }
        }

        // Фильтрация инвентаря
        function filterInventoryItems() {
            const filterAll = document.getElementById('filterAll').value.toLowerCase().trim();
            const inventoryItems = document.querySelectorAll('#inventory-list .inventory-item');

            inventoryItems.forEach(item => {
                const serial = item.dataset.serial || '';
                const model = item.dataset.model || '';
                const inventoryCode = item.dataset.inventoryCode || '';
                const comment = item.dataset.comment || '';

                const matchesAll = !filterAll || 
                    serial.includes(filterAll) || 
                    model.includes(filterAll) || 
                    inventoryCode.includes(filterAll) || 
                    comment.includes(filterAll);

                if (matchesAll) {
                    item.style.setProperty('display', 'block', 'important');
                } else {
                    item.style.setProperty('display', 'none', 'important');
                }
            });

            updateSelectAllCheckbox();
        }

        // Подтверждение выбора инвентаря
        function confirmInventorySelection() {
            const selected = document.getElementById('selected-inventory');
            selected.innerHTML = '';

            BX24.callMethod('custom.getiplusinventory', {}, response => {
                if (response.error()) {
                    console.error('Ошибка при загрузке инвентаря:', response.error());
                    return;
                }

                const inventoryItems = response.data().result;
                const selectedItems = inventoryItems.filter(item => selectedInventoryItems.includes(item.ID.toString()));

                selectedItems.forEach(item => {
                    selected.innerHTML += `
                        <label class="d-block">
                            <input type="checkbox" value="${item.ID}" checked>
                            ${item.MODEL} (${item.SERIAL_CODE}) 
                            - Инв. номер: ${item.INVENTORY_CODE || 'Не указан'} 
                            ${item.COMMENT ? `| Комментарий: ${item.COMMENT}` : ''}
                        </label>`;
                });

                if (selectedItems.length === 0) {
                    selected.innerHTML = '<p>Инвентарь не выбран.</p>';
                }

                closeInventorySelector();
            });
        }

        // Закрытие попапа выбора инвентаря
        function closeInventorySelector() {
            document.getElementById('inventory-selector-popup').style.display = 'none';
            document.getElementById('inventory-selector-content').style.display = 'none';
            document.getElementById('filterSerial').value = '';
            document.getElementById('filterModel').value = '';
            document.getElementById('filterInventoryCode').value = '';
            document.getElementById('filterComment').value = '';
            filterInventoryItems();
        }

        // Сохранение новой инвентаризации
        function saveInventorying() {
            const users = Array.from(document.querySelectorAll('#users-list input:checked')).map(input => input.value);
            const inventory = Array.from(document.querySelectorAll('#selected-inventory input:checked')).map(input => input.value);

            BX24.callMethod('user.current', {}, userResponse => {
                if (userResponse.error()) {
                    console.error('Ошибка получения текущего пользователя:', userResponse.error());
                    alert('Ошибка: Не удалось определить текущего пользователя');
                    return;
                }

                const initiator = userResponse.data().ID;
                BX24.callMethod('custom.addinventorying', {
                    initiator: initiator,
                    users: JSON.stringify(users),
                    inventory: JSON.stringify(inventory)
                }, response => {
                    if (response.error()) {
                        console.error('Ошибка сервера:', response.error());
                        alert('Ошибка на сервере: ' + (response.error().description || 'Неизвестная ошибка'));
                        return;
                    }

                    const data = response.data();
                    if (data.success) {
                        closeModal('create-modal');
                        loadInventoryingList();
                    } else {
                        console.error('Ошибка от метода:', data.error);
                        alert('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
                    }
                });
            });
        }

        // Открытие детального просмотра
        function openDetailModal(inventoryingId) {
            currentInventoryingId = inventoryingId;
            console.log('Открытие деталей инвентаризации с ID:', currentInventoryingId);
            BX24.callMethod('custom.getinventoryingdetails', { id: inventoryingId }, response => {
                if (response.error()) {
                    console.error('Ошибка загрузки деталей:', response.error());
                    alert('Ошибка загрузки деталей инвентаризации: ' + response.error().description);
                    return;
                }

                const data = response.data().result;
                console.log('Данные инвентаризации:', data);
                const modal = document.getElementById('detail-modal');
                const modalContent = modal.querySelector('.modal-content');
                let formattedDate = 'Не указана';
                if (data.DATE_OF_CREATION) {
                    const creationDate = new Date(data.DATE_OF_CREATION);
                    if (!isNaN(creationDate.getTime())) {
                        formattedDate = creationDate.toLocaleString('ru-RU', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }
                }

                let inventoryListHtml = '<strong>Список инвентаря:</strong><br>';
                if (data.INVENTORY_ITEMS && data.INVENTORY_ITEMS.length > 0) {
                    data.INVENTORY_ITEMS.forEach(item => {
                        const isConfirmed = item.SCANNED === '1' || item.MANUALLY_CONFIRMED === '1';
                        const colorClass = isConfirmed ? 'confirmed' : 'not-confirmed';
                        inventoryListHtml += `
                            <div class="inventory-record" data-id="${item.ID}">
                                <span class="inventory-item ${colorClass}">
                                    ${item.MODEL} (${item.SERIAL_CODE}) - Инв. номер: ${item.INVENTORY_CODE || 'Не указан'} ${item.COMMENT ? `| Комментарий: ${item.COMMENT}` : ''}
                                </span>
                            </div>`;
                    });
                } else {
                    inventoryListHtml += 'Инвентарь отсутствует.<br>';
                }

                modalContent.innerHTML = `
                    <h2>Детали инвентаризации</h2>
                    <p><strong>Дата создания:</strong> ${formattedDate}</p>
                    <p><strong>Статус:</strong> ${data.IS_DONE === '1' ? 'Завершена' : 'В процессе'}</p>
                    <p><strong>Инвентарь:</strong> ${data.TOTAL_INVENTORY}</p>
                    <p><strong>Проверено:</strong> ${data.CONFIRMED_INVENTORY} (Отсканировано: ${data.SCANNED_INVENTORY}, Вручную: ${data.MANUALLY_CONFIRMED_INVENTORY})</p>
                    ${inventoryListHtml}
                    <div class="modal-footer">
                        <button class="button" onclick="closeModal('detail-modal')">Закрыть</button>
                        <button class="button" onclick="updateInventorying(${inventoryingId})">Изменить</button>
                        <button class="button" id="scanButton">Сканировать</button>
                    </div>
                `;

                modal.style.display = 'block';
                document.getElementById('scanButton').addEventListener('click', () => scanInventory(inventoryingId));
            });
        }

        // Открытие модального окна редактирования
        function updateInventorying(inventoryingId) {
            currentInventoryingId = inventoryingId;
            console.log('Открытие редактирования инвентаризации с ID:', currentInventoryingId);

            // Загружаем данные инвентаризации
            BX24.callMethod('custom.getinventoryingdetails', { id: inventoryingId }, response => {
                if (response.error()) {
                    console.error('Ошибка загрузки деталей:', response.error());
                    alert('Ошибка загрузки деталей инвентаризации: ' + response.error().description);
                    return;
                }

                currentInventoryingData = response.data().result;
                console.log('Данные для редактирования:', currentInventoryingData);

                // Проверяем наличие поля USERS, если его нет — инициализируем как пустой массив
                if (!currentInventoryingData.USERS || !Array.isArray(currentInventoryingData.USERS)) {
                    console.warn('Поле USERS отсутствует или не является массивом, инициализируем как пустой массив');
                    currentInventoryingData.USERS = [];
                } else {
                    // Убедимся, что все элементы в USERS — строки
                    currentInventoryingData.USERS = currentInventoryingData.USERS.map(userId => String(userId));
                }

                // Открываем модальное окно редактирования
                const modal = document.getElementById('edit-modal');
                modal.style.display = 'block';

                // Отображаем текущий список инвентаря
                renderEditInventoryList();
            });
        }

        // Отображение списка инвентаря в модальном окне редактирования
        function renderEditInventoryList() {
            const selected = document.getElementById('edit-selected-inventory');
            selected.innerHTML = '';

            if (currentInventoryingData.INVENTORY_ITEMS && currentInventoryingData.INVENTORY_ITEMS.length > 0) {
                currentInventoryingData.INVENTORY_ITEMS.forEach(item => {
                    const isConfirmed = item.SCANNED === '1' || item.MANUALLY_CONFIRMED === '1';
                    const colorClass = isConfirmed ? 'confirmed' : 'not-confirmed';

                    // Определяем, какие кнопки отображать
                    let actionButtons = '';
                    if (isConfirmed) {
                        // Для отсканированного инвентаря показываем красную галочку для снятия пометки
                        actionButtons = `
                            <span class="action-button uncheck-icon" onclick="unmarkInventory(${item.ID})"></span>
                        `;
                    } else {
                        // Для неотсканированного инвентаря показываем галочку и крестик
                        actionButtons = `
                            <span class="action-button check-icon" onclick="markInventoryManually(${item.ID})"></span>
                            <span class="action-button cross-icon" onclick="removeInventory(${item.ID})"></span>
                        `;
                    }

                    selected.innerHTML += `
                        <div class="inventory-record" data-id="${item.ID}">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="inventory-item ${colorClass}">
                                    ${item.MODEL} (${item.SERIAL_CODE}) - Инв. номер: ${item.INVENTORY_CODE || 'Не указан'} ${item.COMMENT ? `| Комментарий: ${item.COMMENT}` : ''}
                                </span>
                                <div class="action-buttons">
                                    ${actionButtons}
                                </div>
                            </div>
                        </div>`;
                });
            } else {
                selected.innerHTML = '<p>Инвентарь отсутствует.</p>';
            }

            // Применяем фильтр после рендеринга
            filterInventoryList();
        }

        // Фильтрация списка инвентаря
        function filterInventoryList() {
            const filterValue = document.querySelector('input[name="inventory-filter"]:checked').value;
            const selected = document.getElementById('edit-selected-inventory');
            selected.innerHTML = '';

            let filteredItems = currentInventoryingData.INVENTORY_ITEMS;
            if (filterValue === 'found') {
                filteredItems = currentInventoryingData.INVENTORY_ITEMS.filter(item => item.SCANNED === '1' || item.MANUALLY_CONFIRMED === '1');
            } else if (filterValue === 'not-found') {
                filteredItems = currentInventoryingData.INVENTORY_ITEMS.filter(item => item.SCANNED !== '1' && item.MANUALLY_CONFIRMED !== '1');
            }

            if (filteredItems.length > 0) {
                filteredItems.forEach(item => {
                    const isConfirmed = item.SCANNED === '1' || item.MANUALLY_CONFIRMED === '1';
                    const colorClass = isConfirmed ? 'confirmed' : 'not-confirmed';

                    let actionButtons = '';
                    if (isConfirmed) {
                        actionButtons = `
                            <span class="action-button uncheck-icon" onclick="unmarkInventory(${item.ID})"></span>
                        `;
                    } else {
                        actionButtons = `
                            <span class="action-button check-icon" onclick="markInventoryManually(${item.ID})"></span>
                            <span class="action-button cross-icon" onclick="removeInventory(${item.ID})"></span>
                        `;
                    }

                    selected.innerHTML += `
                        <div class="inventory-record" data-id="${item.ID}">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="inventory-item ${colorClass}">
                                    ${item.MODEL} (${item.SERIAL_CODE}) - Инв. номер: ${item.INVENTORY_CODE || 'Не указан'} ${item.COMMENT ? `| Комментарий: ${item.COMMENT}` : ''}
                                </span>
                                <div class="action-buttons">
                                    ${actionButtons}
                                </div>
                            </div>
                        </div>`;
                });
            } else {
                selected.innerHTML = '<p>Инвентарь отсутствует.</p>';
            }
        }

        // Добавление нового инвентаря в инвентаризацию
        function openEditInventorySelector() {
            const selected = document.getElementById('edit-selected-inventory');
            
            // Проверяем, существует ли уже temp-inventory-selector
            const existingTempDiv = document.getElementById('temp-inventory-selector');
            if (existingTempDiv) {
                console.log('Список добавления инвентаря уже открыт, пропускаем повторное открытие');
                return;
            }

            const tempDiv = document.createElement('div');
            tempDiv.id = 'temp-inventory-selector';

            BX24.callMethod('custom.getiplusinventory', {}, response => {
                if (response.error()) {
                    console.error('Ошибка при загрузке инвентаря:', response.error());
                    tempDiv.innerHTML = '<p>Ошибка загрузки инвентаря. Проверьте консоль для деталей.</p>';
                    selected.appendChild(tempDiv);
                    return;
                }

                const inventoryItems = response.data().result;
                if (!Array.isArray(inventoryItems)) {
                    console.error('Данные инвентаря отсутствуют или имеют неверный формат:', response.data());
                    tempDiv.innerHTML = '<p>Нет инвентаря для выбора.</p>';
                    selected.appendChild(tempDiv);
                    return;
                }

                inventoryItems.forEach(item => {
                    // Проверяем, есть ли инвентарь уже в списке
                    const isAlreadyAdded = currentInventoryingData.INVENTORY_ITEMS.some(i => i.ID == item.ID);
                    if (!isAlreadyAdded) {
                        tempDiv.innerHTML += `
                            <label class="d-block">
                                <input type="checkbox" value="${item.ID}">
                                ${item.MODEL} (${item.SERIAL_CODE}) 
                                - Инв. номер: ${item.INVENTORY_CODE || 'Не указан'} 
                                ${item.COMMENT ? `| Комментарий: ${item.COMMENT}` : ''}
                            </label>`;
                    }
                });

                if (tempDiv.innerHTML === '') {
                    tempDiv.innerHTML = '<p>Нет нового инвентаря для добавления.</p>';
                } else {
                    tempDiv.innerHTML += `
                        <div class="mt-2">
                            <button class="button" onclick="addSelectedInventory()">Добавить</button>
                            <button class="button" onclick="cancelAddInventory()">Закрыть</button>
                        </div>`;
                }

                selected.appendChild(tempDiv);
            });
        }

        // Добавление выбранного инвентаря
        function addSelectedInventory() {
            const selectedItems = Array.from(document.querySelectorAll('#temp-inventory-selector input:checked')).map(input => {
                const label = input.parentElement;
                return {
                    ID: input.value,
                    MODEL: label.textContent.split('(')[0].trim(),
                    SERIAL_CODE: label.textContent.match(/\((.*?)\)/)[1],
                    INVENTORY_CODE: label.textContent.includes('Инв. номер:') ? label.textContent.split('Инв. номер:')[1].split('|')[0].trim() : '',
                    COMMENT: label.textContent.includes('Комментарий:') ? label.textContent.split('Комментарий:')[1].trim() : '',
                    SCANNED: '0',
                    MANUALLY_CONFIRMED: '0'
                };
            });

            selectedItems.forEach(item => {
                currentInventoryingData.INVENTORY_ITEMS.push(item);
            });

            document.getElementById('temp-inventory-selector').remove();
            renderEditInventoryList();

            // Сохраняем изменения на сервере
            saveInventoryingChanges();
            showNotification('green', 'Инвентарь успешно добавлен');

            // Проверяем, можно ли завершить инвентаризацию
            checkAndCompleteInventorying();
        }

        // Отмена добавления инвентаря
        function cancelAddInventory() {
            document.getElementById('temp-inventory-selector').remove();
        }

        // Обновление данных инвентаризации с сервера
        function refreshInventoryingData(callback) {
            BX24.callMethod('custom.getinventoryingdetails', { id: currentInventoryingId }, response => {
                if (response.error()) {
                    console.error('Ошибка загрузки деталей:', response.error());
                    showNotification('red', 'Ошибка загрузки данных инвентаризации: ' + response.error().description);
                    return;
                }

                currentInventoryingData = response.data().result;
                console.log('Данные инвентаризации обновлены:', currentInventoryingData);
                if (callback) callback();
            });
        }

        // Ручная пометка инвентаря как отсканированного
        function markInventoryManually(inventoryId) {
            const inventoryingIdStr = String(currentInventoryingId);
            const inventoryIdStr = String(inventoryId);

            BX24.callMethod('custom.markinventorymanually', {
                inventorying_id: inventoryingIdStr,
                inventory_id: inventoryIdStr
            }, response => {
                if (response.error()) {
                    console.error('Ошибка при ручной пометке инвентаря:', response.error());
                    showNotification('red', 'Ошибка при ручной пометке инвентаря: ' + (response.error().description || 'Неизвестная ошибка'));
                    return;
                }

                const data = response.data();
                if (data.result.success) {
                    // Обновляем данные с сервера
                    refreshInventoryingData(() => {
                        renderEditInventoryList();
                        if (data.result.already_marked) {
                            showNotification('green', 'Инвентарь уже помечен вручную');
                        } else {
                            showNotification('green', 'Инвентарь успешно помечен вручную');
                        }
                        // Проверяем, можно ли завершить инвентаризацию
                        checkAndCompleteInventorying();
                    });
                } else {
                    showNotification('red', 'Не удалось пометить инвентарь вручную');
                }
            });
        }

        // Снятие пометки сканирования
        function unmarkInventory(inventoryId) {
            const inventoryingIdStr = String(currentInventoryingId);
            const inventoryIdStr = String(inventoryId);

            BX24.callMethod('custom.unmarkinventoryscanned', {
                inventorying_id: inventoryingIdStr,
                inventory_id: inventoryIdStr
            }, response => {
                if (response.error()) {
                    console.error('Ошибка при снятии пометки инвентаря:', response.error());
                    showNotification('red', 'Ошибка при снятии пометки: ' + (response.error().description || 'Неизвестная ошибка'));
                    return;
                }

                const data = response.data();
                if (data.result.success) {
                    // Обновляем данные с сервера
                    refreshInventoryingData(() => {
                        renderEditInventoryList();
                        if (data.result.already_unmarked) {
                            showNotification('green', 'Инвентарь уже не помечен');
                        } else {
                            showNotification('green', 'Пометка сканирования успешно снята');
                        }
                        // Проверяем, можно ли завершить инвентаризацию
                        checkAndCompleteInventorying();
                    });
                } else {
                    showNotification('red', 'Не удалось снять пометку сканирования');
                }
            });
        }

        // Удаление инвентаря из инвентаризации
        function removeInventory(inventoryId) {
            if (!confirm('Вы уверены, что хотите удалить этот инвентарь из инвентаризации?')) {
                return;
            }

            const inventoryingIdStr = String(currentInventoryingId);
            const inventoryIdStr = String(inventoryId);

            BX24.callMethod('custom.removeinventoryfrominventorying', {
                inventorying_id: inventoryingIdStr,
                inventory_id: inventoryIdStr
            }, response => {
                if (response.error()) {
                    console.error('Ошибка при удалении инвентаря:', response.error());
                    showNotification('red', 'Ошибка при удалении инвентаря: ' + (response.error().description || 'Неизвестная ошибка'));
                    return;
                }

                const data = response.data();
                if (data.result.success) {
                    // Обновляем данные с сервера
                    refreshInventoryingData(() => {
                        renderEditInventoryList();
                        showNotification('green', 'Инвентарь успешно удален из инвентаризации');
                        // Проверяем, можно ли завершить инвентаризацию
                        checkAndCompleteInventorying();
                    });
                } else {
                    showNotification('red', 'Не удалось удалить инвентарь');
                }
            });
        }

        // Сохранение изменений инвентаризации на сервере
        function saveInventoryingChanges() {
            const users = currentInventoryingData.USERS;
            const inventory = currentInventoryingData.INVENTORY_ITEMS.map(item => item.ID);

            // Получаем текущего пользователя (initiator)
            BX24.callMethod('user.current', {}, userResponse => {
                if (userResponse.error()) {
                    console.error('Ошибка получения текущего пользователя:', userResponse.error());
                    showNotification('red', 'Ошибка: Не удалось определить текущего пользователя');
                    return;
                }

                const initiator = userResponse.data().ID;
                BX24.callMethod('custom.addinventorying', {
                    id: currentInventoryingId, // Передаем ID для обновления
                    initiator: initiator,
                    users: JSON.stringify(users),
                    inventory: JSON.stringify(inventory)
                }, response => {
                    if (response.error()) {
                        console.error('Ошибка при сохранении изменений:', response.error());
                        showNotification('red', 'Ошибка при сохранении изменений: ' + (response.error().description || 'Неизвестная ошибка'));
                        return;
                    }

                    const data = response.data();
                    if (data.success) {
                        console.log('Изменения успешно сохранены на сервере');
                        // Обновляем список инвентаризаций
                        loadInventoryingList();
                        // Обновляем модальное окно деталей (если оно открыто)
                        if (document.getElementById('detail-modal').style.display === 'block') {
                            openDetailModal(currentInventoryingId);
                        }
                    } else {
                        showNotification('red', 'Не удалось сохранить изменения');
                    }
                });
            });
        }

        // Завершение инвентаризации (по кнопке "Завершить")
        function completeInventorying() {
            if (!currentInventoryingId) {
                showNotification('red', 'Ошибка: ID инвентаризации не установлен');
                return;
            }

            // Проверяем текущий статус инвентаризации
            if (currentInventoryingData.IS_DONE === '1') {
                showNotification('red', 'Инвентаризация уже завершена');
                return;
            }

            if (!confirm('Вы уверены, что хотите завершить эту инвентаризацию?')) {
                return;
            }

            const inventoryingIdStr = String(currentInventoryingId);
            const currentDate = new Date().toISOString(); // Текущая дата в формате ISO

            BX24.callMethod('custom.updateinventorying', {
                id: inventoryingIdStr,
                is_done: '1',
                date_of_completion: currentDate
            }, response => {
                if (response.error()) {
                    console.error('Ошибка при завершении инвентаризации:', response.error());
                    showNotification('red', 'Ошибка при завершении инвентаризации: ' + (response.error().description || 'Неизвестная ошибка'));
                    return;
                }

                const data = response.data();
                if (data.success) {
                    showNotification('green', 'Инвентаризация успешно завершена');
                    // Обновляем данные с сервера
                    refreshInventoryingData(() => {
                        // Обновляем список инвентаризаций
                        loadInventoryingList();
                        // Обновляем модальное окно деталей (если оно открыто)
                        if (document.getElementById('detail-modal').style.display === 'block') {
                            openDetailModal(currentInventoryingId);
                        }
                        // Закрываем модальное окно редактирования
                        closeModal('edit-modal');
                    });
                } else {
                    showNotification('red', 'Не удалось завершить инвентаризацию');
                }
            });
        }

        
        // Проверка и уведомление о возможности завершения инвентаризации
        function checkAndCompleteInventorying() {
            if (!currentInventoryingId || !currentInventoryingData) {
                console.error('currentInventoryingId или currentInventoryingData не установлены');
                return;
            }

            // Проверяем, завершена ли уже инвентаризация
            if (currentInventoryingData.IS_DONE === '1') {
                console.log('Инвентаризация уже завершена, пропускаем проверку');
                return;
            }

            // Проверяем, все ли элементы инвентаря помечены как отсканированные
            const allConfirmed = currentInventoryingData.INVENTORY_ITEMS.every(item => 
                item.SCANNED === '1' || item.MANUALLY_CONFIRMED === '1'
            );

            if (allConfirmed && currentInventoryingData.INVENTORY_ITEMS.length > 0) {
                console.log('Все элементы инвентаря помечены, показываем уведомление');
                showNotification('green', 'Все элементы инвентаря помечены! Вы можете завершить инвентаризацию вручную.');
            } else {
                console.log('Не все элементы инвентаря помечены, инвентаризация не завершена');
            }
        }

        // Закрытие модального окна
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Функция сканирования
        function scanInventory(inventoryingId) {
            currentInventoryingId = inventoryingId;
            console.log('Запуск сканирования для инвентаризации:', currentInventoryingId);
            openCameraPopup();
        }

        // Открытие попапа управления пользователями
        function openUsersPopup() {
            const popup = document.getElementById('users-popup');
            const usersList = document.getElementById('users-popup-list');
            usersList.innerHTML = '';

            // Проверяем, что currentInventoryingData и USERS существуют
            if (!currentInventoryingData || !currentInventoryingData.USERS) {
                console.error('currentInventoryingData или USERS не определены:', currentInventoryingData);
                usersList.innerHTML = '<p>Ошибка: данные инвентаризации не загружены.</p>';
                return;
            }

            // Загружаем список всех пользователей
            BX24.callMethod('user.get', {}, response => {
                if (response.error()) {
                    console.error('Ошибка загрузки пользователей:', response.error());
                    usersList.innerHTML = '<p>Ошибка загрузки пользователей.</p>';
                    return;
                }

                const allUsers = response.data();
                allUsers.forEach(user => {
                    const isChecked = currentInventoryingData.USERS.includes(String(user.ID)) ? 'checked' : '';
                    usersList.innerHTML += `
                        <label class="d-block">
                            <input type="checkbox" value="${user.ID}" ${isChecked} onchange="updateUserSelection(this)">
                            ${user.LAST_NAME} ${user.NAME}
                        </label>`;
                });

                popup.style.display = 'block';
            });
        }

        // Обновление списка пользователей при изменении чекбокса
        function updateUserSelection(checkbox) {
            const userId = String(checkbox.value); // Приводим к строке
            if (checkbox.checked) {
                // Добавляем пользователя в список, если он еще не добавлен
                if (!currentInventoryingData.USERS.includes(userId)) {
                    currentInventoryingData.USERS.push(userId);
                }
            } else {
                // Удаляем пользователя из списка
                currentInventoryingData.USERS = currentInventoryingData.USERS.filter(id => id !== userId);
            }
            console.log('Обновленный список пользователей:', currentInventoryingData.USERS);
            // Сохраняем изменения на сервере
            saveInventoryingChanges();
            showNotification('green', 'Список пользователей обновлен');
        }

        // Закрытие попапа управления пользователями
        function closeUsersPopup() {
            document.getElementById('users-popup').style.display = 'none';
        }

        // Открытие камеры
        function openCameraPopup() {
            document.getElementById('cameraPopupOverlay').style.display = 'block';
            document.getElementById('cameraPopup').style.display = 'block';
            startCamera('environment');
        }

        // Запуск камеры
        function startCamera(facingMode = 'environment') {
            const video = document.getElementById('video');
            navigator.mediaDevices.getUserMedia({
                video: { facingMode }
            }).then(stream => {
                video.srcObject = stream;
                currentStream = stream;
                startQRScanner();
            }).catch(error => {
                console.error('Ошибка доступа к камере:', error);
                alert('Не удалось получить доступ к камере.');
                closeCameraPopup();
            });
        }

        // Сканирование QR-кода
        function startQRScanner() {
            const video = document.getElementById('video');
            const canvas = document.createElement('canvas');
            const canvasContext = canvas.getContext('2d');
            scanningQR = true;

            function scanQR() {
                if (!scanningQR) return;

                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvasContext.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const imageData = canvasContext.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height);

                    if (code && !isProcessingQR) {
                        const qrData = code.data;
                        if (qrData !== lastScannedQR) { // Проверяем, новый ли QR-код
                            console.log('Распознанный QR-код:', qrData);
                            lastScannedQR = qrData; // Обновляем последний отсканированный код
                            isProcessingQR = true; // Блокируем повторную обработку
                            handleQRCode(qrData);
                            // Сбрасываем флаг через 1 секунду, чтобы разрешить новое сканирование
                            setTimeout(() => {
                                isProcessingQR = false;
                            }, 1000);
                        }
                    }
                }
                requestAnimationFrame(scanQR);
            }

            scanQR();
        }

        // Обработка QR-кода
        function handleQRCode(data) {
            let inventoryId;
            try {
                const url = new URL(data);
                inventoryId = url.searchParams.get('id');
                console.log('Извлеченный inventoryId:', inventoryId);
            } catch (e) {
                console.error('Некорректный URL в QR-коде:', data);
                showNotification('red', 'QR-код не подходит');
                isProcessingQR = false; // Разрешаем дальнейшее сканирование
                return;
            }

            if (inventoryId) {
                checkInventoryInInventorying(inventoryId);
            } else {
                console.error('ID инвентаря не найден в QR-коде');
                showNotification('red', 'QR-код не содержит ID инвентаря');
                isProcessingQR = false; // Разрешаем дальнейшее сканирование
            }
        }

        // Проверка принадлежности инвентаря к инвентаризации
        function checkInventoryInInventorying(inventoryId) {
            if (!currentInventoryingId) {
                console.error('currentInventoryingId не установлен');
                showNotification('red', 'Ошибка: ID инвентаризации не установлен');
                isProcessingQR = false;
                return;
            }

            const inventoryingIdStr = String(currentInventoryingId);
            const inventoryIdStr = String(inventoryId);

            console.log('Проверка принадлежности инвентаря:', { inventorying_id: inventoryingIdStr, inventory_id: inventoryIdStr });
            BX24.callMethod('custom.checkinventoryinventorying', {
                inventorying_id: inventoryingIdStr,
                inventory_id: inventoryIdStr
            }, response => {
                if (response.error()) {
                    console.error('Ошибка проверки инвентаря:', response.error());
                    const errorMessage = response.error().description || 'Неизвестная ошибка';
                    showNotification('red', 'Ошибка проверки инвентаря: ' + errorMessage);
                    isProcessingQR = false;
                    return;
                }

                const data = response.data();
                console.log('Ответ от сервера (checkinventoryinventorying):', data);

                if (data && data.result && data.result.is_in_inventorying) {
                    markAsScanned(inventoryId);
                } else {
                    showNotification('red', 'Этот инвентарь не состоит в этой инвентаризации',
                        `<a href="inventory.php?id=${inventoryId}" onclick="navigateToInventory(${inventoryId}); return false;">Перейти к инвентарю</a>`);
                    isProcessingQR = false;
                }
            });
        }
        function markAsScanned(inventoryId) {
            const inventoryingIdStr = String(currentInventoryingId);
            const inventoryIdStr = String(inventoryId);

            console.log('Пометка инвентаря как отсканированного:', { inventorying_id: inventoryingIdStr, inventory_id: inventoryIdStr });
            BX24.callMethod('custom.markinventoryscanned', {
                inventorying_id: inventoryingIdStr,
                inventory_id: inventoryIdStr
            }, response => {
                if (response.error()) {
                    console.error('Ошибка при пометке инвентаря:', response.error());
                    showNotification('red', 'Ошибка при пометке инвентаря: ' + (response.error().description || 'Неизвестная ошибка'));
                    vibrateDevice(100);
                    isProcessingQR = false;
                    return;
                }

                const data = response.data();
                console.log('Ответ от сервера (markinventoryscanned):', data);

                if (data && data.result && data.result.success) {
                    updateInventoryItemUI(inventoryId, true);
                    if (data.result.already_scanned) {
                        showCheckmarkAnimation();
                        showNotification('green', 'Этот инвентарь уже отмечен как отсканированный');
                        vibrateDevice(200);
                    } else {
                        showCheckmarkAnimation();
                        showNotification('green', 'Инвентарь успешно отсканирован');
                        vibrateDevice([200, 100, 200]);
                    }
                    refreshInventoryingData(() => {
                        checkAndCompleteInventorying();
                    });
                } else {
                    showNotification('red', 'Не удалось пометить инвентарь как отсканированный');
                    vibrateDevice(100);
                }
                isProcessingQR = false; // Разрешаем новое сканирование после завершения
            });
        }
        // Функция для вызова вибрации
        function vibrateDevice(pattern = 200) {
    console.log('Попытка вызвать вибрацию с паттерном:', pattern);

    // Проверяем наличие Bitrix24 API
    if (typeof BX !== 'undefined' && BX.MobileVibration) {
        try {
            console.log('Используем BX.MobileVibration');
            BX.MobileVibration.vibrate(Array.isArray(pattern) ? pattern : [pattern]);
            console.log('Вибрация через Bitrix24 выполнена');
        } catch (error) {
            console.error('Ошибка при вызове BX.MobileVibration:', error);
        }
    }
    // Запасной вариант через стандартный API
    else if ('vibrate' in navigator) {
        try {
            console.log('Используем navigator.vibrate');
            navigator.vibrate(pattern);
            console.log('Вибрация через стандартный API выполнена');
        } catch (error) {
            console.error('Ошибка при вызове navigator.vibrate:', error);
        }
    }
    // Если ничего не поддерживается
    else {
        console.warn('Вибрация не поддерживается: ни BX.MobileVibration, ни navigator.vibrate недоступны');
        alert("Вибрация доступна только в мобильном приложении Bitrix24 или в браузерах с поддержкой Vibration API");
    }
}
        // Обновление интерфейса
        function updateInventoryItemUI(inventoryId, scanned) {
            console.log('Обновление интерфейса для инвентаря:', inventoryId);
            const inventoryItems = document.querySelectorAll('.inventory-record');
            let found = false;
            inventoryItems.forEach(item => {
                const itemId = item.getAttribute('data-id');
                console.log('Проверяемый элемент:', { itemId, inventoryId });
                if (itemId == inventoryId) {
                    found = true;
                    const span = item.querySelector('.inventory-item');
                    if (span) {
                        if (scanned) {
                            span.classList.remove('not-confirmed');
                            span.classList.add('confirmed');
                            console.log('Инвентарь подсвечен зеленым:', inventoryId);
                        }
                    } else {
                        console.error('Не найден элемент .inventory-item в записи:', item);
                    }
                }
            });
            if (!found) {
                console.error('Не найдена запись инвентаря с ID:', inventoryId);
            }
        }

        // Показ анимации галочки
        function showCheckmarkAnimation() {
            const checkmark = document.getElementById('checkmark');
            checkmark.style.display = 'block';
            setTimeout(() => {
                checkmark.style.display = 'none';
            }, 1000);
        }

        // Показ уведомления
        function showNotification(color, message, additionalInfo) {
            const notification = document.getElementById('notification');
            notification.innerHTML = `<p style="color: ${color};">${message}</p>${additionalInfo || ''}`;
            notification.style.display = 'block';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        // Закрытие камеры
        function closeCameraPopup() {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }
            scanningQR = false;
            isProcessingQR = false; // Сбрасываем флаг обработки
            lastScannedQR = null; // Очищаем последний QR-код
            document.getElementById('cameraPopupOverlay').style.display = 'none';
            document.getElementById('cameraPopup').style.display = 'none';

            // Очищаем уведомления и анимации
            const notification = document.getElementById('notification');
            const checkmark = document.getElementById('checkmark');
            const cross = document.getElementById('cross');
            notification.style.display = 'none';
            checkmark.style.display = 'none';
            cross.style.display = 'none';

            // Очищаем все таймеры уведомлений
            clearAllTimeouts();
        }

        // Вспомогательная функция для очистки всех таймеров
        function clearAllTimeouts() {
            const highestId = setTimeout(() => {});
            for (let i = 0; i < highestId; i++) {
                clearTimeout(i);
            }
        }

        // Переход к инвентарю
        function navigateToInventory(inventoryId) {
            alert(`Переход к инвентарю с ID ${inventoryId} (функционал не реализован)`);
        }
        // Проверка прав при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof BX24 === 'undefined') {
                showError('Ошибка: BX24 не загружен. Откройте страницу через Bitrix24.');
                console.error('BX24 не загружен');
                return;
            }

            BX24.init(function() {
                let currentUserId = null;

                // Получаем ID текущего пользователя
                if (BX24.user && typeof BX24.user.getId === 'function') {
                    currentUserId = BX24.user.getId();
                    checkPermissions(currentUserId);
                } else {
                    BX24.callMethod('user.current', {}, function(result) {
                        if (result.error()) {
                            showError('Ошибка получения текущего пользователя: ' + result.error());
                            console.error('Ошибка получения текущего пользователя:', result.error());
                            return;
                        }
                        currentUserId = result.data().ID;
                        checkPermissions(currentUserId);
                    });
                }
            });
        });

        function checkPermissions(userId) {
            fetchToken().then(access_token => {
                BX24.callMethod(
                    'custom.userrules',
                    { action: 'get_permissions', user_id: userId },
                    function(result) {
                        if (result.error()) {
                            showError('Ошибка получения прав: ' + result.error());
                            console.error('Ошибка получения прав:', result.error());
                            return;
                        }

                        const permissions = result.data().result.permission || 'view';
                        setupInterfaceBasedOnPermissions(permissions);
                    },
                    { auth: access_token }
                );
            }).catch(err => {
                showError('Ошибка получения токена: ' + err.message);
                console.error('Ошибка получения токена:', err);
            });
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

        function setupInterfaceBasedOnPermissions(permissions) {
            const loader = document.getElementById('loader');
            const accessDenied = document.getElementById('accessDenied');
            const mainContainer = document.getElementById('mainContainer');
            const inventoryingList = document.getElementById('inventorying-list'); // Добавляем селектор списка

            // Скрываем лоадер
            if (loader) loader.style.display = 'none';

            if (permissions === 'view') {
                // Показываем сообщение "Доступ запрещён" и только кнопку "Назад"
                if (accessDenied) accessDenied.style.display = 'block';
                if (mainContainer) {
                    mainContainer.style.display = 'block';
                    const headerButtons = mainContainer.querySelector('.header-buttons');
                    headerButtons.innerHTML = '<a href="pril.php" class="button" id="backButton">Назад</a>';
                    if (inventoryingList) {
                        inventoryingList.style.display = 'none'; // Скрываем список
                        inventoryingList.innerHTML = ''; // Очищаем содержимое списка
                    }
                }
            } else if (permissions === 'edit' || permissions === 'full') {
                // Показываем полный интерфейс
                if (accessDenied) accessDenied.style.display = 'none';
                if (mainContainer) {
                    mainContainer.style.display = 'block';
                    if (inventoryingList) inventoryingList.style.display = 'block'; // Показываем список
                    loadInventoryingList(); // Загружаем список инвентаризаций
                }
            } else {
                // Неизвестные права
                showError('Неизвестный тип прав: ' + permissions);
                console.error('Неизвестный тип прав:', permissions);
                if (accessDenied) accessDenied.style.display = 'block';
                if (mainContainer) {
                    mainContainer.style.display = 'block';
                    const headerButtons = mainContainer.querySelector('.header-buttons');
                    headerButtons.innerHTML = '<a href="pril.php" class="button" id="backButton">Назад</a>';
                    if (inventoryingList) {
                        inventoryingList.style.display = 'none'; // Скрываем список
                        inventoryingList.innerHTML = ''; // Очищаем содержимое списка
                    }
                }
            }
        }

        function showError(message) {
            const loader = document.getElementById('loader');
            const accessDenied = document.getElementById('accessDenied');
            const mainContainer = document.getElementById('mainContainer');
            const inventoryingList = document.getElementById('inventorying-list'); // Добавляем селектор списка

            if (loader) loader.style.display = 'none';
            if (accessDenied) {
                accessDenied.textContent = message;
                accessDenied.style.display = 'block';
            }
            if (mainContainer) {
                mainContainer.style.display = 'block';
                const headerButtons = mainContainer.querySelector('.header-buttons');
                headerButtons.innerHTML = '<a href="pril.php" class="button" id="backButton">Назад</a>';
                if (inventoryingList) {
                    inventoryingList.style.display = 'none'; // Скрываем список
                    inventoryingList.innerHTML = ''; // Очищаем содержимое списка
                }
            }
        }
    </script>
</body>
</html>