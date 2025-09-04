<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Справочник</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Подключаем Bitrix24 API -->
    <script src="https://api.bitrix24.com/api/v1/"></script>
    
    <!-- Подключаем стили -->
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <style>
        @font-face {
            font-family: 'Gilroy-Light';
            src: url('Gilroy-Light.otf') format('opentype');
            font-weight: normal;
            font-style: normal;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Gilroy-Light', sans-serif !important;
            color: rgb(29, 25, 84);
            background-color: #fff !important;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        /* Стили для кнопки "Назад" */
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
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

        /* Стили для контейнера */
        .container {
            width: 100% !important;
            max-width: 500px !important;
            text-align: center;
            display: none; /* Скрываем по умолчанию */
        }

        /* Стили для кнопок меню */
        .menu-button {
            display: block;
            width: 100%;
            max-width: 400px !important;
            margin: 10px auto !important;
            padding: 15px !important;
            background-color: #fff !important;
            border: 2px solid #e50045 !important;
            border-radius: 10px !important;
            font-size: clamp(16px, 2vw, 20px) !important;
            text-decoration: none !important;
            color: rgb(29, 25, 84) !important;
            font-weight: normal !important;
            transition: 0.3s !important;
        }

        .menu-button:hover {
            background-color: #d0003f !important;
            color: #fff !important;
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
            color: rgb(29, 25, 84);
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

    <!-- Основное содержимое -->
    <a class="back-button" id="backButton" href="pril.php" style="display: none;">НАЗАД</a>

    <div class="container" id="menuContainer">
        <a href="type_inventory.php" class="menu-button">Типы инвентаря</a>
        <a href="company_inventory.php" class="menu-button">Компании</a>
        <a href="location_inventory.php" class="menu-button">Местоположения</a>
        <a href="status_inventory.php" class="menu-button">Статусы инвентаря</a>
        <a href="operation_inventory.php" class="menu-button">Операции</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Проверяем наличие BX24
            if (typeof BX24 === 'undefined') {
                showError('Ошибка: BX24 не загружен. Откройте страницу через Bitrix24.');
                console.error('BX24 не загружен');
                return;
            }

            // Инициализация BX24
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
            // Запрашиваем токен
            fetchToken().then(access_token => {
                // Запрашиваем права пользователя
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
            const backButton = document.getElementById('backButton');
            const menuContainer = document.getElementById('menuContainer');

            // Скрываем лоадер
            if (loader) loader.style.display = 'none';

            if (permissions === 'view') {
                // Показываем сообщение "Доступ запрещён"
                if (accessDenied) accessDenied.style.display = 'block';
                if (backButton) backButton.style.display = 'inline-block'; // Кнопка "Назад" видима
                if (menuContainer) menuContainer.style.display = 'none';
            } else if (permissions === 'edit' || permissions === 'full') {
                // Показываем основное содержимое
                if (accessDenied) accessDenied.style.display = 'none';
                if (backButton) backButton.style.display = 'inline-block';
                if (menuContainer) menuContainer.style.display = 'block';
            } else {
                // На случай неизвестных прав
                showError('Неизвестный тип прав: ' + permissions);
                console.error('Неизвестный тип прав:', permissions);
                if (accessDenied) accessDenied.style.display = 'block';
                if (backButton) backButton.style.display = 'inline-block';
                if (menuContainer) menuContainer.style.display = 'none';
            }
        }

        function showError(message) {
            const loader = document.getElementById('loader');
            const accessDenied = document.getElementById('accessDenied');
            const backButton = document.getElementById('backButton');
            const menuContainer = document.getElementById('menuContainer');

            if (loader) loader.style.display = 'none';
            if (accessDenied) {
                accessDenied.textContent = message;
                accessDenied.style.display = 'block';
            }
            if (backButton) backButton.style.display = 'inline-block';
            if (menuContainer) menuContainer.style.display = 'none';
        }
    </script>
</body>
</html>