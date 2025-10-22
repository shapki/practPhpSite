<!-- Окно изменения фото -->
<div class="modal-overlay" id="avatarModal">
    <div class="modal">
        <div class="modal-header">
            Изменение фотографии
            <button class="modal-close" onclick="closeModal('avatarModal')">&times;</button>
        </div>
        <div class="modal-content">
            <div class="form-group">
                <button class="style1-btn" style="width: 100%; margin-bottom: 15px;" onclick="showUploadForm()">Загрузить фото</button>
                <?php if (!empty($user['foto'])): ?>
                    <button class="style3-btn" style="width: 100%;" onclick="deleteAvatar()">Удалить фото</button>
                <?php endif; ?>
            </div>
            
            <div id="uploadForm" style="display: none;">
                <div class="form-group">
                    <label>Выберите файл (JPG, PNG, GIF):</label>
                    <input type="file" id="avatarFile" accept="image/jpeg,image/png,image/gif">
                    <div class="error-message" id="fileError"></div>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="style1-btn" onclick="uploadAvatar()">Загрузить</button>
                    <button type="button" class="style3-btn" onclick="closeUploadForm()">Отмена</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Окно редактирования имени -->
<div class="modal-overlay" id="nameModal">
    <div class="modal">
        <div class="modal-header">
            Редактирование имени
            <button class="modal-close" onclick="closeModal('nameModal')">&times;</button>
        </div>
        <div class="modal-content">
            <div class="form-group">
                <label for="editFirstName">Имя:</label>
                <input type="text" id="editFirstName" value="<?php echo htmlspecialchars($user['first_name']); ?>">
            </div>
            <div class="form-group">
                <label for="editLastName">Фамилия:</label>
                <input type="text" id="editLastName" value="<?php echo htmlspecialchars($user['last_name']); ?>">
            </div>
            <div class="modal-buttons">
                <button class="style1-btn" onclick="saveName()">Сохранить</button>
                <button class="style3-btn" onclick="closeModal('nameModal')">Отмена</button>
            </div>
        </div>
    </div>
</div>

<!-- Окно редактирования почты -->
<div class="modal-overlay" id="emailModal">
    <div class="modal">
        <div class="modal-header">
            Редактирование почты
            <button class="modal-close" onclick="closeModal('emailModal')">&times;</button>
        </div>
        <div class="modal-content">
            <div class="form-group">
                <label for="editEmail">Почта:</label>
                <input type="email" id="editEmail" value="<?php echo htmlspecialchars($user['e_mail']); ?>">
                <div class="error-message" id="emailError"></div>
            </div>
            <div class="modal-buttons">
                <button class="style1-btn" onclick="saveEmail()">Сохранить</button>
                <button class="style3-btn" onclick="closeModal('emailModal')">Отмена</button>
            </div>
        </div>
    </div>
</div>

<!-- Окно редактирования компании -->
<div class="modal-overlay" id="companyModal">
    <div class="modal">
        <div class="modal-header">
            Редактирование компании
            <button class="modal-close" onclick="closeModal('companyModal')">&times;</button>
        </div>
        <div class="modal-content">
            <div class="form-group">
                <label for="editCompany">Компания:</label>
                <input type="text" id="editCompany" value="<?php echo htmlspecialchars($user['company']); ?>">
            </div>

            <div class="modal-buttons">
                <button class="style1-btn" onclick="saveCompany()">Сохранить</button>
                <button class="style3-btn" onclick="closeModal('companyModal')">Отмена</button>
            </div>
        </div>
    </div>
</div>

<!-- Окно настроек -->
<div class="modal-overlay" id="settingsModal">
    <div class="modal">
        <div class="modal-header">
            Настройки аккаунта
            <button class="modal-close" onclick="closeModal('settingsModal')">&times;</button>
        </div>
        <div class="modal-content">                
            <div class="form-group">
                <label for="newLogin">Логин:</label>
                <input type="text" id="newLogin" value="<?php echo htmlspecialchars($user['login']); ?>">
            </div>
            
            <div class="form-group">
                <label for="currentPassword">Текущий пароль:</label>
                <input type="password" id="currentPassword">
            </div>
            
            <div class="split-fields">                    
                <div class="form-group">
                    <label for="newPassword">Новый пароль:</label>
                    <input type="password" id="newPassword">
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Подтверждение пароля:</label>
                    <input type="password" id="confirmPassword">
                    <div class="error-message" id="passwordError"></div>
                </div>
            </div>

            <div class="decoration"></div>
            
            <div class="modal-buttons">
                <button class="style1-btn" onclick="saveSettings()">Сохранить</button>
                <button class="style3-btn" onclick="closeModal('settingsModal')">Отмена</button>
            </div>
        </div>
    </div>
</div>

<!-- Окно для сообщений -->
<div class="modal-overlay message-modal" id="messageModal">
    <div class="modal">
        <div class="modal-header">
            Сообщение
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-content" id="messageContent">

        </div>
        <div class="modal-buttons">
            <button type="button" class="style1-btn" id="messageOkBtn">OK</button>
        </div>
    </div>
</div>

<!-- Окно подтверждения удаления фото -->
<div class="modal-overlay message-modal" id="confirmDeleteModal">
    <div class="modal">
        <div class="modal-header">
            Подтверждение удаления
            <button class="modal-close" onclick="closeModal('confirmDeleteModal')">&times;</button>
        </div>
        <div class="modal-content">
            <p>Вы уверены, что хотите удалить фото?</p>
        </div>
        <div class="modal-buttons">
            <button type="button" class="style1-btn" onclick="confirmDeleteAvatar()">OK</button>
            <button type="button" class="style3-btn" onclick="closeModal('confirmDeleteModal')">Отмена</button>
        </div>
    </div>
</div>