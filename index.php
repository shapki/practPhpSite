<?php 
    session_start();
    
    if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'])) {
        include 'site_modules/db_connect.php';
        include 'site_modules/auth_cookie.php';
        
        $user_id = $_COOKIE['remember_user'];
        $user_data = validateRememberCookie($user_id, $mysqli);
        
        if ($user_data) {
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user_data['login'];
            $_SESSION['first_name'] = $user_data['first_name'];
            $_SESSION['last_name'] = $user_data['last_name'];
            $_SESSION['is_admin'] = $user_data['administrator'];
            $_SESSION['foto'] = $user_data['foto'];
            
            header('Location: userpage.php?id=' . $user_data['id']);
            exit();
        } else {
            setcookie('remember_user', '', time() - 3600, '/');
        }
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCM | <?php echo isset($_SESSION['user_id']) ? 'Личный кабинет' : 'Авторизация'; ?></title>
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
            
            <?php if (!isset($_SESSION['user_id'])): ?>
                <!-- только для неавторизованных -->
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
            <?php else: ?>
                <!-- для авторизованных -->
                <div class="login-main">
                    <div class="auth-form">
                        <h1>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . mb_substr($_SESSION['last_name'], 0, 1, 'UTF-8') . '.'); ?></h1>
                        <div class="why-authorize">
                            <p>Вы уже авторизованы в системе. Перейдите в личный кабинет для управления профилем.</p>
                        </div>
                        
                        <div class="decoration"></div>
                        
                        <div class="buttons">
                            <button type="button" class="style1-btn" onclick="window.location.href='userpage.php?id=<?php echo $_SESSION['user_id']; ?>'">
                                Личный кабинет
                            </button>
                            <button type="button" class="style3-btn" onclick="logout()">Выйти</button>
                        </div>
                    </div>
                </div>
                
                <script>
                function logout() {
                    fetch('/site_modules/logout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'ajax=true'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'index.php';
                        }
                    });
                }
                </script>
            <?php endif; ?>
            
            <div class="login-footer">
                <div class="contact-info">
                    <p>MCM Systems © 1970-<?php echo date("Y"); ?></p>
                    <p>Телефон: (800) 555-35-35</p>
                    <p>Адрес: г. Ураган, ул. Ветренная, д. 10</p>
                </div>
            </div>
        </div>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <?php include 'modals/modals_index.php';
            include 'modals/modals_message.php'; ?>
        <?php endif; ?>
    </div>

    <script src="scripts/main.js"></script>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <script src="scripts/index.js"></script>
    <?php endif; ?>
</body>
</html>