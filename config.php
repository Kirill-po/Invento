<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки прав пользователей и отделов</title>
    <style>
    body { 
        font-family: Arial, sans-serif; 
        margin: 0;
        padding: 0;
        background-color: white;
        min-height: 100vh;
    }
    .list { max-width: 500px; margin: auto; }
    .item { padding: 10px; border: 1px solid #ccc; margin: 5px 0; cursor: pointer; }
    .hidden { display: none; }
    .message { margin: 10px 0; padding: 5px; border-radius: 5px; }
    .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .info { background-color: #cce5ff; color: #004085; border: 1px solid #b8daff; }
    .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .access-denied {
        color: red;
        font-size: 36px;
        text-align: center;
        margin-top: 50px;
        font-weight: bold;
    }
    .filter-container {
        text-align: center;
        margin: 20px auto;
        max-width: 800px;
    }
    .filter-search-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
    }
    .filter-half, .search-half {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .search-half input[type="text"] {
        width: 100%;
        max-width: 200px;
        padding: 8px;
        border-radius: 4px;
        border: 1px solid #ccc;
        font-size: 14px;
        box-sizing: border-box;
    }
    @media (max-width: 768px) {
        .filter-search-wrapper {
            flex-direction: column;
            gap: 10px;
        }
        .filter-half, .search-half {
            width: 100%;
            max-width: 300px;
        }
        .search-half input[type="text"] {
            max-width: 100%;
        }
    }
    .filter-button-inactive {
        background-color: #ccc;
    }
    .filter-button-inactive:hover {
        background-color: #b3b3b3;
    }
    .filter-button-active {
        background-color: #e50045;
        color: #fff;
    }
    .filter-button-active:hover {
        background-color: #d0003f;
    }
    #filterButton {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.2s;
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
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        z-index: 1001;
        max-width: 400px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }
    .popup h2 {
        color: rgb(29, 25, 84);
        margin-bottom: 10px;
        font-size: 18px;
    }
    .popup .field-group {
        margin-bottom: 10px;
    }
    .popup .field-group label {
        display: block;
        margin-bottom: 5px;
        font-size: 14px;
    }
    .popup .field-group input[type="text"],
    .popup .field-group select {
        width: 100%;
        padding: 8px;
        border-radius: 4px;
        border: 1px solid #ccc;
        font-size: 14px;
        box-sizing: border-box;
    }
    .popup .record-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    .popup .record-buttons button {
        flex: 1;
        padding: 10px;
        font-size: 16px;
        border: none;
        border-radius: 5px;
        background-color: #e50045;
        color: #fff;
        cursor: pointer;
        text-align: center;
        box-sizing: border-box;
    }
    .popup .record-buttons button:hover {
        background-color: #d0003f;
    }
    </style>
</head>
<body>
    <div id="main-content" class="hidden">
        <h1>Настройки прав пользователей и отделов</h1>
        <div class="filter-container">
            <div class="filter-search-wrapper">
                <div class="filter-half">
                    <button id="filterButton" class="filter-button-inactive">Фильтр</button>
                </div>
                <div class="search-half">
                    <label for="searchInput">Поиск</label>
                    <input type="text" id="searchInput" placeholder="Поиск по пользователям...">
                </div>
            </div>
        </div>
        <div class="list" id="userList">
            <h2>Пользователи</h2>
        </div>
        <button onclick="window.location.href='pril.php'">← Вернуться</button>
    </div>
    <div id="access-denied" class="hidden">
        <div class="access-denied">Доступ запрещен</div>
    </div>

    <!-- Попап для фильтра -->
    <div class="popup-overlay" id="filterPopupOverlay"></div>
    <div class="popup" id="filterPopup">
        <h2>Фильтр пользователей</h2>
        <div class="field-group">
            <label for="filterName">Имя</label>
            <input type="text" id="filterName" placeholder="Введите имя...">
        </div>
        <div class="field-group">
            <label for="filterLastName">Фамилия</label>
            <input type="text" id="filterLastName" placeholder="Введите фамилию...">
        </div>
        <div class="field-group">
            <label for="filterEmail">Email</label>
            <input type="text" id="filterEmail" placeholder="Введите email...">
        </div>
        <div class="field-group">
            <label for="filterWorkPosition">Должность</label>
            <input type="text" id="filterWorkPosition" placeholder="Введите должность...">
        </div>
        <div class="field-group">
            <label for="filterDepartment">Отдел</label>
            <select id="filterDepartment">
                <option value="">Выберите отдел...</option>
            </select>
        </div>
        <div class="record-buttons">
            <button onclick="applyFilter()">Применить</button>
            <button onclick="cancelFilter()">Отменить</button>
        </div>
    </div>

    <script src="https://api.bitrix24.com/api/v1/"></script>
    <script>
    const apiUrl = 'rest_api.php';
    let userPermissions = null;
    let allUsers = [];
    let userProfiles = {};
    let isFilterApplied = false;
    let departments = [];

    function getUserPermissions(userId) {
        return fetch(`${apiUrl}?action=get_permissions&user_id=${userId}`)
            .then(response => response.json())
            .then(data => data.permission || 'view')
            .catch(() => 'view');
    }

    function loadUsers() {
        return fetch(`${apiUrl}?action=get_users`)
            .then(response => response.json())
            .then(users => {
                allUsers = users;
                return new Promise((resolve, reject) => {
                    BX24.callMethod('user.get', {}, function(result) {
                        if (result.error()) {
                            console.error('Ошибка загрузки профилей пользователей:', result.error());
                            userProfiles = {};
                            displayUsers(allUsers);
                            resolve(allUsers);
                        } else {
                            result.data().forEach(profile => {
                                userProfiles[profile.ID] = profile;
                            });
                            displayUsers(allUsers);
                            resolve(allUsers);
                        }
                    });
                });
            })
            .catch(error => {
                console.error('Ошибка загрузки пользователей:', error);
                return Promise.reject(error);
            });
    }

    function loadDepartments() {
        return new Promise((resolve, reject) => {
            let allDepartments = [];
            let start = 0;
            const pageSize = 50;

            function fetchDepartmentsPage() {
                BX24.callMethod(
                    'department.get',
                    {
                        sort: 'NAME',
                        order: 'ASC',
                        START: start
                    },
                    function(result) {
                        if (result.error()) {
                            console.error('Ошибка загрузки отделов:', result.error());
                            departments = [];
                            const filterDepartmentSelect = document.getElementById('filterDepartment');
                            filterDepartmentSelect.innerHTML = '<option value="">Отделы недоступны</option>';
                            resolve(departments);
                        } else {
                            const data = result.data();
                            allDepartments = allDepartments.concat(data);

                            const total = result.total();
                            if (start + pageSize < total) {
                                start += pageSize;
                                fetchDepartmentsPage();
                            } else {
                                departments = allDepartments;
                                const filterDepartmentSelect = document.getElementById('filterDepartment');
                                filterDepartmentSelect.innerHTML = '<option value="">Выберите отдел...</option>';
                                departments.forEach(dept => {
                                    const option = document.createElement('option');
                                    option.value = dept.ID;
                                    option.textContent = dept.NAME;
                                    filterDepartmentSelect.appendChild(option);
                                });
                                resolve(departments);
                            }
                        }
                    }
                );
            }

            fetchDepartmentsPage();
        });
    }

    function displayUsers(users) {
        const userList = document.getElementById('userList');
        userList.innerHTML = '<h2>Пользователи</h2>';
        if (!users || users.length === 0) {
            userList.innerHTML += '<p>Нет пользователей для отображения.</p>';
            return;
        }
        users.forEach(user => {
            const profile = userProfiles[user.ID] || {};
            const userDiv = document.createElement('div');
            userDiv.className = 'item';
            const displayName = profile.NAME || user.NAME || '';
            const displayLastName = profile.LAST_NAME || user.LAST_NAME || '';
            userDiv.textContent = `${displayLastName} ${displayName} (ID: ${user.ID})`;
            
            userDiv.onclick = () => showPermissions(user.ID, 'user');
            userDiv.ondblclick = () => openUserProfile(user.ID);
            
            userList.appendChild(userDiv);

            const formDiv = document.createElement('div');
            formDiv.className = 'hidden';
            formDiv.id = `form-user-${user.ID}`;
            formDiv.innerHTML = `
                <form onsubmit="savePermissions(event, ${user.ID}, 'user')">
                    <input type="hidden" name="user_id" value="${user.ID}">
                    <label><input type="radio" name="permission" value="full"> Полные права</label>
                    <label><input type="radio" name="permission" value="edit"> Добавление и редактирование</label>
                    <label><input type="radio" name="permission" value="view"> Только просмотр</label>
                    <button type="submit">Сохранить</button>
                    <div class="message hidden" id="message-user-${user.ID}"></div>
                </form>
            `;
            userList.appendChild(formDiv);

            getUserPermissions(user.ID)
                .then(permission => {
                    const form = document.getElementById(`form-user-${user.ID}`);
                    form.querySelector(`input[value="${permission}"]`).checked = true;
                })
                .catch(() => {
                    const form = document.getElementById(`form-user-${user.ID}`);
                    form.querySelector(`input[value="view"]`).checked = true;
                });
        });
    }

    function showPermissions(id, type) {
        setTimeout(() => {
            const allForms = document.querySelectorAll('.hidden');
            let isDblClick = false;

            allForms.forEach(form => {
                if (form.classList.contains('hidden')) return;
                isDblClick = true;
            });

            if (!isDblClick) {
                document.querySelectorAll('.hidden').forEach(form => form.classList.add('hidden'));
                document.getElementById(`form-${type}-${id}`).classList.remove('hidden');
            }
        }, 300);
    }

    function openUserProfile(userId) {
        if (typeof BX24 === 'undefined') {
            console.error('BX24 не загружен');
            alert('Ошибка: BX24 не загружен');
            return;
        }

        const profilePath = `/company/personal/user/${userId}/`;
        BX24.openPath(profilePath, function(result) {
            if (result.error()) {
                console.error('Ошибка открытия профиля:', result.error());
                alert('Не удалось открыть профиль пользователя. Ошибка: ' + result.error());
            }
        });
    }

    function savePermissions(event, id, type) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const data = {
            action: 'update_permission',
            [`${type}_id`]: formData.get(`${type}_id`),
            permission: formData.get('permission')
        };
        const messageBox = document.getElementById(`message-${type}-${id}`);

        if (!messageBox) {
            console.error(`Элемент messageBox с ID message-${type}-${id} не найден`);
            return;
        }

        fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                messageBox.textContent = data.message;
                messageBox.classList.remove('hidden', 'success', 'info');
                messageBox.classList.add(data.status);
            })
            .catch(error => {
                console.error('Ошибка в savePermissions:', error);
                messageBox.textContent = "Ошибка при сохранении";
                messageBox.classList.remove('hidden', 'success', 'info');
                messageBox.classList.add('error');
            });
    }

    function updateFilterButtonStyle() {
        const filterButton = document.getElementById('filterButton');
        if (isFilterApplied) {
            filterButton.classList.remove('filter-button-inactive');
            filterButton.classList.add('filter-button-active');
        } else {
            filterButton.classList.remove('filter-button-active');
            filterButton.classList.add('filter-button-inactive');
        }
    }

    function applyFilter() {
        const filters = {
            name: document.getElementById('filterName').value.trim().toLowerCase(),
            lastName: document.getElementById('filterLastName').value.trim().toLowerCase(),
            email: document.getElementById('filterEmail').value.trim().toLowerCase(),
            workPosition: document.getElementById('filterWorkPosition').value.trim().toLowerCase(),
            department: document.getElementById('filterDepartment').value
        };

        isFilterApplied = !Object.values(filters).every(val => !val);
        updateFilterButtonStyle();

        applySearchAndFilter();
        closeFilterPopup();
    }

    function cancelFilter() {
        document.getElementById('filterName').value = '';
        document.getElementById('filterLastName').value = '';
        document.getElementById('filterEmail').value = '';
        document.getElementById('filterWorkPosition').value = '';
        document.getElementById('filterDepartment').value = '';

        isFilterApplied = false;
        updateFilterButtonStyle();

        applySearchAndFilter();
        closeFilterPopup();
    }

    function openFilterPopup() {
        document.getElementById('filterPopupOverlay').style.display = 'block';
        document.getElementById('filterPopup').style.display = 'block';
    }

    function closeFilterPopup() {
        document.getElementById('filterPopupOverlay').style.display = 'none';
        document.getElementById('filterPopup').style.display = 'none';
    }

    function applySearchAndFilter() {
        const searchText = document.getElementById('searchInput').value.trim().toLowerCase();
        const filters = {
            name: document.getElementById('filterName').value.trim().toLowerCase(),
            lastName: document.getElementById('filterLastName').value.trim().toLowerCase(),
            email: document.getElementById('filterEmail').value.trim().toLowerCase(),
            workPosition: document.getElementById('filterWorkPosition').value.trim().toLowerCase(),
            department: document.getElementById('filterDepartment').value
        };

        let filteredUsers = allUsers;

        filteredUsers = filteredUsers.filter(user => {
            const profile = userProfiles[user.ID] || {};
            const matchesName = !filters.name || 
                (profile.NAME && profile.NAME.toLowerCase().includes(filters.name)) || 
                (user.NAME && user.NAME.toLowerCase().includes(filters.name));
            const matchesLastName = !filters.lastName || 
                (profile.LAST_NAME && profile.LAST_NAME.toLowerCase().includes(filters.lastName)) || 
                (user.LAST_NAME && user.LAST_NAME.toLowerCase().includes(filters.lastName));
            const matchesEmail = !filters.email || 
                (profile.EMAIL && profile.EMAIL.toLowerCase().includes(filters.email)) || 
                (user.EMAIL && user.EMAIL.toLowerCase().includes(filters.email));
            const matchesWorkPosition = !filters.workPosition || 
                (profile.WORK_POSITION && profile.WORK_POSITION.toLowerCase().includes(filters.workPosition)) || 
                (user.WORK_POSITION && user.WORK_POSITION.toLowerCase().includes(filters.workPosition));
            const matchesDepartment = !filters.department || 
                (profile.UF_DEPARTMENT && profile.UF_DEPARTMENT.includes(parseInt(filters.department)));

            return matchesName && matchesLastName && matchesEmail && matchesWorkPosition && matchesDepartment;
        });

        if (searchText) {
            filteredUsers = filteredUsers.filter(user => {
                const profile = userProfiles[user.ID] || {};
                const fieldsToSearch = [
                    (profile.NAME || user.NAME || ''),
                    (profile.LAST_NAME || user.LAST_NAME || ''),
                    (profile.EMAIL || user.EMAIL || '')
                ];
                return fieldsToSearch.some(field => field.toLowerCase().includes(searchText));
            });
        }

        displayUsers(filteredUsers);
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof BX24 === 'undefined') {
            document.body.innerHTML = '<div>Ошибка: BX24 не загружен</div>';
            return;
        }

        BX24.init(function() {
            let userId;

            if (typeof BX24.getAuth === 'function') {
                const auth = BX24.getAuth();
                if (auth && auth.userId) {
                    userId = auth.userId;
                    BX24.callMethod('user.current', {}, function(result) {
                        if (result.error()) {
                            console.error('Ошибка получения данных пользователя:', result.error());
                        } else {
                            const user = result.data();
                            console.log('Текущий пользователь:', user);
                            if (!user.ADMIN) {
                                console.warn('Текущий пользователь не является администратором. Возможно, у него нет прав на просмотр отделов.');
                            }
                        }
                    });
                    initializePage(userId);
                } else {
                    BX24.callMethod('user.current', {}, function(result) {
                        if (result.error()) {
                            console.error('Ошибка получения пользователя:', result.error());
                            showAccessDenied();
                        } else {
                            userId = result.data().ID;
                            initializePage(userId);
                        }
                    });
                }
            } else {
                BX24.callMethod('user.current', {}, function(result) {
                    if (result.error()) {
                        console.error('Ошибка получения пользователя:', result.error());
                        showAccessDenied();
                    } else {
                        userId = result.data().ID;
                        initializePage(userId);
                    }
                });
            }
        });
    });

    function initializePage(userId) {
        getUserPermissions(userId)
            .then(permissions => {
                userPermissions = permissions;
                if (permissions === 'full') {
                    document.getElementById('main-content').classList.remove('hidden');
                    Promise.all([loadUsers(), loadDepartments()]).then(() => {
                        const filterButton = document.getElementById('filterButton');
                        const searchInput = document.getElementById('searchInput');

                        if (filterButton) {
                            filterButton.addEventListener('click', openFilterPopup);
                        }

                        if (searchInput) {
                            searchInput.addEventListener('input', applySearchAndFilter);
                        }

                        updateFilterButtonStyle();
                    }).catch(err => {
                        console.error('Ошибка инициализации:', err);
                        showAccessDenied();
                    });
                } else {
                    showAccessDenied();
                }
            })
            .catch(err => {
                console.error('Ошибка получения прав:', err);
                showAccessDenied();
            });
    }

    function showAccessDenied() {
        document.getElementById('access-denied').classList.remove('hidden');
    }
    </script>
</body>
</html>