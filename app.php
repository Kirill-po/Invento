<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная страница</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Подключаем единый CSS -->
    <link rel="stylesheet" href="styles.css">

    <!-- Фиксируем Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <!-- Основной контейнер -->
    <div class="container">
        <div class="row g-2 justify-content-center">
            <div class="col-4 d-flex">
                <a href="inventory.php" class="button">Инвентарь</a>
            </div>
            <div class="col-4 d-flex">
                <a href="manual.php" class="button">Справочники</a>
            </div>
            <div class="col-4 d-flex">
                <a href="config.php" class="button">Пользователи</a>
            </div>
        </div>
        <div id="statusMessage"></div> <!-- Контейнер для сообщений -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof BX24 === 'undefined') {
                document.getElementById('statusMessage').innerHTML = '<p style="color: red;">BX24 не загружен. Убедитесь, что приложение установлено в Bitrix24.</p>';
                return;
            }

            BX24.ready(function() {
                const isMobileApp = typeof BX !== 'undefined' && typeof BX24 !== 'undefined';

                function fetchSqlTables(accessToken = null) {
                    const headers = { 'Accept': 'application/json' };
                    if (accessToken) {
                        headers['Authorization'] = `Bearer ${accessToken}`;
                    }

                    fetch('/local-pril/sql_tables.php', {
                        method: 'GET',
                        credentials: 'omit',
                        headers: headers
                    })
                    .then(response => {
                        console.log('Ответ от sql_tables.php:', response);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('JSON ответ от sql_tables.php:', data);
                        const statusDiv = document.getElementById('statusMessage');
                        if (data.status === 'success') {
                            statusDiv.innerHTML = `<p style="color: green;">${data.message}</p>`;
                            fetch('/local-pril/log.txt', { method: 'POST', body: 'Успешно выполнено: ' + JSON.stringify(data) });
                        } else {
                            statusDiv.innerHTML = `<p style="color: red;">Ошибка: ${data.message || 'Неизвестная ошибка'}</p>`;
                            fetch('/local-pril/log.txt', { method: 'POST', body: 'Ошибка: ' + JSON.stringify(data) });
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка при выполнении sql_tables.php:', error);
                        document.getElementById('statusMessage').innerHTML = 
                            `<p style="color: red;">Ошибка: не удалось выполнить запрос (${error.message})</p>`;
                        fetch('/local-pril/log.txt', { method: 'POST', body: 'Ошибка сети: ' + error.message });
                    });
                }

                if (isMobileApp) {
                    console.log('Мобильная версия: получаем токен через BX24.getAuthToken()');
                    BX24.getAuthToken(function(token) {
                        console.log('Получен токен для мобильной версии:', token);
                        if (!token) {
                            document.getElementById('statusMessage').innerHTML = '<p style="color: red;">Ошибка: не удалось получить токен авторизации</p>';
                            fetch('/local-pril/log.txt', { method: 'POST', body: 'Ошибка: токен не получен в мобильной версии' });
                            return;
                        }
                        fetchSqlTables(token); // Выполняем запрос с токеном
                    });
                } else {
                    console.log('Веб-версия: проверяем авторизацию через BX24');
                    BX24.callMethod('user.current', {}, function(result) {
                        console.log('Результат user.current:', result);
                        if (result.error()) {
                            document.getElementById('statusMessage').innerHTML = '<p style="color: red;">Ошибка: пользователь не авторизован</p>';
                            fetch('/local-pril/log.txt', { method: 'POST', body: 'Ошибка: пользователь не авторизован в веб-версии' });
                        } else {
                            fetchSqlTables(null); // Запрос к sql_tables.php без токена
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>