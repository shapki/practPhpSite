<script>
    let pendingAvatarDelete = false;

    function showUploadForm() {
        document.getElementById('uploadForm').style.display = 'block';
    }

    function closeUploadForm() {
        document.getElementById('uploadForm').style.display = 'none';
        document.getElementById('avatarFile').value = '';
        document.getElementById('fileError').style.display = 'none';
    }

    // Функция для показа/скрытия индикатора загрузки
    function showLoading(show = true) {
        const loadingElement = document.getElementById('loadingIndicator') || createLoadingIndicator();
        loadingElement.style.display = show ? 'block' : 'none';
    }

    function createLoadingIndicator() {
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'loadingIndicator';
        loadingDiv.innerHTML = '<div class="loading-spinner"></div>';
        loadingDiv.style.cssText = 'position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); padding:20px; border-radius:5px; z-index:10000; display:flex; align-items:center;';
        document.body.appendChild(loadingDiv);
        return loadingDiv;
    }

    function uploadAvatar() {
        const fileInput = document.getElementById('avatarFile');
        const fileError = document.getElementById('fileError');
        
        if (!fileInput.files || !fileInput.files[0]) {
            fileError.textContent = 'Выберите файл';
            fileError.style.display = 'block';
            return;
        }
        
        const file = fileInput.files[0];
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!allowedTypes.includes(file.type)) {
            fileError.textContent = 'Разрешены только JPG, PNG и GIF файлы';
            fileError.style.display = 'block';
            return;
        }
        
        if (file.size > maxSize) {
            fileError.textContent = 'Размер файла не должен превышать 5MB';
            fileError.style.display = 'block';
            return;
        }
        
        fileError.style.display = 'none';
        showLoading(true);
        
        const formData = new FormData();
        formData.append('action', 'upload_avatar');
        formData.append('avatar', file);
        formData.append('user_id', <?php echo $user['id']; ?>);
        
        fetch('site_modules/update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети: ' + response.status);
            }
            return response.text();
        })
        .then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Невалидный JSON ответ:', text);
                throw new Error('Сервер вернул невалидный ответ');
            }
        })
        .then(data => {
            if (data.success) {
                showMessage('Фото успешно загружено');
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                showMessage(data.message, true);
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showMessage(error.message, true);
        })
        .finally(() => {
            showLoading(false);
        });
    }

    function deleteAvatar() {
        document.querySelector('#confirmDeleteModal .modal-content p').textContent = 'Вы уверены, что хотите удалить фото?';
        openModal('confirmDeleteModal');
        pendingAvatarDelete = true;
    }

    function confirmDeleteAvatar() {
        if (!pendingAvatarDelete) return;
        
        closeModal('confirmDeleteModal');
        pendingAvatarDelete = false;
        showLoading(true);
        
        const formData = new FormData();
        formData.append('action', 'delete_avatar');
        formData.append('user_id', <?php echo $user['id']; ?>);
        
        fetch('site_modules/update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети: ' + response.status);
            }
            return response.text();
        })
        .then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Невалидный JSON ответ:', text);
                throw new Error('Сервер вернул невалидный ответ: ' + text.substring(0, 100));
            }
        })
        .then(data => {
            if (data.success) {
                showMessage('Фото успешно удалено');
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                showMessage(data.message, true);
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showMessage('Ошибка: ' + error.message, true);
        })
        .finally(() => {
            showLoading(false);
        });
    }

    document.querySelector('#confirmDeleteModal .modal-close').addEventListener('click', function() {
        pendingAvatarDelete = false;
    });
    document.querySelector('#confirmDeleteModal .style3-btn').addEventListener('click', function() {
        pendingAvatarDelete = false;
    });

    // Функция для работы с модальными окнами
    function openEditModal(type) {
        if (type === 'company') {
            openModal('companyModal');
        } else if (type === 'name') {
            openModal('nameModal');
        }
    }
    
    function sendUpdateRequest(formData, successMessage) {
        showLoading(true);
        
        return fetch('site_modules/update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети: ' + response.status);
            }
            return response.text();
        })
        .then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Невалидный JSON ответ:', text);
                throw new Error('Сервер вернул невалидный ответ');
            }
        })
        .then(data => {
            if (data.success) {
                showMessage(successMessage);
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                showMessage(data.message, true);
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showMessage(error.message, true);
        })
        .finally(() => {
            showLoading(false);
        });
    }

    function saveName() {
        const firstName = document.getElementById('editFirstName').value.trim();
        const lastName = document.getElementById('editLastName').value.trim();
        
        if (!firstName || !lastName) {
            showMessage('Имя и фамилия не могут быть пустыми', true);
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'update_name');
        formData.append('first_name', firstName);
        formData.append('last_name', lastName);
        formData.append('user_id', <?php echo $user['id']; ?>);
        
        sendUpdateRequest(formData, 'Имя успешно обновлено');
    }

    function saveCompany() {
        const company = document.getElementById('editCompany').value.trim();
        
        const formData = new FormData();
        formData.append('action', 'update_company');
        formData.append('company', company);
        formData.append('user_id', <?php echo $user['id']; ?>);
        
        sendUpdateRequest(formData, 'Компания успешно обновлена');
    }

    function saveSettings() {
        const newLogin = document.getElementById('newLogin').value.trim();
        const currentPassword = document.getElementById('currentPassword').value;
        const currentPasswordError = document.getElementById('currentPasswordError');
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const passwordError = document.getElementById('passwordError');
        const editEmailInput = document.getElementById('editEmail');
        const emailError = document.getElementById('emailError');
        const email = editEmailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (newPassword && newPassword !== confirmPassword) {
            passwordError.textContent = 'Пароли не совпадают';
            passwordError.style.display = 'block';
            return;
        }
        
        if (!currentPassword) {
            currentPasswordError.textContent = 'Введите текущий пароль';
            currentPasswordError.style.display = 'block';
            return;
        }
        
        if (email === '') {
            emailError.textContent = 'Почта не должна быть пустой';
            emailError.style.display = 'block';
            return;
        } else if (!emailRegex.test(email)) {
            emailError.textContent = 'Введите корректный почтовый адрес';
            emailError.style.display = 'block';
            return;
        }
        
        passwordError.style.display = 'none';
        currentPasswordError.style.display = 'none';
        emailError.style.display = 'none';
        
        const formData = new FormData();
        formData.append('action', 'update_settings');
        formData.append('new_login', newLogin);
        formData.append('current_password', currentPassword);
        formData.append('new_password', newPassword);
        formData.append('email', email);
        formData.append('user_id', <?php echo $user['id']; ?>);
        
        sendUpdateRequest(formData, 'Настройки успешно обновлены');
    }
</script>