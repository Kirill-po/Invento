<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Справочник операций</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://api.bitrix24.com/api/v1/"></script>
    <style>
        body {
            font-family: 'Gilroy-Light', sans-serif;
            color: rgb(29, 25, 84);
            background-color: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 95%;
            max-width: 600px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            margin-top: 80px;
            margin-bottom: 20px;
            display: none; /* Скрываем по умолчанию до проверки прав */
        }
        .btn-submit, .btn-back {
            display: inline-block;
            color: #1D1954;
            text-decoration: none;
            font-size: 16px;
            border: 2px solid #e50045;
            padding: 10px 15px;
            border-radius: 5px;
            transition: 0.3s;
            cursor: pointer;
        }
        .btn-submit:hover, .btn-back:hover {
            background-color: #d0003f;
            color: #fff;
        }
        .dropdown-menu {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 5px;
            border-radius: 5px;
            width: 100%;
        }
        .dropdown-toggle {
            background-color: #fff;
            border: 1px solid #ccc;
            color: #1D1954;
        }
        .dropdown-toggle:disabled {
            background-color: #e9ecef;
            opacity: 1;
        }
        @font-face {
            font-family: 'Gilroy-Light';
            src: url('https://predprod.reforma-sk.ru/local-pril/Gilroy-Light.otf') format('opentype');
            font-weight: normal;
            font-style: normal;
        }
        .controls {
            position: fixed;
            top: 20px;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            z-index: 1000;
        }
        .back-button, .add-button {
            display: inline-block;
            color: #1D1954;
            text-decoration: none;
            font-size: clamp(16px, 2vw, 20px);
            border: 2px solid #e50045;
            padding: 10px 15px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .back-button:hover, .add-button:hover {
            background-color: #d0003f;
            color: #fff;
        }
        /* Скрываем кнопку "+ Добавить" по умолчанию */
        .add-button {
            display: none;
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
            border: 2px solid rgb(29, 25, 84) !important;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 10px;
            font-size: 18px;
            box-sizing: border-box;
        }
        .record-item.deleted {
            background-color: #d3d3d3;
        }
        .delete-button {
            background-color: white;
            border: 2px solid rgb(220, 0, 67);
            color: rgb(220, 0, 67);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            padding: 0;
            line-height: 20px;
        }
        .delete-button:hover {
            background-color: rgb(220, 0, 67);
            color: white;
        }
        .edit-button {
            background-color: white;
            border: 2px solid rgb(29, 25, 84);
            color: rgb(29, 25, 84);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            padding: 0;
            line-height: 20px;
            margin-right: 20px;
        }
        .edit-button:hover {
            background-color: rgb(29, 25, 84);
            color: white;
        }
        .filter-panel {
            display: flex;
            align-items: center;
        }
        .filter-panel label {
            margin-right: 10px;
            font-size: 16px;
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
        @media (max-width: 768px) {
            .controls {
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                padding: 0px;
            }
            .back-button, .add-button {
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
        .modal-body input, .modal-body select, .modal-body textarea {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .form-select, .form-control {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        #edit-allowed-users-list, #add-allowed-users-list {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            background-color: #fafafa;
        }
        #save-edit, #save-add {
            background-color: #e50045;
            color: #1D1954;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            transition: 0.3s;
            cursor: pointer;
        }
        #save-edit:hover, #save-add:hover {
            background-color: #d0003f;
            color: #1D1954;
        }
        .form-check-input[type="checkbox"] {
            width: 16px;
            height: 16px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            border-radius: 3px;
            background-color: #fff;
            cursor: pointer;
            position: relative;
            margin-right: 6px;
            display: inline-block;
            vertical-align: middle;
        }
        .form-check-input[type="checkbox"]:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .form-check-input[type="radio"] {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            border-radius: 50%;
            border: 1px solid #ccc;
            background-color: #fff;
            cursor: pointer;
            position: relative;
            display: inline-block;
            vertical-align: middle;
        }
        .form-check-input[type="radio"]:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
            box-shadow: inset 0 0 0 3px #fff;
        }
        .form-check-label {
            font-size: 16px;
            line-height: 1;
            vertical-align: middle;
        }
        .checkbox-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
        }
        .download-requisites {
            color: #1D1954;
            text-decoration: underline;
            margin-top: 10px;
            display: inline-block;
        }
        .controls { display: flex; justify-content: space-between; align-items: center; padding: 10px; }
        .records { list-style: none; padding: 0; }
        .records li { padding: 10px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
        .records li button { margin-left: 10px; }
    </style>
</head>
<body>
    <!-- Лоадер на время проверки прав -->
    <div class="loader" id="loader">Загрузка...</div>

    <!-- Сообщение для пользователей с правами view -->
    <div class="access-denied" id="accessDenied">Доступ запрещён</div>

    <div class="controls" id="controls">
        <a class="back-button" href="https://predprod.reforma-sk.ru/local-pril/manual.php">НАЗАД</a>
        <div class="filter-panel">
            <label for="show-deleted">Показывать удалённые</label>
            <input type="checkbox" id="show-deleted" class="form-check-input" />
        </div>
        <a href="#" class="add-button" id="addButton">+ Добавить</a>
    </div>

    <div class="container" id="mainContainer">
        <div class="header">
            <h1>Справочник операций</h1>
        </div>
        <ul class="records" id="operation-records"></ul>
    </div>

    <!-- Add Operation Modal -->
    <div class="modal fade" id="addOperationModal" tabindex="-1" aria-labelledby="addOperationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Операция: создание</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add-name-operation" class="form-label">Название операции</label>
                        <input type="text" class="form-control" id="add-name-operation">
                    </div>
                    <div class="mb-3">
                        <label for="add-direct-name" class="form-label">Название операции для исходного ответственного</label>
                        <input type="text" class="form-control" id="add-direct-name">
                    </div>
                    <div class="mb-3">
                        <label for="add-reverse-name" class="form-label">Название операции для конечного ответственного</label>
                        <input type="text" class="form-control" id="add-reverse-name">
                    </div>
                    <div class="mb-3">
                        <label for="add-template-file" class="form-label">Шаблон печатной формы</label>
                        <input type="file" id="add-template-file" accept=".docx" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Исходные статусы инвентаря:</label>
                        <div class="checkbox-list" id="add-initial-statuses-list"></div>
                    </div>
                    <div class="mb-3">
                        <label for="add-after-status" class="form-label">Статус после выполнения операции</label>
                        <select class="form-select" id="add-after-status">
                            <option value="">Выберите статус</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add-allowed-users" class="form-label">Пользователи с правом выполнения:</label>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button" id="add-allowed-users-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Выберите пользователей
                            </button>
                            <ul class="dropdown-menu w-100" id="add-allowed-users-list" style="max-height: 200px; overflow-y: auto;">
                                <!-- Пользователи будут добавлены через JavaScript -->
                            </ul>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="add-is-first">
                        <label class="form-check-label" for="add-is-first">Первая операция</label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="add-is-last">
                        <label class="form-check-label" for="add-is-last">Последняя операция</label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="add-require-confirmation">
                        <label class="form-check-label" for="add-require-confirmation">Требовать подтверждения</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Тип операции:</label>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="add-operation-type" id="add-type-direct" value="direct">
                            <label class="form-check-label" for="add-type-direct">Прямая операция</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="add-operation-type" id="add-type-reverse" value="reverse">
                            <label class="form-check-label" for="add-type-reverse">Обратная операция</label>
                        </div>
                    </div>
                    <a href="/local-pril/documents/requisites.docx" class="download-requisites" download>Скачать файл с реквизитами</a>
                    <button type="button" id="save-add" class="btn btn-primary mt-3">Сохранить</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Operation Modal -->
    <div class="modal fade" id="editOperationModal" tabindex="-1" aria-labelledby="editOperationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирование операции</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit-operation-id">
                    <!-- Блок для отображения всех трёх названий -->
                    <div class="mb-3" style="display:none;">
                        <h6>Текущие названия операции:</h6>
                        <p><strong>Название операции:</strong> <span id="edit-current-name-operation">—</span></p>
                        <p><strong>Название для исходного ответственного:</strong> <span id="edit-current-direct-name">—</span></p>
                        <p><strong>Название для конечного ответственного:</strong> <span id="edit-current-reverse-name">—</span></p>
                    </div>
                    <div class="mb-3">
                        <label for="edit-name-operation" class="form-label">Название операции</label>
                        <input type="text" class="form-control" id="edit-name-operation">
                    </div>
                    <div class="mb-3">
                        <label for="edit-direct-name" class="form-label">Название операции для исходного ответственного</label>
                        <input type="text" class="form-control" id="edit-direct-name">
                    </div>
                    <div class="mb-3">
                        <label for="edit-reverse-name" class="form-label">Название операции для конечного ответственного</label>
                        <input type="text" class="form-control" id="edit-reverse-name">
                    </div>
                    <div class="mb-3">
                        <label for="edit-template-file" class="form-label">Шаблон печатной формы</label>
                        <input type="file" id="edit-template-file" accept=".docx" class="form-control">
                        <div id="template-download"></div>
                    </div>
                    <div class="mb-3">
                        <label>Исходные статусы инвентаря:</label>
                        <div class="checkbox-list" id="edit-initial-statuses-list"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-after-status" class="form-label">Статус после выполнения операции</label>
                        <select class="form-select" id="edit-after-status">
                            <option value="">Выберите статус</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-allowed-users" class="form-label">Пользователи с правом выполнения:</label>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button" id="edit-allowed-users-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Выберите пользователей
                            </button>
                            <ul class="dropdown-menu w-100" id="edit-allowed-users-list" style="max-height: 200px; overflow-y: auto;">
                                <!-- Пользователи будут добавлены через JavaScript -->
                            </ul>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit-is-first">
                        <label class="form-check-label" for="edit-is-first">Первая операция</label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit-is-last">
                        <label class="form-check-label" for="edit-is-last">Последняя операция</label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit-require-confirmation">
                        <label class="form-check-label" for="edit-require-confirmation">Требовать подтверждения</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Тип операции:</label>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="edit-operation-type" id="edit-type-direct" value="direct">
                            <label class="form-check-label" for="edit-type-direct">Прямая операция</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="edit-operation-type" id="edit-type-reverse" value="reverse">
                            <label class="form-check-label" for="edit-type-reverse">Обратная операция</label>
                        </div>
                    </div>
                    <a href="/local-pril/documents/Explanation.docx" class="download-requisites" download>Скачать файл с реквизитами</a>
                    <button type="button" id="save-edit" class="btn btn-primary mt-3">Сохранить</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = new bootstrap.Modal(document.getElementById('editOperationModal'));
    const addModal = new bootstrap.Modal(document.getElementById('addOperationModal'));
    const operationRecordsList = document.getElementById('operation-records');
    const showDeletedCheckbox = document.getElementById('show-deleted');
    const addButton = document.getElementById('addButton');
    const controls = document.getElementById('controls');
    let statusOptions = [];
    let userOptions = [];
    let operations = [];
    let userPermissions = null;

    if (typeof BX24 === 'undefined') {
        showError('Ошибка: BX24 не загружен. Откройте страницу через Bitrix24.');
        return;
    }

    // Функция очистки полей модального окна
    function clearModalFields(modalPrefix) {
        console.log(`Clearing fields for ${modalPrefix} modal`); // Отладочный вывод
        document.getElementById(`${modalPrefix}-operation-id`).value = '';
        document.getElementById(`${modalPrefix}-name-operation`).value = '';
        document.getElementById(`${modalPrefix}-direct-name`).value = '';
        document.getElementById(`${modalPrefix}-reverse-name`).value = '';
        document.getElementById(`${modalPrefix}-after-status`).value = '';
        document.getElementById(`${modalPrefix}-template-file`).value = '';
        if (modalPrefix === 'edit') {
            document.getElementById('template-download').innerHTML = '';
            // Очистка новых полей для отображения названий
            const nameOperationElement = document.getElementById('edit-current-name-operation');
            const directNameElement = document.getElementById('edit-current-direct-name');
            const reverseNameElement = document.getElementById('edit-current-reverse-name');

            if (nameOperationElement) nameOperationElement.textContent = '—';
            if (directNameElement) directNameElement.textContent = '—';
            if (reverseNameElement) reverseNameElement.textContent = '—';
        }

        // Очистка чекбоксов для исходных статусов
        document.querySelectorAll(`#${modalPrefix}-initial-statuses-list input[type="checkbox"]`).forEach(checkbox => {
            checkbox.checked = false;
        });

        // Очистка чекбоксов для пользователей
        document.querySelectorAll(`#${modalPrefix}-allowed-users-list input[type="checkbox"]`).forEach(checkbox => {
            checkbox.checked = false;
        });

        // Сброс текста в выпадающем списке пользователей
        const allowedUsersDropdown = document.getElementById(`${modalPrefix}-allowed-users-dropdown`);
        allowedUsersDropdown.textContent = 'Выберите пользователей';

        // Очистка чекбоксов и радио-кнопок
        document.getElementById(`${modalPrefix}-is-first`).checked = false;
        document.getElementById(`${modalPrefix}-is-last`).checked = false;
        document.getElementById(`${modalPrefix}-require-confirmation`).checked = false;
        document.getElementById(`${modalPrefix}-type-direct`).checked = false;
        document.getElementById(`${modalPrefix}-type-reverse`).checked = false;

        // Сброс состояния полей (включение/отключение)
        const directName = document.getElementById(`${modalPrefix}-direct-name`);
        const reverseName = document.getElementById(`${modalPrefix}-reverse-name`);
        const initialStatuses = document.getElementById(`${modalPrefix}-initial-statuses-list`);
        const allowedUsersList = document.getElementById(`${modalPrefix}-allowed-users-list`);
        const allowedUsersDropdownDisabled = document.getElementById(`${modalPrefix}-allowed-users-dropdown`);

        directName.disabled = false;
        reverseName.disabled = false;
        initialStatuses.querySelectorAll('input').forEach(input => input.disabled = false);
        allowedUsersList.querySelectorAll('input').forEach(input => input.disabled = false);
        allowedUsersDropdownDisabled.disabled = false;
    }

    // Очистка полей при закрытии модальных окон
    document.getElementById('editOperationModal').addEventListener('hidden.bs.modal', function() {
        clearModalFields('edit');
    });

    document.getElementById('addOperationModal').addEventListener('hidden.bs.modal', function() {
        clearModalFields('add');
    });

    // Функция получения токена из tokens.json
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

    // Функция получения прав пользователя через custom.userrules
    function getUserPermissions(userId, token) {
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
                { auth: token }
            );
        });
    }

    // Fetch statuses
    function fetchStatuses() {
        return new Promise((resolve, reject) => {
            BX24.callMethod(
                'custom.getiplusreferencestatus',
                { show_deleted: 'false', limit: 1000, offset: 0 },
                function(result) {
                    if (result.error()) reject(result.error());
                    else {
                        statusOptions = result.data().result || [];
                        resolve();
                    }
                }
            );
        });
    }

    // Fetch users
    function fetchUsers() {
        return new Promise((resolve, reject) => {
            BX24.callMethod(
                'user.get',
                {},
                function(result) {
                    if (result.error()) reject(result.error());
                    else {
                        userOptions = result.data();
                        resolve();
                    }
                }
            );
        });
    }

    // Fetch operations
    function fetchOperations() {
        return new Promise((resolve, reject) => {
            BX24.callMethod(
                'custom.getiplusreferenceoperations',
                { show_deleted: document.getElementById('show-deleted').checked ? 'true' : 'false' },
                function(result) {
                    if (result.error()) reject(result.error());
                    else {
                        operations = result.data().result || [];
                        operations.sort((a, b) => {
                            if (a.ACTIVE !== b.ACTIVE) {
                                return b.ACTIVE - a.ACTIVE;
                            }
                            return a.ID - b.ID;
                        });
                        renderOperations();
                        resolve();
                    }
                }
            );
        });
    }

    // Настройка интерфейса в зависимости от прав пользователя
    function setupInterfaceBasedOnPermissions(permissions) {
        const loader = document.getElementById('loader');
        const accessDenied = document.getElementById('accessDenied');
        const mainContainer = document.getElementById('mainContainer');

        // Скрываем лоадер
        if (loader) loader.style.display = 'none';

        userPermissions = permissions; // Сохраняем права

        if (permissions === 'view') {
            // Показываем сообщение "Доступ запрещён" и только кнопку "Назад"
            if (accessDenied) accessDenied.style.display = 'block';
            if (mainContainer) {
                mainContainer.style.display = 'block';
                // Оставляем только кнопку "Назад"
                controls.innerHTML = '<a class="back-button" href="https://predprod.reforma-sk.ru/local-pril/manual.php">НАЗАД</a>';
                // Скрываем список операций
                operationRecordsList.style.display = 'none';
                operationRecordsList.innerHTML = '';
            }
        } else if (permissions === 'edit' || permissions === 'full') {
            // Показываем полный интерфейс
            if (accessDenied) accessDenied.style.display = 'none';
            if (mainContainer) {
                mainContainer.style.display = 'block';
                addButton.style.display = 'inline-block';
                // Загружаем данные
                Promise.all([fetchStatuses(), fetchUsers()])
                    .then(() => fetchOperations())
                    .catch(err => showError('Ошибка инициализации: ' + err));
            }
        } else {
            // Неизвестные права
            showError('Неизвестный тип прав: ' + permissions);
            if (mainContainer) {
                mainContainer.style.display = 'block';
                controls.innerHTML = '<a class="back-button" href="https://predprod.reforma-sk.ru/local-pril/manual.php">НАЗАД</a>';
                operationRecordsList.style.display = 'none';
                operationRecordsList.innerHTML = '';
            }
        }
    }

    // Функция отображения ошибки
    function showError(message) {
        const loader = document.getElementById('loader');
        const accessDenied = document.getElementById('accessDenied');
        const mainContainer = document.getElementById('mainContainer');

        if (loader) loader.style.display = 'none';
        if (accessDenied) {
            accessDenied.textContent = message;
            accessDenied.style.display = 'block';
        }
        if (mainContainer) {
            mainContainer.style.display = 'block';
            controls.innerHTML = '<a class="back-button" href="https://predprod.reforma-sk.ru/local-pril/manual.php">НАЗАД</a>';
            operationRecordsList.style.display = 'none';
            operationRecordsList.innerHTML = '';
        }
    }

    // Инициализация BX24
    BX24.init(function() {
        // Получаем ID текущего пользователя через user.current
        let userId;
        if (BX24.user && typeof BX24.user.getId === 'function') {
            userId = BX24.user.getId();
            fetchToken().then(token => {
                return getUserPermissions(userId, token);
            }).then(permissions => {
                setupInterfaceBasedOnPermissions(permissions);
            }).catch(err => {
                console.error('Ошибка получения токена или прав:', err);
                showError('Ошибка получения токена или прав: ' + err);
            });
        } else {
            BX24.callMethod('user.current', {}, function(result) {
                if (result.error()) {
                    console.error('Ошибка получения текущего пользователя:', result.error());
                    showError('Ошибка получения пользователя: ' + result.error());
                    return;
                }
                userId = result.data().ID;
                fetchToken().then(token => {
                    return getUserPermissions(userId, token);
                }).then(permissions => {
                    setupInterfaceBasedOnPermissions(permissions);
                }).catch(err => {
                    console.error('Ошибка получения токена или прав:', err);
                    showError('Ошибка получения токена или прав: ' + err);
                });
            });
        }
    });

    // Render operations list
    function renderOperations() {
        operationRecordsList.innerHTML = '';
        operations.forEach(op => {
            const li = document.createElement('li');
            li.className = 'record-item';
            if (op.ACTIVE === '0') {
                li.classList.add('deleted');
            }
            // Гибкое получение имени операции
            const nameOperation = op.NAME_OPERATION || op.name_operation || '';
            const directName = op.DIRECT_OPERATION_NAME || op.direct_operation_name || '';
            const reverseName = op.REVERSE_OPERATION_NAME || op.reverse_operation_name || '';
            const operationName = nameOperation || `${directName} / ${reverseName}`;
            li.innerHTML = `
                ${operationName} 
                (Статус после: ${statusOptions.find(s => s.ID === (op.AFTER_OPERATION_STATUS || op.after_operation_status))?.STATUS_NAME || 'Не указан'})
                ${op.PRINTED_FORM_TEMPLATE || op.printed_form_template 
                    ? `<span>Шаблон загружен (${op.NAME_OF_TEMPLATE || op.name_of_template || 'template.docx'})</span>` 
                    : '<span>Шаблон отсутствует</span>'}
                <div class="button-group">
                    ${op.ACTIVE === '1' && (userPermissions === 'edit' || userPermissions === 'full') ? `
                        <button class="edit-button" data-id="${op.ID}">✎</button>
                        <button class="delete-button" data-id="${op.ID}">×</button>
                    ` : ''}
                </div>
            `;
            operationRecordsList.appendChild(li);
        });

        document.querySelectorAll('.edit-button').forEach(btn => {
            btn.addEventListener('click', () => openEditModal(btn.dataset.id));
        });
        document.querySelectorAll('.delete-button').forEach(btn => {
            btn.addEventListener('click', () => deleteOperation(btn.dataset.id));
        });
    }

    // Populate form fields
    function populateFields(modalPrefix, operation = null) {
        const afterStatusSelect = document.getElementById(`${modalPrefix}-after-status`);
        afterStatusSelect.innerHTML = '<option value="">Выберите статус</option>';
        statusOptions.forEach(status => {
            afterStatusSelect.innerHTML += `<option value="${status.ID}">${status.STATUS_NAME}</option>`;
        });

        const initialStatusesList = document.getElementById(`${modalPrefix}-initial-statuses-list`);
        initialStatusesList.innerHTML = '';
        statusOptions.forEach(status => {
            initialStatusesList.innerHTML += `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="${status.ID}" id="${modalPrefix}_initial_${status.ID}">
                    <label class="form-check-label" for="${modalPrefix}_initial_${status.ID}">${status.STATUS_NAME}</label>
                </div>`;
        });

        const allowedUsersList = document.getElementById(`${modalPrefix}-allowed-users-list`);
        allowedUsersList.innerHTML = '';
        userOptions.forEach(user => {
            allowedUsersList.innerHTML += `
                <li>
                    <div class="form-check px-3 py-1">
                        <input class="form-check-input" type="checkbox" value="${user.ID}" id="${modalPrefix}_user_${user.ID}">
                        <label class="form-check-label" for="${modalPrefix}_user_${user.ID}">${user.NAME} ${user.LAST_NAME}</label>
                    </div>
                </li>`;
        });

        const allowedUsersDropdown = document.getElementById(`${modalPrefix}-allowed-users-dropdown`);
        allowedUsersDropdown.textContent = 'Выберите пользователей';

        if (operation) {
            console.log(`Populating fields for ${modalPrefix} modal with operation:`, operation);

            // Гибкое получение значений, учитывая возможные различия в регистрах ключей
            const nameOperation = operation.NAME_OPERATION || operation.name_operation || '';
            const directName = operation.DIRECT_OPERATION_NAME || operation.direct_operation_name || '';
            const reverseName = operation.REVERSE_OPERATION_NAME || operation.reverse_operation_name || '';

            document.getElementById(`${modalPrefix}-operation-id`).value = operation.ID || '';
            document.getElementById(`${modalPrefix}-name-operation`).value = nameOperation;
            document.getElementById(`${modalPrefix}-direct-name`).value = directName;
            document.getElementById(`${modalPrefix}-reverse-name`).value = reverseName;
            document.getElementById(`${modalPrefix}-after-status`).value = operation.AFTER_OPERATION_STATUS || operation.after_operation_status || '';

            // Отображение всех трёх названий в модальном окне редактирования
            if (modalPrefix === 'edit') {
                const nameOperationElement = document.getElementById('edit-current-name-operation');
                const directNameElement = document.getElementById('edit-current-direct-name');
                const reverseNameElement = document.getElementById('edit-current-reverse-name');

                if (nameOperationElement) {
                    nameOperationElement.textContent = nameOperation || '—';
                } else {
                    console.error('Element with ID "edit-current-name-operation" not found');
                }
                if (directNameElement) {
                    directNameElement.textContent = directName || '—';
                } else {
                    console.error('Element with ID "edit-current-direct-name" not found');
                }
                if (reverseNameElement) {
                    reverseNameElement.textContent = reverseName || '—';
                } else {
                    console.error('Element with ID "edit-current-reverse-name" not found');
                }
            }

            let initialStatuses = [];
            try {
                initialStatuses = JSON.parse(operation.INITIAL_STATUSES || operation.initial_statuses || '[]');
                if (!Array.isArray(initialStatuses)) {
                    initialStatuses = [];
                }
            } catch (e) {
                console.error('Ошибка парсинга INITIAL_STATUSES:', e);
                initialStatuses = [];
            }
            initialStatuses.forEach(statusId => {
                const checkbox = document.getElementById(`${modalPrefix}_initial_${statusId}`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });

            let allowedUsers = [];
            try {
                allowedUsers = JSON.parse(operation.ALLOWED_USERS || operation.allowed_users || '[]');
                if (!Array.isArray(allowedUsers)) {
                    allowedUsers = [];
                }
            } catch (e) {
                console.error('Ошибка парсинга ALLOWED_USERS:', e);
                allowedUsers = [];
            }
            allowedUsers.forEach(userId => {
                const checkbox = document.getElementById(`${modalPrefix}_user_${userId}`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });

            const selectedUsersCount = allowedUsers.length;
            allowedUsersDropdown.textContent = selectedUsersCount > 0 ? `Выбрано: ${selectedUsersCount}` : 'Выберите пользователей';

            document.getElementById(`${modalPrefix}-is-first`).checked = String(operation.FIRST_OPERATION || operation.first_operation) === '1';
            document.getElementById(`${modalPrefix}-is-last`).checked = String(operation.LAST_OPERATION || operation.last_operation) === '1';
            document.getElementById(`${modalPrefix}-require-confirmation`).checked = String(operation.REQUIRE_CONFIRMATION || operation.require_confirmation) === '1';

            if (String(operation.IS_DIRECT || operation.is_direct) === '1') {
                document.getElementById(`${modalPrefix}-type-direct`).checked = true;
            }
            if (String(operation.IS_REVERSE || operation.is_reverse) === '1') {
                document.getElementById(`${modalPrefix}-type-reverse`).checked = true;
            }

            if (modalPrefix === 'edit' && (operation.PRINTED_FORM_TEMPLATE || operation.printed_form_template)) {
                const template = operation.PRINTED_FORM_TEMPLATE || operation.printed_form_template;
                const templateName = operation.NAME_OF_TEMPLATE || operation.name_of_template || 'template.docx';
                document.getElementById('template-download').innerHTML = `
                    <div>
                        <span>Текущий шаблон: ${templateName}</span>
                        <button class="btn btn-primary btn-sm" onclick="downloadBase64File('${template}', '${templateName}')">Скачать текущий шаблон</button>
                    </div>
                `;
            } else if (modalPrefix === 'edit') {
                document.getElementById('template-download').innerHTML = '<span>Шаблон отсутствует</span>';
            }
        }

        document.querySelectorAll(`#${modalPrefix}-allowed-users-list input[type="checkbox"]`).forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const selectedCount = document.querySelectorAll(`#${modalPrefix}-allowed-users-list input[type="checkbox"]:checked`).length;
                allowedUsersDropdown.textContent = selectedCount > 0 ? `Выбрано: ${selectedCount}` : 'Выберите пользователей';
            });
        });
    }

    // Toggle field states based on "First Operation"
    function toggleFirstOperation(modalPrefix, isChecked) {
        const directName = document.getElementById(`${modalPrefix}-direct-name`);
        const reverseName = document.getElementById(`${modalPrefix}-reverse-name`);
        const initialStatuses = document.getElementById(`${modalPrefix}-initial-statuses-list`);
        const allowedUsersList = document.getElementById(`${modalPrefix}-allowed-users-list`);
        const allowedUsersDropdown = document.getElementById(`${modalPrefix}-allowed-users-dropdown`);
        const isLast = document.getElementById(`${modalPrefix}-is-last`);

        if (isChecked) {
            directName.disabled = true;
            directName.value = '';
            reverseName.disabled = true;
            reverseName.value = '';
            initialStatuses.querySelectorAll('input').forEach(input => {
                input.disabled = true;
                input.checked = false;
            });
            allowedUsersList.querySelectorAll('input').forEach(input => {
                input.disabled = true;
                input.checked = false;
            });
            allowedUsersDropdown.disabled = true;
            allowedUsersDropdown.textContent = 'Выберите пользователей';
            isLast.checked = false;
        } else {
            directName.disabled = false;
            reverseName.disabled = false;
            initialStatuses.querySelectorAll('input').forEach(input => input.disabled = false);
            allowedUsersList.querySelectorAll('input').forEach(input => input.disabled = false);
            allowedUsersDropdown.disabled = false;
        }
    }

    // Event listeners
    showDeletedCheckbox.addEventListener('change', () => {
        if (userPermissions === 'edit' || userPermissions === 'full') {
            fetchOperations();
        }
    });

    addButton.addEventListener('click', function(e) {
        e.preventDefault();
        if (userPermissions === 'view') {
            alert('У вас нет прав на добавление');
            return;
        }
        populateFields('add');
        addModal.show();
    });

    document.getElementById('add-is-first').addEventListener('change', function() {
        toggleFirstOperation('add', this.checked);
    });
    document.getElementById('edit-is-first').addEventListener('change', function() {
        toggleFirstOperation('edit', this.checked);
    });
    document.getElementById('add-is-last').addEventListener('change', function() {
        if (this.checked) document.getElementById('add-is-first').checked = false;
    });
    document.getElementById('edit-is-last').addEventListener('change', function() {
        if (this.checked) document.getElementById('edit-is-first').checked = false;
    });

    // Save add
    document.getElementById('save-add').addEventListener('click', function() {
        if (userPermissions === 'view') {
            alert('У вас нет прав на добавление');
            return;
        }
        saveOperation('add');
    });

    // Save edit
    document.getElementById('save-edit').addEventListener('click', function() {
        if (userPermissions === 'view') {
            alert('У вас нет прав на редактирование');
            return;
        }
        saveOperation('edit');
    });

    // Open edit modal
    function openEditModal(id) {
        if (userPermissions === 'view') {
            alert('У вас нет прав на редактирование');
            return;
        }
        const operation = operations.find(op => op.ID === id);
        if (operation) {
            populateFields('edit', operation);
            editModal.show();
        }
    }

    // Delete operation
    function deleteOperation(id) {
        if (userPermissions === 'view') {
            alert('У вас нет прав на удаление');
            return;
        }
        if (confirm('Вы уверены, что хотите удалить эту операцию?')) {
            BX24.callMethod('custom.deleteiplusreferenceoperations', { id: id }, function(result) {
                if (result.error()) {
                    alert('Ошибка удаления: ' + result.error());
                } else {
                    alert('Операция успешно удалена');
                    fetchOperations();
                }
            });
        }
    }

    // Save operation
    function saveOperation(mode) {
        const modalPrefix = mode === 'add' ? 'add' : 'edit';
        const isFirst = document.getElementById(`${modalPrefix}-is-first`).checked;
        const nameOperation = document.getElementById(`${modalPrefix}-name-operation`).value.trim();
        const directName = document.getElementById(`${modalPrefix}-direct-name`).value.trim();
        const reverseName = document.getElementById(`${modalPrefix}-reverse-name`).value.trim();
        const afterStatus = document.getElementById(`${modalPrefix}-after-status`).value;
        const initialStatuses = Array.from(document.querySelectorAll(`#${modalPrefix}-initial-statuses-list input:checked`)).map(input => input.value);
        const allowedUsers = Array.from(document.querySelectorAll(`#${modalPrefix}-allowed-users-list input:checked`)).map(input => input.value);
        const isLast = document.getElementById(`${modalPrefix}-is-last`).checked;
        const requireConfirmation = document.getElementById(`${modalPrefix}-require-confirmation`).checked;
        const isDirect = document.getElementById(`${modalPrefix}-type-direct`).checked;
        const isReverse = document.getElementById(`${modalPrefix}-type-reverse`).checked;

        if (!nameOperation) {
            alert('Укажите название операции');
            return;
        }
        if (!isFirst && (!directName || !reverseName || !afterStatus || allowedUsers.length === 0)) {
            alert('Заполните все обязательные поля');
            return;
        }
        if (isFirst && !afterStatus) {
            alert('Выберите статус после выполнения операции');
            return;
        }

        const data = {
            name_operation: nameOperation,
            direct_name: directName,
            reverse_name: reverseName,
            after_status: afterStatus,
            initial_statuses: JSON.stringify(initialStatuses),
            allowed_users: allowedUsers.join(','),
            first_operation: isFirst ? '1' : '0',
            last_operation: isLast ? '1' : '0',
            require_confirmation: requireConfirmation ? '1' : '0',
            is_direct: isDirect ? '1' : '0',
            is_reverse: isReverse ? '1' : '0'
        };

        if (mode === 'edit') {
            data.id = document.getElementById('edit-operation-id').value;
        }

        const fileInput = document.getElementById(`${modalPrefix}-template-file`);
        if (fileInput.files[0]) {
            const file = fileInput.files[0];
            if (file.name.split('.').pop().toLowerCase() !== 'docx') {
                alert('Файл должен быть в формате .docx');
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                data.template_base64 = e.target.result.split(',')[1];
                data.template_file_name = file.name;
                sendSaveRequest(data, mode);
            };
            reader.readAsDataURL(file);
        } else {
            sendSaveRequest(data, mode);
        }
    }

    function sendSaveRequest(data, mode) {
        const method = mode === 'add' ? 'custom.addiplusreferenceoperations' : 'custom.updateiplusreferenceoperations';
        BX24.callMethod(method, data, function(result) {
            if (result.error()) {
                alert(`Ошибка ${mode === 'add' ? 'добавления' : 'обновления'}: ` + result.error());
            } else {
                alert(`Операция успешно ${mode === 'add' ? 'добавлена' : 'обновлена'}`);
                (mode === 'add' ? addModal : editModal).hide();

                if (mode === 'edit') {
                    const operationId = data.id;
                    const operationIndex = operations.findIndex(op => op.ID === operationId);
                    if (operationIndex !== -1) {
                        operations[operationIndex] = {
                            ...operations[operationIndex],
                            NAME_OPERATION: data.name_operation,
                            DIRECT_OPERATION_NAME: data.direct_name,
                            REVERSE_OPERATION_NAME: data.reverse_name,
                            AFTER_OPERATION_STATUS: data.after_status,
                            INITIAL_STATUSES: data.initial_statuses,
                            ALLOWED_USERS: data.allowed_users,
                            FIRST_OPERATION: data.first_operation,
                            LAST_OPERATION: data.last_operation,
                            REQUIRE_CONFIRMATION: data.require_confirmation,
                            IS_DIRECT: data.is_direct,
                            IS_REVERSE: data.is_reverse,
                            PRINTED_FORM_TEMPLATE: data.template_base64 || operations[operationIndex].PRINTED_FORM_TEMPLATE,
                            NAME_OF_TEMPLATE: data.template_file_name || operations[operationIndex].NAME_OF_TEMPLATE,
                        };
                        renderOperations();
                    }
                }

                fetchOperations();
            }
        });
    }
});

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
        link.download = fileName || 'template.docx';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (e) {
        console.error('Ошибка при скачивании файла:', e);
        alert('Ошибка при скачивании файла');
    }
}
</script>
</body>
</html>