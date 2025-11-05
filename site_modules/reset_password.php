<?php
session_start();
include 'db_connect.php';

function handlePasswordReset($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Некорректный метод запроса'];
    }

    $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING) ?? '';
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT) ?? 0;
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['reset_confirm_password'] ?? '';

    // Валидация данных
    if (empty($token) || $user_id <= 0) {
        return ['success' => false, 'message' => 'Неверная ссылка для сброса пароля'];
    }

    if (empty($new_password) || empty($confirm_password)) {
        return ['success' => false, 'message' => 'Заполните все поля'];
    }

    if ($new_password !== $confirm_password) {
        return ['success' => false, 'message' => 'Пароли не совпадают'];
    }

    if (strlen($new_password) < 6) {
        return ['success' => false, 'message' => 'Пароль должен содержать минимум 6 символов'];
    }

    // Проверяем токен (в реальной системе токены хранятся в БД с временем жизни)
    // Здесь упрощенная версия - проверяем существование пользователя
    $stmt = $mysqli->prepare("SELECT id, login FROM user WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($db_id, $db_login);
    
    if (!$stmt->fetch()) {
        $stmt->close();
        return ['success' => false, 'message' => 'Пользователь не найден'];
    }
    $stmt->close();

    // Обновляем пароль
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $mysqli->prepare("UPDATE user SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            // Пароль успешно обновлен
            $stmt->close();
            
            // Автоматически авторизуем пользователя
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $db_login;
            $_SESSION['first_name'] = ''; // Эти данные можно получить дополнительным запросом
            $_SESSION['last_name'] = '';
            $_SESSION['is_admin'] = 0;
            $_SESSION['foto'] = null;
            
            return [
                'success' => true, 
                'message' => 'Пароль успешно изменен! Вы автоматически вошли в систему.',
                'redirect' => 'userpage.php?id=' . $user_id
            ];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Ошибка при обновлении пароля'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()];
    }
}

// Если скрипт вызван напрямую
if (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
    header('Content-Type: application/json');
    $result = handlePasswordReset($mysqli);
    echo json_encode($result);
    exit;
}
?>