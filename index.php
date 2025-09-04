<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

define('DATA_FILE', __DIR__ . '/records.json');
ini_set('display_errors', 1);
error_reporting(E_ALL);
function loadRecords()
{
    if (!file_exists(DATA_FILE)) {
        return [];
    }
    $data = file_get_contents(DATA_FILE);
    return json_decode($data, true) ?? [];
}

function saveRecords($records)
{
    file_put_contents(DATA_FILE, json_encode($records, JSON_PRETTY_PRINT));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && $_POST['action'] === 'add' && !empty($_POST['text'])) {
    $records = loadRecords();
    $newRecord = [
        'id' => uniqid(), 
        'text' => trim($_POST['text']),
    ];
    $records[] = $newRecord;
    saveRecords($records);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && $_POST['action'] === 'update' && !empty($_POST['id']) && !empty($_POST['text'])) {
    $records = loadRecords();
    foreach ($records as &$record) {
        if ($record['id'] === $_POST['id']) {
            $record['text'] = trim($_POST['text']);
            break;
        }
    }
    saveRecords($records);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && $_POST['action'] === 'delete' && !empty($_POST['id'])) {
    $records = loadRecords();
    $records = array_filter($records, function ($record) {
        return $record['id'] !== $_POST['id'];
    });
    saveRecords($records);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
$records = loadRecords();
?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление записями</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 10px;
            line-height: 1.5;
        }
        textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            font-size: 14px;
            box-sizing: border-box;
        }
        button {
            display: block;
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 5px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .record {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .record form {
            margin-bottom: 10px;
        }
        @media (max-width: 600px) {
            body {
                margin: 5px;
            }
            textarea {
                height: 80px;
                font-size: 12px;
            }
            button {
                font-size: 14px;
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <h1>Управление записями</h1>
    <form method="post">
        <textarea name="text" placeholder="Введите текст записи" required></textarea>
        <input type="hidden" name="action" value="add">
        <button type="submit">Добавить запись</button>
    </form>

    <div id="records">
        <?php foreach ($records as $record): ?>
            <div class="record">
                <form method="post">
                    <textarea name="text" required><?= htmlspecialchars($record['text']) ?></textarea>
                    <input type="hidden" name="id" value="<?= $record['id'] ?>">
                    <input type="hidden" name="action" value="update">
                    <button type="submit">Обновить</button>
                </form>
                <form method="post">
                    <input type="hidden" name="id" value="<?= $record['id'] ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit">Удалить</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>


