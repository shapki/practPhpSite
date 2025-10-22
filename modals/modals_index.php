<!-- Окно регистрации -->
<div class="modal-overlay" id="registerModal">
    <div class="modal">
        <div class="modal-header">
            Регистрация
            <button class="modal-close">&times;</button>
        </div>
        <form id="registerForm">
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label for="reg_login">Логин:</label>
                <input type="text" id="reg_login" name="login">
                <div class="error-message" id="loginError"></div>
            </div>
            
            <div class="split-fields">
                <div class="form-group">
                    <label for="reg_password">Пароль:</label>
                    <input type="password" id="reg_password" name="password">
                    <div class="error-message" id="passwordError"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Подтвердите пароль:</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                    <div class="error-message" id="confirmError"></div>
                </div>
            </div>

            <div class="split-fields">
                <div class="form-group">
                    <label for="first_name">Имя:</label>
                    <input type="text" id="first_name" name="first_name">
                    <div class="error-message" id="fullNameError"></div>
                </div>
                <div class="form-group">
                    <label for="last_name">Фамилия:</label>
                    <input type="text" id="last_name" name="last_name">
                </div>
            </div>

            <div class="form-group">
                <label for="company">Компания [Не обязательно]:</label>
                <input type="text" id="company" name="company">
            </div>

            <div class="form-group">
                <label for="registr_email">Почта [Не обязательно]:</label>
                <input type="email" id="registr_email" name="registr_email">
            </div>
            
            <div class="decoration"></div>
            
            <div class="buttons">
                <button type="submit" class="style1-btn">Зарегистрироваться</button>
                <button type="button" class="style3-btn" id="cancelRegister">Отмена</button>
            </div>
        </form>
    </div>
</div>

<!-- Окно восстановления пароля -->
<div class="modal-overlay" id="forgotModal">
    <div class="modal">
        <div class="modal-header">
            Восстановление пароля
            <button class="modal-close">&times;</button>
        </div>
        <form id="forgotForm">
            <input type="hidden" name="action" value="forgot">
            <div class="form-group">
                <label for="forgot_login">Логин:</label>
                <input type="text" id="forgot_login" name="login" required>
            </div>
            
            <div class="form-group">
                <label for="forgot_email">Email:</label>
                <input type="email" id="forgot_email" name="forgot_email" required>
            </div>
            
            <div class="decoration"></div>
            
            <div class="buttons">
                <button type="submit" class="style1-btn">Восстановить</button>
                <button type="button" class="style3-btn" id="cancelForgot">Отмена</button>
            </div>
        </form>
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