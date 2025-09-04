// /local-pril/init.js

document.addEventListener('DOMContentLoaded', function() {
    const isMobileApp = typeof BX !== 'undefined' && typeof BX24 !== 'undefined'; // Проверка мобильного приложения

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
        console.log('Мобильная версия: инициализируем BX24 для мобильного приложения');
        BX24.ready(function() {
            BX24.callMethod('app.info', {}, function(result) {
                if (result.error()) {
                    console.error('Ошибка проверки статуса приложения:', result.error());
                    document.getElementById('statusMessage').innerHTML = '<p style="color: red;">Ошибка: ' + result.error().ex + '</p>';
                    return;
                }

                console.log('Статус приложения:', result.data());
                BX24.getAuthToken(function(token) {
                    console.log('Получен токен для мобильной версии:', token);
                    if (!token) {
                        document.getElementById('statusMessage').innerHTML = '<p style="color: red;">Ошибка: не удалось получить токен авторизации</p>';
                        fetch('/local-pril/log.txt', { method: 'POST', body: 'Ошибка: токен не получен в мобильной версии' });
                        return;
                    }
                    fetchSqlTables(token); // Выполняем запрос с токеном
                });
            });
        });
    } else {
        console.log('Веб-версия: проверяем авторизацию через BX24');
        BX24.ready(function() {
            BX24.callMethod('user.current', {}, function(result) {
                console.log('Результат user.current:', result);
                if (result.error()) {
                    document.getElementById('statusMessage').innerHTML = '<p style="color: red;">Ошибка: пользователь не авторизован</p>';
                    fetch('/local-pril/log.txt', { method: 'POST', body: 'Ошибка: пользователь не авторизован в веб-версии' });
                } else {
                    fetchSqlTables(null); // Запрос к sql_tables.php без токена
                }
            });
        });
    }
});