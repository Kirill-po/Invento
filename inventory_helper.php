<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$recordsFile = $_SERVER['DOCUMENT_ROOT'] . '/local-pril/records.json';
file_put_contents("log.txt", "Вошло в pril.php \n", FILE_APPEND);

// Функция для чтения записей из файла
function getRecords() {
    global $recordsFile;
    if (!file_exists($recordsFile)) {
        file_put_contents($recordsFile, json_encode(['records' => [], 'groups' => []]));
    }
    return json_decode(file_get_contents($recordsFile), true);
}

// Функция для сохранения записей в файл
function saveRecords($data) {
    global $recordsFile;
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($recordsFile, $jsonData);
}

// Функция для добавления записи
function addRecord($id, $text) {
    $data = getRecords();
    $data['records'][$id] = [
        'id' => $id,
        'text_field' => $text
    ];
    saveRecords($data);
    return $id;
}

// Функция для обновления записи
function updateRecord($id, $text) {
    $data = getRecords();
    if (isset($data['records'][$id])) {
        $data['records'][$id]['text_field'] = $text;
        saveRecords($data);
    }
}

// Функция для удаления записи
function deleteRecord($id) {
    $data = getRecords();
    if (isset($data['records'][$id])) {
        unset($data['records'][$id]);
        // Удаляем запись из всех групп
        foreach ($data['groups'] as &$group) {
            $group['records'] = array_diff($group['records'], [$id]);
        }
        saveRecords($data);
    }
}

// Функция для создания группы
function createGroup($id, $name) {
    $data = getRecords();
    $data['groups'][$id] = [
        'id' => $id,
        'name' => $name,
        'records' => (object)[] // Записи группы теперь хранятся как объект
    ];
    saveRecords($data);
    return $id;
}

// Функция для добавления записи в группу
function addRecordToGroup($groupId, $recordId) {
    $data = getRecords();

    if (!isset($data['groups'][$groupId])) {
        return false;
    }

    if (!isset($data['records'][$recordId])) {
        return false;
    }

    // Добавляем запись в группу как объект
    $records = (array)$data['groups'][$groupId]['records'];
    $records[] = $recordId; // Добавляем запись в массив
    $data['groups'][$groupId]['records'] = (object)$records; // Преобразуем обратно в объект

    saveRecords($data);
    return $data['groups'][$groupId]; // Возвращаем обновленную группу
}

// Функция для удаления записи из группы
function removeRecordFromGroup($groupId, $recordId) {
    $data = getRecords();

    if (isset($data['groups'][$groupId])) {
        $records = (array)$data['groups'][$groupId]['records'];
        $records = array_diff($records, [$recordId]); // Удаляем запись
        $data['groups'][$groupId]['records'] = (object)$records; // Преобразуем обратно в объект
        saveRecords($data);
    }
}

// Функция для удаления группы
function deleteGroup($groupId) {
    $data = getRecords();
    if (isset($data['groups'][$groupId])) {
        unset($data['groups'][$groupId]);
        saveRecords($data);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    $action = $input['action'] ?? null;

    switch ($action) {
        case 'add':
            $id = $input['id'] ?? null;
            $text = $input['text'] ?? '';

            if (empty($text)) {
                echo json_encode(['error' => 'Текст записи не может быть пустым']);
                exit;
            }

            $id = addRecord($id, $text);
            echo json_encode(['status' => 'success', 'id' => $id]);
            break;

        case 'update':
            $id = $input['id'] ?? null;
            $text = $input['text'] ?? null;

            if (empty($id) || empty($text)) {
                echo json_encode(['error' => 'ID и текст записи обязательны']);
                exit;
            }

            updateRecord($id, $text);
            echo json_encode(['status' => 'success']);
            break;

        case 'delete':
            $id = $input['id'] ?? null;

            if (empty($id)) {
                echo json_encode(['error' => 'ID записи обязательна']);
                exit;
            }

            deleteRecord($id);
            echo json_encode(['status' => 'success']);
            break;

        case 'add_group':
            $id = $input['id'] ?? null;
            $name = $input['name'] ?? 'Новая группа';
            $records = $input['records'] ?? [];

            if (empty($id)) {
                echo json_encode(['error' => 'ID группы обязательно']);
                exit;
            }

            $id = createGroup($id, $name);
            //file_put_contents("log.txt", "Группа создалась: " . $id . "\n", FILE_APPEND);

            // Добавляем записи в группу (если есть)
            if (!empty($records) && is_array($records)) {
                //file_put_contents("log.txt", "Вошло в условие где добавляются записи \n", FILE_APPEND);
                foreach ($records as $recordId) {
                    addRecordToGroup($id, $recordId);
                }
            } 

            echo json_encode(['status' => 'success', 'id' => $id, 'records' => $records]);
            break;

            case 'add_to_group':
                $groupId = $input['groupId'] ?? null;
                $recordIds = $input['recordIds'] ?? [];
            
                if (empty($groupId) || empty($recordIds)) {
                    echo json_encode(['error' => 'ID группы и записи обязательны']);
                    exit;
                }
            
                $updatedGroup = null;
                foreach ($recordIds as $recordId) {
                    $updatedGroup = addRecordToGroup($groupId, $recordId);
                }
            
                if ($updatedGroup) {
                    echo json_encode(['status' => 'success', 'group' => $updatedGroup]);
                } else {
                    echo json_encode(['error' => 'Ошибка добавления записей в группу']);
                }
                break;
        case 'remove_from_group':
            $groupId = $input['groupId'] ?? null;
            $recordId = $input['recordId'] ?? null;

            if (empty($groupId) || empty($recordId)) {
                echo json_encode(['error' => 'ID группы и записи обязательны']);
                exit;
            }

            removeRecordFromGroup($groupId, $recordId);
            echo json_encode(['status' => 'success']);
            break;

        case 'delete_group':
            $groupId = $input['groupId'] ?? null;

            if (empty($groupId)) {
                echo json_encode(['error' => 'ID группы обязательно']);
                exit;
            }

            deleteGroup($groupId);
            echo json_encode(['status' => 'success']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = getRecords();
    echo json_encode($data);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
