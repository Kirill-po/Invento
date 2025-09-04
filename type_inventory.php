<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Типы инвентаря</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Подключение Bitrix24 JS API -->
    <script src="https://api.bitrix24.com/api/v1/"></script>
    <style>
        /* Плавная прокрутка для всех устройств */
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

    <!-- Панель управления -->
    <div class="controls" id="controls">
        <a class="back-button" href="https://predprod.reforma-sk.ru/local-pril/manual.php">НАЗАД</a>
        <div class="filter-panel">
            <label for="show-deleted" class="filter-checkbox">Показывать удалённые</label>
            <input type="checkbox" id="show-deleted" class="filter-checkbox">
        </div>
        <!-- Ссылка на страницу добавления, изначально скрыта -->
        <a href="https://predprod.reforma-sk.ru/local-pril/add/add_record_type.php" class="add-button" id="addButton" style="display: none;">+ Добавить</a>
    </div>

    <div class="container" id="mainContainer">
        <div class="header">
            <h1>Типы инвентаря</h1>
        </div>
        <ul class="records" id="type-records">
            <!-- Здесь будут динамически загруженные записи о типах -->
        </ul>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const typeRecordsList = document.getElementById('type-records');
    const showDeletedCheckbox = document.getElementById('show-deleted');
    const addButton = document.getElementById('addButton');
    const controls = document.getElementById('controls');
    let userPermissions = null; // Глобальная переменная для хранения прав пользователя
    let allTypes = [];

    if (typeof BX24 === 'undefined') {
        showError('Ошибка: BX24 не загружен. Откройте страницу через Bitrix24.');
        return;
    }

    BX24.init(function() {
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

        // Настройка интерфейса на основе прав
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
                    // Скрываем список типов
                    typeRecordsList.style.display = 'none';
                    typeRecordsList.innerHTML = '';
                }
            } else if (permissions === 'edit' || permissions === 'full') {
                // Показываем полный интерфейс
                if (accessDenied) accessDenied.style.display = 'none';
                if (mainContainer) {
                    mainContainer.style.display = 'block';
                    addButton.style.display = 'inline-block';
                    fetchTypes(); // Загружаем список типов
                }
            } else {
                // Неизвестные права
                showError('Неизвестный тип прав: ' + permissions);
                if (mainContainer) {
                    mainContainer.style.display = 'block';
                    controls.innerHTML = '<a class="back-button" href="https://predprod.reforma-sk.ru/local-pril/manual.php">НАЗАД</a>';
                    typeRecordsList.style.display = 'none';
                    typeRecordsList.innerHTML = '';
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
                typeRecordsList.style.display = 'none';
                typeRecordsList.innerHTML = '';
            }
        }

        // Функция получения списка типов
        function fetchTypes() {
            const showDeleted = showDeletedCheckbox.checked ? 'true' : 'false';
            BX24.callMethod(
                'custom.getiplusreferenceinventorytypes',
                { show_deleted: showDeleted, limit: 1000, offset: 0 },
                function(result) {
                    if (result.error()) {
                        console.error('Ошибка REST:', result.error());
                        typeRecordsList.innerHTML = '<li>Ошибка загрузки данных</li>';
                        return;
                    }
                    allTypes = result.data().result || [];
                    // Сортировка: сначала по убыванию ACTIVE, затем по возрастанию ID
                    allTypes.sort((a, b) => {
                        // Сортировка по ACTIVE (1 - активные, 0 - удалённые)
                        if (a.ACTIVE !== b.ACTIVE) {
                            return b.ACTIVE - a.ACTIVE; // Убывание по ACTIVE
                        }
                        // Если ACTIVE одинаковое, сортировка по ID по возрастанию
                        return a.ID - b.ID;
                    });
                    displayTypes();
                }
            );
        }

        // Функция отображения списка типов
        function displayTypes() {
            typeRecordsList.innerHTML = '';
            if (!allTypes.length) {
                typeRecordsList.innerHTML = '<li>Нет данных о типах инвентаря</li>';
                return;
            }
            allTypes.forEach(type => {
                const li = document.createElement('li');
                li.className = 'record-item';
                const isDeleted = (type.ACTIVE === '0');
                // Добавляем класс deleted для удалённых элементов
                if (isDeleted) {
                    li.classList.add('deleted');
                }
                // Убрали подпись "(Удалённый)"
                let innerHtml = `<span>${type.TYPE_NAME || 'Без названия'}</span>`;
                // Показываем кнопку удаления для прав edit и full, если запись не удалена
                if (!isDeleted && (userPermissions === 'edit' || userPermissions === 'full')) {
                    innerHtml += `<button class="delete-button" data-id="${type.ID}" data-name="${type.TYPE_NAME}">✖</button>`;
                }
                li.innerHTML = innerHtml;
                typeRecordsList.appendChild(li);
            });

            // Обработчики для кнопок удаления
            document.querySelectorAll('.delete-button').forEach(btn => {
                btn.addEventListener('click', function() {
                    const typeId = this.getAttribute('data-id');
                    const typeName = this.getAttribute('data-name');
                    if (confirm(`Действительно удалить "${typeName}"?`)) {
                        deleteType(typeId);
                    }
                });
            });
        }

        // Функция удаления типа
        function deleteType(typeId) {
            if (userPermissions === 'view') {
                alert('У вас нет прав на удаление');
                return;
            }
            BX24.callMethod(
                'custom.deleteiplusreferenceinventorytypes',
                { id: typeId },
                function(result) {
                    if (result.error()) {
                        alert('Ошибка удаления: ' + result.error());
                        return;
                    }
                    const data = result.data();
                    alert(data.message || 'Тип успешно удалён');
                    fetchTypes();
                }
            );
        }

        // Получение ID пользователя и настройка интерфейса
        let userId;
        if (BX24.user && typeof BX24.user.getId === 'function') {
            userId = BX24.user.getId();
            fetchToken().then(access_token => {
                getUserPermissions(userId, access_token).then(permissions => {
                    setupInterfaceBasedOnPermissions(permissions);
                }).catch(err => {
                    console.error('Ошибка получения прав:', err);
                    showError('Ошибка получения прав: ' + err);
                });
            }).catch(err => {
                console.error('Ошибка получения токена:', err);
                showError('Ошибка получения токена: ' + err);
            });
        } else {
            BX24.callMethod('user.current', {}, function(result) {
                if (result.error()) {
                    console.error('Ошибка получения текущего пользователя:', result.error());
                    showError('Ошибка получения пользователя: ' + result.error());
                    return;
                }
                userId = result.data().ID;
                fetchToken().then(access_token => {
                    getUserPermissions(userId, access_token).then(permissions => {
                        setupInterfaceBasedOnPermissions(permissions);
                    }).catch(err => {
                        console.error('Ошибка получения прав:', err);
                        showError('Ошибка получения прав: ' + err);
                    });
                }).catch(err => {
                    console.error('Ошибка получения токена:', err);
                    showError('Ошибка получения токена: ' + err);
                });
            });
        }

        // Переключение показа удалённых записей
        showDeletedCheckbox.addEventListener('change', () => {
            if (userPermissions === 'edit' || userPermissions === 'full') {
                fetchTypes();
            }
        });
    });
});
</script>
</body>
</html>