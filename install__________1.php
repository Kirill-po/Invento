<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="style.css">
    <title>Тестовое приложение для записей</title>
    <style>
		@media (max-width: 600px) {
			.record {
				flex-direction: row; /* Убедимся, что элементы идут в строку */
				align-items: center; /* Выравниваем элементы по центру */
			}

			.record .arrow {
				margin-left: 10px; /* Добавляем отступ между текстом и стрелкой */
			}
		}

        @font-face {
            font-family: 'Gilroy-Light';
            src: url('Gilroy-Light.otf') format('opentype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'Gilroy-Light', Arial, sans-serif;
            color: rgb(29, 25, 84); /* Цвет текста R29 G25 B84 */
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        h1 {
            text-align: center;
            color: rgb(29, 25, 84);
        }

        .add-button {
            background-color: rgb(220, 0, 67); /* Цвет фона кнопок R220 G0 B67 */
            color: white;
            border: none;
            padding: 15px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Тень для кнопок */
            margin: 5px;
            width: 100px; /* Фиксированный размер кнопок */
            text-align: center;
        }

        .record {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border: 1px solid #ccc;
            margin: 5px 0;
            cursor: pointer;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Тень для записей */
        }

        .record label {
            flex-grow: 1;
            margin-right: 10px;
            cursor: pointer;
        }

        .record .arrow {
            font-size: 20px;
            cursor: pointer;
        }

        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            z-index: 1001;
        }

        .popup h2 {
            color: rgb(29, 25, 84);
        }

        .popup textarea {
			width: calc(100% - 20px); /* Учитываем отступы */
			margin: 0 10px 10px 10px; /* Отступы слева и справа */
			padding: 10px;
			border: 1px solid #ccc;
			border-radius: 5px;
		}

        .popup button {
            background-color: rgb(220, 0, 67);
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin-right: 10px;
        }

        .popup button:hover {
            background-color: rgb(200, 0, 60);
        }

        .groups-container {
            margin-top: 20px;
        }

        .group {
			display: flex;
			flex-direction: column; /* Элементы в столбик */
			align-items: center; /* Выравниваем по центру */
			padding: 10px;
			border: 1px solid #ccc;
			margin: 5px 0;
			background-color: white;
			border-radius: 5px;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		}
		
		.group p {
			margin: 0 0 10px 0; /* Отступ снизу для названия группы */
		}
        .group button {
			margin: 5px 0; /* Отступы между кнопками */
            background-color: rgb(220, 0, 67);
            color: white;
            border: none;
            padding: 5px 10px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 5px;
        }
		.group-buttons button, .add-records-button {
			background-color: rgb(220, 0, 67);
			color: white;
			border: none;
			padding: 10px 20px;
			font-size: 16px;
			cursor: pointer;
			border-radius: 5px;
			margin: 10px 0;
		}
		
		.group-buttons button:hover, .add-records-button:hover {
			background-color: rgb(200, 0, 60);
		}
    </style>
    <a class="config-button" onclick="window.location.href='config.php'">&#128736;</a>
</head>
<body>
    <h1>Тестовое приложение для записей</h1>

    <!-- Кнопки, которые зависят от прав пользователя -->
    <button id="addButton" class="add-button">+</button>
    <button id="cameraButton" class="add-button">Камера</button>
    <button id="addButtonGroup" class="add-button">Г+</button>

    <!-- Попап для добавления новой записи -->
    <div class="popup-overlay" id="popuptonew"></div>
    <div class="popup" id="addnewpopup">
        <h2>Добавление новой записи</h2>
        <textarea name="addnew" id="addnew"></textarea>
        <button onclick="addNewRecord()">Добавить</button>
        <button onclick="closeAddNewPopup()">Закрыть</button>
    </div>

    <!-- Контейнер для групп -->
    <div id="groups" class="groups-container"></div>

    <!-- Добавление новой группы -->
    <div class="popup-overlay" id="popuptonew"></div>
    <div class="popup" id="addnewpopupgroup">
        <h2>Добавление новой группы</h2>
        <input type="text" id="addnewGroup" placeholder="Название группы">
        <h3>Выберите записи для группы:</h3>
        <div id="groupRecords" class="records-selection"></div>
        <button onclick="addNewGroup()">Добавить</button>
        <button onclick="closeAddNewGroupPopup()">Закрыть</button>
    </div>

    <!-- Попап для детального просмотра группы -->
    <div class="popup-overlay" id="groupPopupOverlay"></div>
    <div class="popup" id="groupPopup">
        <h2>Детальный просмотр группы</h2>
        <div id="groupRecordss"></div>
        <div id="newRecordsForGroup"></div>
        <div class="group-buttons">
            <button onclick="generateGroupQRCode()">Сгенерировать QR-код</button>
        </div>
        <canvas id="qrCanvas"></canvas>
        <button onclick="closeGroupPopup()">Закрыть</button>
    </div>

    <!-- Список записей -->
    <div id="records" class="records-on"></div>

    <!-- Попап для камеры -->
    <div class="popup-overlay" id="cameraPopupOverlay"></div>
    <div class="popup" id="cameraPopup">
        <h2>Камера</h2>
        <video id="video" autoplay></video>
        <button id="start-camera">Запустить камеру</button>
        <button id="switch-camera" disabled>Сменить камеру</button>
        <button id="capture-photo" disabled>Сделать фото</button>
        <h2>Превью фото:</h2>
        <img id="photo-preview" src="" alt="Превью фото">
        <button onclick="closeCameraPopup()">Закрыть</button>
    </div>

    <!-- Попап для редактирования записи -->
    <div class="popup-overlay" id="recordPopupOverlay"></div>
    <div class="popup" id="recordPopup">
        <h2>Редактирование записи</h2>
        <textarea id="editRecordText"></textarea>
        <div class="record-buttons">
            <button onclick="updateRecord()">Сохранить</button>
            <button onclick="deleteRecord()">Удалить</button>
            <button onclick="generateQRCode()">Сгенерировать QR-код</button>
            <button onclick="generatePdf()">Скачать Word</button>
        </div>
        <div class="qr-code" id="popupQrCode"></div>
        <button onclick="closeRecordPopup()">Закрыть</button>
    </div>

    <canvas id="canvas" style="display: none;"></canvas>
    <div id="qr-output"></div>

    <!-- Подключаем необходимые скрипты -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    <script src="https://api.bitrix24.com/api/v1/"></script>
    <script>
	document.addEventListener("DOMContentLoaded", function () {
		fetch("/local-pril/sql_tables.php")
			.then(response => response.json())
				//.then(data => console.log(data.message))
			.catch(error => console.error("Ошибка при создании таблиц:", error));
	});

        BX24.init(app);

        function app() {
            const initDate = BX24.getAuth();
            console.log("ititDate: ", initDate);
        }

        async function getCurrentUserId() {
            try {
                let response = await fetch('/local-pril/UserId.php');
                let data = await response.json();
                if (data.error) {
                    console.error(data.error);
                    return null;
                }
                return data.user_id;
            } catch (error) {
                console.error('Ошибка получения ID пользователя:', error);
                return null;
            }
        }

        const apiUrl = '/local-pril/pril.php';
        const restApiUrl = 'rest_api_user.php';
        let currentRecordId = null;
        let currentUserId = null; // ID текущего пользователя

        async function checkPermissions() {
            currentUserId = await getCurrentUserId();
            if (!currentUserId) return;

            fetch(`${restApiUrl}?action=get_permissions&user_id=${currentUserId}`)
                .then(response => response.json())
                .then(data => {
                    const permission = data.permission;
                    // Скрываем/отображаем элементы в зависимости от прав
                    if (permission === 'view') {
                        document.querySelectorAll('.add-button, .edit-button, .delete-button, .config-button')
                            .forEach(button => {
                                button.style.display = 'none';
                            });
                    } else if (permission === 'edit') {
                        document.querySelectorAll('.config-button')
                            .forEach(button => {
                                button.style.display = 'none';
                            });
                    }
                    // Если права "full", все кнопки доступны
                })
                .catch(error => console.error('Ошибка загрузки прав пользователя:', error));
        }

        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                checkPermissions();
                loadRecords();
            }, 0);
        });

        // -----------------------------------------------------
        //      РАБОТА С ГРУППАМИ (добавление, удаление и т.д.)
        // -----------------------------------------------------

        function loadRecordsForSelection() {
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    const recordsContainer = document.getElementById('groupRecords');
                    recordsContainer.innerHTML = '';
                    Object.keys(data.records).forEach(key => {
                        const record = data.records[key];
                        const recordDiv = document.createElement('div');
                        recordDiv.className = 'record';
                        recordDiv.innerHTML = `
                            <input type="checkbox" id="record_${record.id}" value="${record.id}">
                            <label for="record_${record.id}">${record.text_field}</label>
                        `;
                        recordsContainer.appendChild(recordDiv);
                    });
                })
                .catch(error => console.error('Ошибка загрузки записей:', error));
        }

        function addNewGroup() {
            const groupName = document.getElementById('addnewGroup').value.trim();
            if (!groupName) return;
            const idgroup = Date.now().toString();
            const selectedRecords = Array.from(document.querySelectorAll('#groupRecords input:checked'))
                .map(checkbox => checkbox.value);

            fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'add_group',
                    id: idgroup,
                    name: groupName,
                    records: selectedRecords
                })
            })
                .then(response => response.text())
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        console.log("Ответ JSON:", data);
                        loadGroups();
                        closeAddNewGroupPopup();
                    } catch (error) {
                        console.error("Ошибка парсинга JSON:", error, "Ответ сервера:", text);
                    }
                })
                .catch(error => console.error('Ошибка добавления группы:', error));

            document.getElementById('addnewGroup').value = '';
        }

        function loadGroups() {
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    const groupsContainer = document.getElementById('groups');
                    groupsContainer.innerHTML = '';
                    Object.keys(data.groups).forEach(groupId => {
                        const group = data.groups[groupId];
                        const groupDiv = document.createElement('div');
                        groupDiv.className = 'group';
                        groupDiv.innerHTML = `
                            <p>${group.name}</p>
                            <button onclick="openGroupPopup('${groupId}')">Открыть</button>
                            <button class="delete-button" onclick="deleteGroup('${groupId}')">Удалить</button>
                        `;
                        groupsContainer.appendChild(groupDiv);
                    });
                })
                .catch(error => console.error('Ошибка загрузки групп:', error));
        }

        function openAddGroupPopup() {
            document.getElementById('popuptonew').style.display = 'block';
            document.getElementById('addnewpopupgroup').style.display = 'block';
            document.getElementById('addnew').value = '';
            loadRecordsForSelection();
        }

        function closeAddNewGroupPopup() {
            document.getElementById('popuptonew').style.display = 'none';
            document.getElementById('addnewpopupgroup').style.display = 'none';
            document.getElementById('addnew').value = '';
        }

        document.getElementById('addButtonGroup').addEventListener('click', openAddGroupPopup);

        function addRecordsToGroup(groupId) {
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    const recordsContainer = document.getElementById('newRecordsForGroup');
                    recordsContainer.innerHTML = '';
                    Object.keys(data.records).forEach(key => {
                        const record = data.records[key];
                        const recordDiv = document.createElement('div');
                        recordDiv.className = 'record';
                        recordDiv.innerHTML = `
                            <input type="checkbox" id="record_${record.id}" value="${record.id}">
                            <label for="record_${record.id}">${record.text_field}</label>
                        `;
                        recordsContainer.appendChild(recordDiv);
                    });

                    const addButton = document.createElement('button');
                    addButton.textContent = 'Добавить выбранные записи';
                    addButton.onclick = () => {
                        const selectedRecords = Array.from(document.querySelectorAll('#newRecordsForGroup input:checked'))
                            .map(checkbox => checkbox.value);

                        fetch(apiUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                action: 'add_to_group',
                                groupId,
                                recordIds: selectedRecords
                            })
                        })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'success') {
                                openGroupPopup(groupId);
                                resetGroupPopup();
                            } else {
                                console.error('Ошибка добавления записей в группу:', response.error);
                            }
                        })
                        .catch(error => console.error('Ошибка добавления записей в группу:', error));
                    };
                    recordsContainer.appendChild(addButton);
                })
                .catch(error => console.error('Ошибка загрузки записей:', error));
        }

        function resetGroupPopup() {
            const recordsContainer = document.getElementById('newRecordsForGroup');
            if (recordsContainer) {
                recordsContainer.innerHTML = '';
            }
        }

        function openGroupPopup(groupId) {
            resetGroupPopup();
            document.getElementById('qrCanvas').innerHTML = '';
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    const group = data.groups[groupId];
                    const groupRecordsContainer = document.getElementById('groupRecordss');
                    groupRecordsContainer.innerHTML = '';

                    const groupName = document.createElement('h3');
                    groupName.textContent = group.name;
                    groupRecordsContainer.appendChild(groupName);

                    if (group.records && Object.keys(group.records).length > 0) {
                        Object.values(group.records).forEach(recordId => {
                            const record = data.records[recordId];
                            if (record) {
                                const recordDiv = document.createElement('div');
                                recordDiv.className = 'record';
                                recordDiv.innerHTML = `
                                    <label data-id="${record.id}" readonly>${record.text_field}</label>
                                    <span class="arrow" onclick="openRecordPopup('${record.id}', '${record.text_field}')">→</span>
                                    <span class="delete-icon delete-button" onclick="removeRecordFromGroup('${groupId}', '${record.id}')">🗑️</span>
                                `;
                                groupRecordsContainer.appendChild(recordDiv);
                            }
                        });
                    } else {
                        groupRecordsContainer.innerHTML = '<p>В этой группе нет записей.</p>';
                    }

                    const addRecordsButton = document.createElement('button');
                    addRecordsButton.textContent = 'Добавить записи в группу';
                    addRecordsButton.classList.add('add-records-button');
                    addRecordsButton.onclick = () => addRecordsToGroup(groupId);
                    groupRecordsContainer.appendChild(addRecordsButton);

                    document.getElementById('groupPopup').setAttribute('data-group-id', groupId);
                    document.getElementById('groupPopupOverlay').style.display = 'block';
                    document.getElementById('groupPopup').style.display = 'block';
                })
                .catch(error => console.error('Ошибка загрузки группы:', error));
        }

        function closeGroupPopup() {
            document.getElementById('groupPopupOverlay').style.display = 'none';
            document.getElementById('groupPopup').style.display = 'none';
            const qrCanvas = document.getElementById('qrCanvas');
            if (qrCanvas) {
                const context = qrCanvas.getContext('2d');
                context.clearRect(0, 0, qrCanvas.width, qrCanvas.height);
            }
        }

        function deleteGroup(groupId) {
            fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_group', groupId }),
            })
                .then(response => response.json())
                .then(() => {
                    loadGroups();
                })
                .catch(error => console.error('Ошибка:', error));
        }

        function removeRecordFromGroup(groupId, recordId) {
            fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'remove_from_group', groupId, recordId }),
            })
                .then(response => response.json())
                .then(() => {
                    openGroupPopup(groupId);
                })
                .catch(error => console.error('Ошибка:', error));
        }

        function generateGroupQRCode() {
            const groupId = document.getElementById('groupPopup').getAttribute('data-group-id');
            if (!groupId) {
                console.error("Ошибка: groupId не найден!");
                return;
            }
            const qrCodeData = `https://predprod.reforma-sk.ru/marketplace/app/1/?groupId=${groupId}`;
            QRCode.toCanvas(document.getElementById('qrCanvas'), qrCodeData, { width: 200 }, (error) => {
                if (error) console.error('Ошибка генерации QR-кода:', error);
            });
        }

        // -----------------------------------------------------
        //             РАБОТА С ЗАПИСЯМИ (CRUD)
        // -----------------------------------------------------
        function getQueryParam(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        function loadRecords() {
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (typeof data !== 'object' || data === null) {
                        console.error('Ошибка: Ожидается объект записей, получено:', data);
                        return;
                    }
                    const recordsContainer = document.getElementById('records');
                    recordsContainer.innerHTML = '';
                    const recordIdFromUrl = getQueryParam('id');

                    Object.keys(data.records).forEach(key => {
                        const record = data.records[key];
                        const recordDiv = document.createElement('div');
                        recordDiv.className = 'record';
                        recordDiv.innerHTML = `
                            <label data-id="${record.id}" readonly>${record.text || record.text_field}</label>
                            <span class="arrow" onclick="openRecordPopup('${record.id}', '${record.text || record.text_field}')">→</span>
                        `;
                        recordDiv.addEventListener('click', () => {
                            openRecordPopup(record.id, record.text || record.text_field);
                        });
                        recordsContainer.appendChild(recordDiv);

                        if (recordIdFromUrl && recordIdFromUrl === record.id.toString()) {
                            openRecordPopup(record.id, record.text || record.text_field);
                        }
                    });
                })
                .catch(error => {
                    console.error('Ошибка загрузки записей:', error);
                });
        }

        function openRecordPopup(id, text) {
            currentRecordId = id;
            document.getElementById('editRecordText').value = text;
            document.getElementById('recordPopupOverlay').style.display = 'block';
            document.getElementById('recordPopup').style.display = 'block';
            const recordButtons = document.querySelector('.record-buttons');
            recordButtons.style.display = 'block';
        }

        function closeRecordPopup() {
            currentRecordId = null;
            document.getElementById('recordPopupOverlay').style.display = 'none';
            document.getElementById('recordPopup').style.display = 'none';
            document.getElementById('editRecordText').value = '';
            document.getElementById('popupQrCode').innerHTML = '';
        }

        function updateRecord() {
            if (!currentRecordId) return;
            const text = document.getElementById('editRecordText').value.trim();
            fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update', id: currentRecordId, text }),
            })
                .then(response => response.json())
                .then(() => {
                    loadRecords();
                    closeRecordPopup();
                })
                .catch(error => console.error('Ошибка:', error));
        }

        function deleteRecord() {
            if (!currentRecordId) return;
            fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id: currentRecordId }),
            })
                .then(response => response.json())
                .then(() => {
                    loadRecords();
                    closeRecordPopup();
                })
                .catch(error => console.error('Ошибка:', error));
        }

        function generateQRCode() {
			const recordText = document.getElementById('editRecordText').value;
			const qrCodeElement = document.getElementById('popupQrCode');
		
			if (!recordText || !qrCodeElement) return;
		
			// Генерация QR-кода
			const qrCodeData = `https://predprod.reforma-sk.ru/marketplace/app/1/?id=${currentRecordId}`;
			const canvas = document.createElement('canvas');
			qrCodeElement.appendChild(canvas);

			QRCode.toCanvas(canvas, qrCodeData, { width: 200 }, (error) => {
				if (error) {
					console.error('Ошибка генерации QR-кода:', error);
					return;
				}
		
				// Получаем данные QR-кода в формате base64
				const qrCodeDataURL = canvas.toDataURL("image/png");
				
				// Для отладки: Выводим данные QR-кода в консоль
				console.log("QR-код в формате base64:", qrCodeDataURL);
				
				// Отправка данных на сервер
				fetch('/local-pril/dbscs.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify({
						record_id: currentRecordId,
						text: recordText,
						qr_code: qrCodeDataURL // Обратите внимание на это
					})
				})
				.then(response => response.json())
				.then(data => {
					console.log(data); // Для отладки
					if (data.status === 'success') {
						qrCodeElement.innerHTML = `<img src="${qrCodeDataURL}" width="200" />`;
					} else {
						alert('Ошибка: ' + data.message);
					}
				})
				.catch(error => {
					console.error('Ошибка:', error);
					alert('Произошла ошибка при отправке данных.');
				});
			});
		}



        // ---------- ВАЖНО: ФУНКЦИЯ ДЛЯ ГЕНЕРАЦИИ PDF-ДОКУМЕНТА -------
        function generatePdf() {
			if (!currentRecordId) return;
		
			const recordTitle = document.getElementById('editRecordText').value;
		
			fetch('/local-pril/generate_pdf.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ record_id: currentRecordId, title: recordTitle })
			})
			.then(response => response.json())
			.then(data => {
				if (data.status === 'success') {
					alert(data.message + ' Файл: ' + data.file_path);
					window.location.href = data.file_path; // Скачивание PDF
				} else {
					alert('Ошибка: ' + data.message);
				}
			})
			.catch(error => {
				console.error('Ошибка:', error);
				alert('Произошла ошибка при выполнении запроса.');
			});
		}



        function openAddNewPopup() {
            document.getElementById('addnewpopup').style.display = 'block';
        }

        function closeAddNewPopup() {
            document.getElementById('popuptonew').style.display = 'none';
            document.getElementById('addnewpopup').style.display = 'none';
            document.getElementById('addnew').value = '';
        }

        function addNewRecord() {
            const text = document.getElementById('addnew').value.trim();
            if (!text) return;
            const newId = Date.now().toString();
            fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add', id: newId, text }),
            })
                .then(response => response.json())
                .then(() => {
                    loadRecords();
                    closeAddNewPopup();
                })
                .catch(error => console.error('Ошибка:', error));
        }

        // -----------------------------------------------------
        //   КАМЕРА (QR scanner + фото) - ваш код без изменений
        // -----------------------------------------------------
        document.getElementById('addButton').addEventListener('click', openAddNewPopup);
        document.addEventListener('DOMContentLoaded', () => {
            loadRecords();
            loadGroups();
        });
        document.getElementById('cameraButton').addEventListener('click', function () {
            openCameraPopup();
        });

        function openCameraPopup() {
            document.getElementById('cameraPopupOverlay').style.display = 'block';
            document.getElementById('cameraPopup').style.display = 'block';
        }

        function closeCameraPopup() {
            document.getElementById('cameraPopupOverlay').style.display = 'none';
            document.getElementById('cameraPopup').style.display = 'none';
        }

        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const photoPreview = document.getElementById('photo-preview');
        const startCameraButton = document.getElementById('start-camera');
        const switchCameraButton = document.getElementById('switch-camera');
        const capturePhotoButton = document.getElementById('capture-photo');
        const qrOutput = document.getElementById('qr-output');

        let currentStream = null;
        let useFrontCamera = false;
        let scanningQR = false;

        async function startCamera(facingMode = 'environment') {
            try {
                if (currentStream) {
                    currentStream.getTracks().forEach(track => track.stop());
                }
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode }
                });
                video.srcObject = stream;
                currentStream = stream;
                capturePhotoButton.disabled = false;
                switchCameraButton.disabled = false;
                startQRScanner();
            } catch (error) {
                console.error('Ошибка доступа к камере:', error);
                alert('Не удалось получить доступ к камере.');
            }
        }

        startCameraButton.addEventListener('click', () => 
            startCamera(useFrontCamera ? 'user' : 'environment')
        );

        switchCameraButton.addEventListener('click', () => {
            useFrontCamera = !useFrontCamera;
            startCamera(useFrontCamera ? 'user' : 'environment');
        });

        capturePhotoButton.addEventListener('click', () => {
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            const photoDataUrl = canvas.toDataURL('image/png');
            photoPreview.src = photoDataUrl;
            photoPreview.style.display = 'block';
            sendPhotoToServer(photoDataUrl);
        });

        function sendPhotoToServer(photoDataUrl) {
            fetch('save_photo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ photo: photoDataUrl }),
            })
            .then(response => response.json())
            .then(data => {
                console.log('Ответ сервера при сохранении фото:', data);
            })
            .catch(error => console.error('Ошибка сохранения фото:', error));
        }

        function startQRScanner() {
            const canvasElement = document.createElement('canvas');
            const canvasContext = canvasElement.getContext('2d');

            function scanQR() {
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    canvasElement.width = video.videoWidth;
                    canvasElement.height = video.videoHeight;
                    canvasContext.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
                    const imageData = canvasContext.getImageData(0, 0, canvasElement.width, canvasElement.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height);

                    if (code) {
                        handleQRCode(code.data);
                    }
                }
                if (scanningQR) {
                    requestAnimationFrame(scanQR);
                }
            }

            scanningQR = true;
            scanQR();
        }

        function handleQRCode(data) {
        if (data.startsWith('https://bitrix24.com') || data.startsWith('https://predprod.reforma-sk.ru')) {
            qrOutput.innerHTML = `Ссылка на Битрикс24: <a href="${data}" target="_blank">${data}</a>`;
            closeCameraPopup();
            const recordId = new URL(data).searchParams.get('id');
            const groupId = new URL(data).searchParams.get('groupId');

            if (recordId) {
                // Загружаем текст записи по recordId
                fetch(apiUrl)
                    .then(response => response.json())
                    .then(data => {
                        const record = data.records[recordId];
                        if (record) {
                            openRecordPopup(recordId, record.text || record.text_field);
                        } else {
                            console.error('Запись не найдена');
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка загрузки записи:', error);
                    });
            } else if (groupId) {
                // Открываем группу по groupId
                openGroupPopup(groupId);
            }
        } else {
            qrOutput.innerHTML = `
                <p style="color: red;">Внимание! Внешняя ссылка:</p>
                <p>${data}</p>
                <p>Будьте осторожны.</p>
            `;
        }
        scanningQR = true; 
    }
    </script>
</body>
</html>
