<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Компании</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Подключение Bitrix24 API -->
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
            max-width: auto;
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
        .container {
            width: 95%;
            max-width: 600px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 80px;
            margin-bottom: 20px;
            display: none; /* Скрываем по умолчанию до проверки прав */
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
        }
        /* Добавлен стиль для удалённых элементов */
        .record-item.deleted {
            background-color: #d3d3d3; /* Серая заливка для удалённых элементов */
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
                margin-right: 5px;
                margin-left: 5px;
            }
            .back-button {
                margin-left: 15px;
                padding-left: 10px;
            }
            .add-button {
                margin-right: 15px;
            }
            .filter-panel {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .filter-checkbox {
                display: flex;
                align-items: center;
                gap: 6px;
            }
            .filter-checkbox input {
                width: 16px;
                height: 16px;
            }
            .container {
                width: 95%;
                margin-top: 80px;
                padding: 10px;
            }
        }
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
            <label for="show-deleted" class="filter-checkbox">Показывать удалённые</label>
            <input type="checkbox" id="show-deleted" class="filter-checkbox">
        </div>
        <a href="https://predprod.reforma-sk.ru/local-pril/add/add_record_company.php?table=iplus_reference_company&field=COMPANY_NAME&dict_name=Компании&field_name=Название компании" class="add-button">+ Добавить</a>
    </div>
    <div class="container" id="mainContainer">
        <div class="header">
            <h1>Компании</h1>
        </div>
        <ul class="records" id="company-records">
            <!-- Здесь будут динамически загруженные записи о компаниях -->
        </ul>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof BX24 === 'undefined') {
        showError('Ошибка: BX24 не загружен. Откройте страницу через Bitrix24.');
        return;
    }

    BX24.init(function() {
        const companyRecordsList = document.getElementById('company-records');
        const showDeletedCheckbox = document.getElementById('show-deleted');
        const addButton = document.querySelector('.add-button');
        const controls = document.getElementById('controls');

        let userPermissions = null;
        let accessToken = null;
        let userId = null;
        let companies = [];

        // Функция получения токена
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

        // Функция получения прав пользователя
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

        // Настройка интерфейса в зависимости от прав пользователя
        function setupInterfaceBasedOnPermissions(permissions) {
            const loader = document.getElementById('loader');
            const accessDenied = document.getElementById('accessDenied');
            const mainContainer = document.getElementById('mainContainer');

            // Скрываем лоадер
            if (loader) loader.style.display = 'none';

            userPermissions = permissions; // Сохраняем права
            accessToken = token; // Сохраняем токен для дальнейших запросов

            if (permissions === 'view') {
                // Показываем сообщение "Доступ запрещён" и только кнопку "Назад"
                if (accessDenied) accessDenied.style.display = 'block';
                if (mainContainer) {
                    mainContainer.style.display = 'block';
                    // Оставляем только кнопку "Назад"
                    controls.innerHTML = '<a class="back-button" href="https://predprod.reforma-sk.ru/local-pril/manual.php">НАЗАД</a>';
                    // Скрываем список компаний
                    companyRecordsList.style.display = 'none';
                    companyRecordsList.innerHTML = '';
                }
            } else if (permissions === 'edit' || permissions === 'full') {
                // Показываем полный интерфейс
                if (accessDenied) accessDenied.style.display = 'none';
                if (mainContainer) {
                    mainContainer.style.display = 'block';
                    addButton.style.display = 'inline-block';
                    loadCompanies(); // Загружаем список компаний
                }
            } else {
                // Неизвестные права
                showError('Неизвестный тип прав: ' + permissions);
                if (mainContainer) {
                    mainContainer.style.display = 'block';
                    controls.innerHTML = '<a class="back-button" href="https://predprod.reforma-sk.ru/local-pril/manual.php">НАЗАД</a>';
                    companyRecordsList.style.display = 'none';
                    companyRecordsList.innerHTML = '';
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
                companyRecordsList.style.display = 'none';
                companyRecordsList.innerHTML = '';
            }
        }

        // Функция загрузки компаний
        function loadCompanies() {
            const showDeleted = showDeletedCheckbox.checked ? 'true' : 'false';
            BX24.callMethod(
                'custom.getiplusreferencecompany',
                { show_deleted: showDeleted, limit: 1000, offset: 0 },
                function(result) {
                    if (result.error()) {
                        console.error('Ошибка API:', result.error());
                        companyRecordsList.innerHTML = '<li>Ошибка загрузки данных</li>';
                        return;
                    }
                    companies = result.data().result || [];
                    // Сортировка: сначала по убыванию ACTIVE, затем по возрастанию ID
                    companies.sort((a, b) => {
                        // Сортировка по ACTIVE (1 - активные, 0 - удалённые)
                        if (a.ACTIVE !== b.ACTIVE) {
                            return b.ACTIVE - a.ACTIVE; // Убывание по ACTIVE
                        }
                        // Если ACTIVE одинаковое, сортировка по ID по возрастанию
                        return a.ID - b.ID;
                    });
                    displayCompanies(companies);
                }
            );
        }

        // Функция отображения списка компаний
        function displayCompanies(companies) {
            companyRecordsList.innerHTML = '';
            if (companies.length > 0) {
                companies.forEach(company => {
                    const li = document.createElement('li');
                    li.className = 'record-item';

                    let companyName = company.COMPANY_NAME || 'Без названия';
                    let isDeleted = company.ACTIVE === '0';

                    // Добавляем класс deleted для удалённых элементов
                    if (isDeleted) {
                        li.classList.add('deleted');
                    }

                    // Убрали подпись "(Удалена)"
                    let innerHtml = `<span>${companyName}</span>`;
                    // Кнопка удаления появляется, если запись не удалена и у пользователя права edit/full
                    if (!isDeleted && (userPermissions === 'edit' || userPermissions === 'full')) {
                        innerHtml += `<button class="delete-button" data-id="${company.ID}" data-name="${companyName}">✖</button>`;
                    }
                    li.innerHTML = innerHtml;
                    companyRecordsList.appendChild(li);
                });

                document.querySelectorAll('.delete-button').forEach(button => {
                    button.addEventListener('click', function() {
                        const companyId = this.getAttribute('data-id');
                        const companyName = this.getAttribute('data-name');
                        if (confirm(`Действительно удалить "${companyName}"?`)) {
                            deleteRecord(companyId);
                        }
                    });
                });
            } else {
                companyRecordsList.innerHTML = '<li>Нет данных о компаниях</li>';
            }
        }

        // Функция удаления компании
        function deleteRecord(recordId) {
            if (userPermissions === 'view') {
                alert('У вас нет прав на удаление');
                return;
            }
            BX24.callMethod(
                'custom.deleteiplusreferencecompany',
                { id: recordId },
                function(result) {
                    if (result.error()) {
                        alert('Ошибка удаления: ' + result.error());
                    } else {
                        alert(result.data().message || "Запись успешно удалена");
                        loadCompanies();
                    }
                },
                { auth: accessToken }
            );
        }

        // Получение ID пользователя и прав
        let token; // Переменная для хранения токена
        if (BX24.user && typeof BX24.user.getId === 'function') {
            userId = BX24.user.getId();
            fetchToken().then(fetchedToken => {
                token = fetchedToken;
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
                fetchToken().then(fetchedToken => {
                    token = fetchedToken;
                    return getUserPermissions(userId, token);
                }).then(permissions => {
                    setupInterfaceBasedOnPermissions(permissions);
                }).catch(err => {
                    console.error('Ошибка получения токена или прав:', err);
                    showError('Ошибка получения токена или прав: ' + err);
                });
            });
        }

        // Обработчик чекбокса "Показывать удалённые"
        showDeletedCheckbox.addEventListener('change', () => {
            if (userPermissions === 'edit' || userPermissions === 'full') {
                loadCompanies();
            }
        });
    });
});
    </script>
</body>
</html>