<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <style>
        @font-face {
            font-family: 'Gilroy-Light';
            src: url('Gilroy-Light.otf') format('opentype');
            font-weight: normal;
            font-style: normal;
        }

        /* Общие стили */
        body {
            font-family: 'Gilroy-Light', sans-serif;
            color: rgb(29, 25, 84);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f8f8;
            margin: 0;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            width: 100%;
            max-width: 800px;
        }

        .button {
            width: 200px;
            height: 200px;
            background-color: white;
            border: 3px solid rgb(220, 0, 67);
            border-radius: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            color: rgb(29, 25, 84);
        }

        .button:hover {
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }
        @media (max-width: 768px) {
            .button {
                width: 90%;
                height: auto;
                padding: 40px;
                font-size: 20px;
            }
        }

    </style>
</head>
<body>

    <div class="container">
        <a href="inventory.php" class="button">Инвентарь</a>
        <a href="manual.php" class="button">Справочники</a>
        <a href="config.php" class="button">Пользователи</a>
    </div>

</body>
</html>