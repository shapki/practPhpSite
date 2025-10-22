<?php 
    session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCM | Авторизация</title>
    <link rel="stylesheet" href="/styles/styles.css">
    <link rel="stylesheet" href="/styles/modalWindowStyles.css">
    <link rel="stylesheet" href="/styles/index.css">
</head>
<body>
    <?php
        include 'site_modules/page_header.php';
    ?>
    <div class="main-content">
        <div class="container">
            <div class="login-header">
                <div class="logo">MCM</div>
                <div class="sublogo-text">
                    <p>Мастерская Костюмов Мюррея</p>
                </div>
            </div>
            
            <div class="login-main">
                <div class="auth-form">
                    <h1>Авторизация</h1>
                    <div class="why-authorize">
                        <p>Представьтесь в системе, чтобы получить скидку 20% на первый костюм</p>
                    </div>
                    <form id="loginForm">
                        <input type="hidden" name="action" value="login">
                        <div class="form-group">
                            <label for="username">Логин:</label>
                            <input type="text" id="username" name="username">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Пароль:</label>
                            <input type="password" id="password" name="password">
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Оставаться в системе</label>
                        </div>
                        
                        <div class="decoration"></div>
                        
                        <div class="buttons">
                            <button type="submit" class="style1-btn">Войти</button>
                            <button type="button" class="style2-btn" id="registerBtn">Зарегистрироваться</button>
                            <button type="button" class="style3-btn" id="forgotBtn">Забыл пароль</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="login-footer">
                <div class="contact-info">
                    <p>MCM Systems © 1970-<?php echo date("Y"); ?></p>
                    <p>Телефон: (800) 555-35-35</p>
                    <p>Адрес: г. Ураган, ул. Ветренная, д. 10</p>
                </div>
            </div>
        </div>

        <?php
            include 'modals/modals_index.php';
        ?>
    </div>

    <script src="scripts/main.js"></script>
    <script src="scripts/index.js"></script>
</body>
</html>