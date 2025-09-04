<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Добавить операцию</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bitrix24 JS API -->
  <script src="https://api.bitrix24.com/api/v1/"></script>
  <style>
    body {
      font-family: 'Gilroy-Light', sans-serif;
      color: rgb(29, 25, 84);
      background-color: #f0f0f0;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
      margin: 0;
      padding: 0;
    }
    .container {
      width: 95%;
      max-width: 600px;
      padding: 20px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
      margin-top: 80px;
      margin-bottom: 20px;
    }
    .form-group {
      margin-bottom: 15px;
    }
    .btn-submit, .btn-back {
      display: inline-block;
      color: rgb(220, 0, 67);
      text-decoration: none;
      font-size: 16px;
      border: 2px solid rgb(220, 0, 67);
      padding: 10px 15px;
      border-radius: 5px;
      transition: 0.3s;
      cursor: pointer;
    }
    .btn-submit:hover, .btn-back:hover {
      background-color: rgb(220, 0, 67);
      color: #fff;
    }
    .dropdown-menu {
      max-height: 200px;
      overflow-y: auto;
      border: 1px solid #ccc;
      padding: 5px;
      border-radius: 5px;
    }
    .form-select, .form-control {
      width: 100%;
      margin-bottom: 10px;
      padding: 8px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    .controls {
      position: fixed;
      top: 20px;
      left: 0;
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
      z-index: 1000;
    }
    .back-button, .add-button {
      display: inline-block;
      color: rgb(220, 0, 67);
      text-decoration: none;
      font-size: clamp(16px, 2vw, 20px);
      border: 2px solid rgb(220, 0, 67);
      padding: 10px 15px;
      border-radius: 5px;
      transition: 0.3s;
    }
    .back-button:hover, .add-button:hover {
      background-color: rgb(220, 0, 67);
      color: #fff;
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
    @media (max-width: 768px) {
      .controls {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 0;
      }
      .back-button, .add-button {
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 6px;
        min-width: 60px;
        text-align: center;
        margin: 0 5px;
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
  <div class="container">
    <h1>Добавить операцию</h1>
    <form id="add-operation-form">
      <div class="mb-3">
        <label for="direct-name" class="form-label">Прямое название операции</label>
        <input type="text" class="form-control" id="direct-name" required>
      </div>
      <div class="mb-3">
        <label for="reverse-name" class="form-label">Обратное название операции</label>
        <input type="text" class="form-control" id="reverse-name" required>
      </div>
      <div class="mb-3">
        <label for="after-status" class="form-label">Статус после операции</label>
        <select class="form-select" id="after-status" required>
          <option value="">Выберите статус</option>
          <!-- Dynamically populated -->
        </select>
      </div>
      <div class="mb-3">
        <label>Исходные статусы инвентаря:</label>
        <div class="dropdown">
          <button class="btn btn-secondary dropdown-toggle" type="button" id="initialStatusDropdownButton" data-bs-toggle="dropdown" aria-expanded="false">
            Выберите статусы
          </button>
          <ul class="dropdown-menu" aria-labelledby="initialStatusDropdownButton" id="initialStatusDropdownMenu">
            <!-- Dynamically populated -->
          </ul>
        </div>
        <input type="hidden" id="initial_statuses_hidden" name="initial_statuses">
      </div>
      <div class="mb-3">
        <label class="form-label">Настройки видимости</label>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="is-available-to-all" checked>
          <label class="form-check-label" for="is-available-to-all">Доступно всем</label>
        </div>
      </div>
      <div class="mb-3" id="allowed-users-group" style="display: none;">
        <label class="form-label">Разрешённые пользователи</label>
        <div class="checkbox-list" id="allowed-users-list">
          <!-- Dynamically populated with checkboxes -->
        </div>
      </div>
      <!-- Поле для загрузки файла шаблона -->
      <div class="mb-3">
        <label for="template-file" class="form-label">Файл шаблона печатной формы</label>
        <input type="file" class="form-control" id="template-file">
      </div>
      <button type="button" class="btn btn-primary" id="save-new">Сохранить</button>
    </form>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      let statusOptions = [];
      let userOptions = [];

      if (typeof BX24 === 'undefined') {
        alert('BX24 не загружен. Откройте страницу через Bitrix24.');
        return;
      }

      BX24.init(function() {
        // Функция загрузки статусов
        function fetchStatuses() {
          return new Promise((resolve, reject) => {
            BX24.callMethod(
              'custom.getiplusreferencestatus',
              { show_deleted: 'false', limit: 1000, offset: 0 },
              function(result) {
                if (result.error()) {
                  reject(result.error());
                } else {
                  statusOptions = result.data().result || [];
                  resolve();
                }
              }
            );
          });
        }

        // Функция загрузки пользователей
        function fetchUsers() {
          return new Promise((resolve, reject) => {
            BX24.callMethod(
              'user.get',
              {},
              function(result) {
                if (result.error()) {
                  reject(result.error());
                } else {
                  userOptions = result.data();
                  resolve();
                }
              }
            );
          });
        }

        // Заполнение селекторов и списка чекбоксов
        function populateDropdowns() {
          const afterStatusSelect = document.getElementById('after-status');
          afterStatusSelect.innerHTML = '<option value="">Выберите статус</option>';
          statusOptions.forEach(status => {
            const option = document.createElement('option');
            option.value = status.ID;
            option.textContent = status.STATUS_NAME;
            afterStatusSelect.appendChild(option);
          });

          const initialStatusDropdownMenu = document.getElementById('initialStatusDropdownMenu');
          initialStatusDropdownMenu.innerHTML = '';
          statusOptions.forEach(status => {
            const li = document.createElement('li');
            li.innerHTML = `<div class="form-check">
                              <input class="form-check-input initial-status-checkbox" type="checkbox" value="${status.ID}" id="status_${status.ID}">
                              <label class="form-check-label" for="status_${status.ID}">${status.STATUS_NAME}</label>
                            </div>`;
            initialStatusDropdownMenu.appendChild(li);
          });

          const allowedUsersList = document.getElementById('allowed-users-list');
          allowedUsersList.innerHTML = '';
          userOptions.forEach(user => {
            const div = document.createElement('div');
            div.className = 'form-check';
            div.innerHTML = `<input class="form-check-input allowed-user-checkbox" type="checkbox" value="${user.ID}" id="user_${user.ID}">
                             <label class="form-check-label" for="user_${user.ID}">${user.NAME} ${user.LAST_NAME}</label>`;
            allowedUsersList.appendChild(div);
          });
        }

        // Обработчик изменения чекбоксов исходных статусов
        document.addEventListener('change', function(e) {
          if (e.target.classList.contains('initial-status-checkbox')) {
            const selected = [];
            const selectedNames = [];
            document.querySelectorAll('.initial-status-checkbox:checked').forEach(checkbox => {
              selected.push(checkbox.value);
              selectedNames.push(checkbox.parentElement.querySelector('label').innerText);
            });
            document.getElementById('initial_statuses_hidden').value = JSON.stringify(selected);
            document.getElementById('initialStatusDropdownButton').innerText = selectedNames.length ? 'Выбрано: ' + selectedNames.join(', ') : 'Выберите статусы';
          }
        });

        // Переключение видимости блока разрешённых пользователей
        document.getElementById('is-available-to-all').addEventListener('change', function() {
          document.getElementById('allowed-users-group').style.display = this.checked ? 'none' : 'block';
        });

        // Сохранение новой операции с файлом шаблона
        document.getElementById('save-new').addEventListener('click', function() {
    const directName = document.getElementById('direct-name').value.trim();
    const reverseName = document.getElementById('reverse-name').value.trim();
    const afterStatus = document.getElementById('after-status').value;
    const initialStatusesRaw = document.getElementById('initial_statuses_hidden').value || '[]';
    const isAvailableToAll = document.getElementById('is-available-to-all').checked;
    const allowedUsers = Array.from(document.querySelectorAll('.allowed-user-checkbox:checked')).map(checkbox => checkbox.value);
    const fileInput = document.getElementById('template-file');

    let initialStatuses;
    try {
        initialStatuses = JSON.parse(initialStatusesRaw);
        if (!Array.isArray(initialStatuses)) throw new Error('initial_statuses должен быть массивом');
    } catch (e) {
        alert('Ошибка: исходные статусы должны быть валидным JSON массивом');
        return;
    }

    if (!directName || !reverseName || !afterStatus || initialStatuses.length === 0) {
        alert('Ошибка: Все поля обязательны для заполнения');
        return;
    }
    if (!isAvailableToAll && allowedUsers.length === 0) {
        alert('Ошибка: Выберите хотя бы одного пользователя, если операция не доступна всем');
        return;
    }

    const sendData = {
        direct_name: directName,
        reverse_name: reverseName,
        after_status: parseInt(afterStatus),
        initial_statuses: JSON.stringify(initialStatuses),
        is_available_to_all: isAvailableToAll ? '1' : '0',
        allowed_users: isAvailableToAll ? '' : allowedUsers.join(',')
    };

    if (fileInput.files && fileInput.files.length > 0) {
        const file = fileInput.files[0];
        if (file.name.split('.').pop().toLowerCase() !== 'docx') {
            alert('Ошибка: Файл должен быть в формате .docx');
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            sendData.template_base64 = e.target.result.split(',')[1]; // Чистый Base64 без префикса
            BX24.callMethod(
                'custom.addiplusreferenceoperations',
                sendData,
                function(result) {
                    if (result.error()) {
                        alert('Ошибка создания: ' + result.error());
                    } else {
                        alert('Операция успешно создана');
                        window.location.href = '/local-pril/operation_inventory.php';
                    }
                }
            );
        };
        reader.readAsDataURL(file);
    } else {
        BX24.callMethod(
            'custom.addiplusreferenceoperations',
            sendData,
            function(result) {
                if (result.error()) {
                    alert('Ошибка создания: ' + result.error());
                } else {
                    alert('Операция успешно создана');
                    window.location.href = '/local-pril/operation_inventory.php';
                }
            }
        );
    }
});

        // Инициализация: загружаем статусы и пользователей, затем заполняем селекторы
        Promise.all([fetchStatuses(), fetchUsers()])
          .then(() => populateDropdowns())
          .catch(err => console.error('Ошибка:', err));
      });
    });
  </script>
</body>
</html>