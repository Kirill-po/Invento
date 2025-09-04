<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная страница</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="styles.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://api.bitrix24.com/api/v1/"></script>
    <style>
        html, body, iframe {
            padding-bottom: 0;
            margin: 0 !important;
            padding: 0 !important;
            overflow: auto !important;
        }

        .container,
        .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
            height: auto !important;
            min-height: 0 !important;
            box-sizing: border-box !important;
        }

        .button {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 10px 16px;
            font-size: 14px;
            text-decoration: none;
            border-radius: 4px;
            color: #fff;
            background-color: #e50045;
            cursor: pointer;
            text-align: center;
        }
        
        #openFilterModal {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 15px 0;
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            background-color: #e50045;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
        }
        
        .btn-amaranth-outline {
            display: inline-block;
            width: 100%;
            padding: 15px 0;
            font-size: 16px;
            font-weight: bold;
            color: #e50045;
            background-color: #fff;
            border: 2px solid #e50045;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
        }
        @media screen and (max-width: 768px) { 
            html, body {
                height: 719px !important; 
                overflow: visible !important;
            }

            .workarea {
                min-height: 100vh !important;
                height: auto !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row g-2 justify-content-center my-3">
            <div class="col-4 d-flex">
                <a href="inventory.php" class="button w-100 text-center">Инвентарь</a>
            </div>
            <div class="col-4 d-flex">
                <a href="manual.php" class="button w-100 text-center">Наименования</a>
            </div>
            <div class="col-4 d-flex">
                <a href="config.php" class="button w-100 text-center">Пользователи</a>
            </div>
        </div>
        <!-- Новая строка для кнопок "Задания" и "Инвентаризации" -->
        <div class="row g-2 justify-content-center my-3">
            <div class="col-4 d-flex">
                <a href="#" id="tasks-button" class="button w-100 text-center">Задания</a>
            </div>
            <div class="col-4 d-flex">
                <a href="inventorying.php" class="button w-100 text-center">Инвентаризации</a>
            </div>
            <div class="col-4 d-flex" id="history-operations-col" style="display: none;">
                <a href="history_operation_completed.php" id="history-operations-button" class="button w-100 text-center">История операций</a>
            </div>
        </div>
        <div class="row justify-content-center my-3">
            
        </div>
    </div>
    <div id="bx24-status" style="padding: 10px; background: #f0f0f0; border: 1px solid #ccc; margin: 20px;">
        <strong>Статус BX24:</strong> <span id="bx24-output">Проверяем...</span>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Функция для получения прав пользователя
        function getUserPermissions(userId) {
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
                    }
                );
            });
        }

        // Логика после загрузки страницы
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof BX24 === 'undefined') {
                document.getElementById('bx24-output').innerHTML =
                    '<p class="text-danger">BX24 НЕ загружен! Убедитесь, что страница открыта через Bitrix24.</p>';
                return;
            }
            BX24.init(function() {
                // Проверка статуса приложения
                BX24.callMethod('app.info', {}, function(result) {
                    if (result.error()) {
                        document.getElementById('bx24-output').innerHTML =
                            '<p class="text-danger">Ошибка: приложение не установлено</p>';
                    } else {
                        document.getElementById('bx24-output').innerHTML =
                            '<p class="text-success">Приложение установлено</p>';
                    }
                });

                // Получение текущего пользователя и его прав
                BX24.callMethod('user.current', {}, function(result) {
                    if (result.error()) {
                        console.error('Ошибка получения текущего пользователя:', result.error());
                        return;
                    }
                    const userId = result.data().ID;
                    getUserPermissions(userId).then(permission => {
                        const tasksButton = document.getElementById('tasks-button');
                        const historyOperationsCol = document.getElementById('history-operations-col');
                        
                        tasksButton.textContent = 'Задания'; // Всегда "Задания" для всех пользователей

                        if (permission === 'full') {
                            // Для пользователей с правами full
                            tasksButton.href = 'history_operation.php'; // Ссылка на страницу для full
                            historyOperationsCol.style.display = 'flex'; // Показываем кнопку "История операций"
                        } else {
                            // Для пользователей с другими правами
                            tasksButton.href = 'history_operation_user.php'; // Ссылка на пользовательскую версию
                            historyOperationsCol.style.display = 'none'; // Скрываем кнопку "История операций"
                        }
                    }).catch(err => {
                        console.error('Ошибка получения прав:', err);
                        // По умолчанию для пользователей без прав
                        document.getElementById('tasks-button').textContent = 'Задания';
                        document.getElementById('tasks-button').href = 'history_operation_user.php';
                        document.getElementById('history-operations-col').style.display = 'none';
                    });
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof BX24 !== 'undefined') {
                BX24.init(function() {
                    // При первой загрузке подгоняем высоту
                    BX24.fitWindow();

                    // Также подгоняем при изменении размеров
                    window.addEventListener('resize', function() {
                        BX24.fitWindow();
                    });
                });
            }
        });
    </script>
</body>
</html>