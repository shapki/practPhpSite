<?php
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        header('Content-Type: application/json');
        
        include 'site_modules/db_connect.php';
        
        $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING) ?? '';
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT) ?? 0;
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
    
        // Валидация
        if (empty($token) || $user_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Неверная ссылка для сброса пароля']);
            exit;
        }
    
        if (empty($new_password) || empty($confirm_password)) {
            echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
            exit;
        }
    
        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Пароли не совпадают']);
            exit;
        }
    
        if (strlen($new_password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Пароль должен содержать минимум 6 символов']);
            exit;
        }
    
        // Проверка токена в БД
        $current_time = date('Y-m-d H:i:s');
        $stmt = $mysqli->prepare("
            SELECT prt.user_id, u.id 
            FROM password_reset_tokens prt 
            JOIN user u ON prt.user_id = u.id 
            WHERE prt.token = ? AND prt.user_id = ? AND prt.expires_at > ?
        ");
        $stmt->bind_param("sis", $token, $user_id, $current_time);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows === 0) {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Ссылка для сброса пароля недействительна или устарела']);
            exit;
        }
        $stmt->close();
    
        // Проверка пользователя
        $stmt = $mysqli->prepare("SELECT id, login, first_name, last_name, administrator, foto FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($db_id, $db_login, $db_first_name, $db_last_name, $db_administrator, $db_foto);
        
        if (!$stmt->fetch()) {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
            exit;
        }
        $stmt->close();
    
        // Обновление пароля
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        try {
            // Начинаем транзакцию для атомарного обновления
            $mysqli->begin_transaction();
            
            // Обновляем пароль
            $stmt = $mysqli->prepare("UPDATE user SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                // Удаляем использованный токен
                $delete_stmt = $mysqli->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
                $delete_stmt->bind_param("s", $token);
                $delete_stmt->execute();
                $delete_stmt->close();
                
                $mysqli->commit();
                
                $stmt->close();
                
                // Автоматический вход пользователя
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $db_login;
                $_SESSION['first_name'] = $db_first_name;
                $_SESSION['last_name'] = $db_last_name;
                $_SESSION['is_admin'] = $db_administrator;
                $_SESSION['foto'] = $db_foto;
                
                echo json_encode(['success' => true, 'message' => 'Пароль успешно изменен! Вы автоматически вошли в систему.', 'redirect' => 'userpage.php?id=' . $user_id]);
                exit;
            } else {
                $mysqli->rollback();
                $stmt->close();
                echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении пароля']);
                exit;
            }
        } catch (Exception $e) {
            $mysqli->rollback();
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
            exit;
        }
    }

    $token = $_GET['token'] ?? '';
    $user_id = $_GET['user_id'] ?? 0;

    $is_valid_token = false;
    if (!empty($token) && $user_id > 0) {
        include 'site_modules/db_connect.php';
        
        // Проверяем токен в БД
        $current_time = date('Y-m-d H:i:s');
        $stmt = $mysqli->prepare("
            SELECT token 
            FROM password_reset_tokens 
            WHERE token = ? AND user_id = ? AND expires_at > ?
        ");
        $stmt->bind_param("sis", $token, $user_id, $current_time);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $is_valid_token = true;
        }
        $stmt->close();
    }

    if (!$is_valid_token) {
        ?>
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>MCM | Ошибка сброса пароля</title>
            <link rel="stylesheet" href="styles/styles.css">
            <link rel="stylesheet" href="styles/modalWindowStyles.css">
            <link rel="stylesheet" href="styles/index.css">
        </head>
        <body>
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
                            <h1>Ошибка сброса пароля</h1>
                            <p>Ссылка для сброса пароля недействительна или устарела.</p>
                            <p>Пожалуйста, запросите восстановление пароля заново.</p>
                            <div class="buttons">
                                <button type="button" class="style1-btn" onclick="window.location.href='index.php'">На главную</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCM | Сброс пароля</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/modalWindowStyles.css">
    <link rel="stylesheet" href="styles/index.css">
</head>
<body>
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
                    <h1>Сброс пароля</h1>
                    <form id="resetPasswordForm">
                        <input type="hidden" name="action" value="reset_password">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                        
                        <div class="form-group">
                            <label for="new_password">Новый пароль:</label>
                            <input type="password" id="new_password" name="new_password" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Подтвердите пароль:</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                        </div>
                        
                        <div class="buttons">
                            <button type="submit" class="style1-btn" id="submitBtn">Установить новый пароль</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'modals/modals_index.php';
    include 'modals/modals_message.php'; ?>

    <script src="scripts/main.js"></script>
    <script src="scripts/reset_password.js"></script>
</body>
</html>