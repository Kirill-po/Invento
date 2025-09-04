<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Создание типа инвентаря</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Подключение Bitrix24 JS API -->
    <script src="https://api.bitrix24.com/api/v1/"></script>

    <style>
        @font-face {
            font-family: 'Gilroy-Light';
            src: url('Gilroy-Light.otf') format('opentype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'Gilroy-Light', sans-serif;
            color: rgb(29, 25, 84);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f4f7f9;
        }

        .container {
            width: 90%;
            max-width: 600px;
            padding: 20px;
            margin-top: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .back-button {
            background: none;
            border: 1px solid#e50045;
            color: #1D1954;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s, color 0.3s;
            text-decoration: none;
        }
        .back-button:hover {
            background-color:#d0003f;
            color: #1D1954;
        }

        .page-title {
            font-size: 24px;
            margin: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 16px;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .form-group input[type="text"].error {
            border-color: rgb(220, 0, 67);
            box-shadow: 0 0 5px rgba(220, 0, 67, 0.5);
        }

        .add-button-container {
            display: flex;
            justify-content: flex-end;
        }

        .add-button {
            background: none;
            border: 2px solid #e50045;
            color: #1D1954;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s, color 0.3s;
        }
        .add-button:hover {
            background-color: #d0003f;
            color: #1D1954;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="/local-pril/type_inventory.php" class="back-button">НАЗАД</a>
            <h1 class="page-title">Тип инвентаря: создание</h1>
        </div>

        <div class="form-group">
            <label for="field-value" id="field-label">Тип инвентаря:</label>
            <input type="text" id="field-value" name="field-value" placeholder="Введите название типа">
        </div>

        <div class="add-button-container">
            <button class="add-button" onclick="addRecord()">+</button>
        </div>
    </div>

    <script>
    function addRecord() {
        const fieldValueInput = document.getElementById('field-value');
        const fieldValue = fieldValueInput.value.trim();

        if (fieldValue === "") {
            fieldValueInput.classList.add('error');
            alert("Пожалуйста, заполните поле!");
            return;
        }
        fieldValueInput.classList.remove('error');

        // Проверяем наличие BX24
        if (typeof BX24 === 'undefined') {
            alert("Ошибка: страница не открыта через Bitrix24. Невозможно вызвать REST метод.");
            return;
        }

        BX24.init(function() {
            BX24.callMethod(
                'custom.addiplusreferenceinventorytypes',
                { name: fieldValue },
                function(result) {
                    if (result.error()) {
                        alert('Ошибка добавления: ' + result.error());
                        console.error('Ошибка добавления:', result.error());
                    } else {
                        const data = result.data();
                        alert(data.message || 'Тип успешно добавлен');
                        window.location.href = '/local-pril/type_inventory.php';
                    }
                }
            );
        });
    }
    </script>

    <script>
    // Дополнительная подгрузка шрифта (опционально)
    document.addEventListener('DOMContentLoaded', function() {
        const style = document.createElement('style');
        style.textContent = `body { font-family: 'Gilroy-Light', sans-serif; }`;
        document.head.appendChild(style);
    });
    </script>
</body>
</html>
