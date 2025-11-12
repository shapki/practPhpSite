<?php
    header('Content-Type: application/json');

    include 'db_connect.php';

    function clearStoredResults($mysqli) {
        do {
            if ($res = $mysqli->store_result()) {
                $res->free();
            }
        } while ($mysqli->more_results() && $mysqli->next_result());
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING) ?? '';
        
        switch ($action) {
            case 'login':
                handleLogin($mysqli);
                break;
            case 'register':
                handleRegister($mysqli);
                break;
            case 'forgot':
                handleForgotPassword($mysqli);
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Неизвестное действие']);
                break;
        }
    }

    function handleLogin($mysqli) {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING) ?? '';
        $password = $_POST['password'] ?? '';
        $remember = filter_input(INPUT_POST, 'remember', FILTER_VALIDATE_BOOLEAN) ?? false;
        
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
            return;
        }
        
        $stmt = $mysqli->prepare("SELECT id, login, password, first_name, last_name, administrator, foto, e_mail FROM user WHERE login = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($id, $login, $hashed_password, $first_name, $last_name, $administrator, $foto, $email);
        
        if ($stmt->fetch() && password_verify($password, $hashed_password)) {            
            session_start();
            $_SESSION['user_id']        = $id;
            $_SESSION['username']       = $login;
            $_SESSION['first_name']     = $first_name;
            $_SESSION['last_name']      = $last_name;
            $_SESSION['is_admin']       = $administrator;
            $_SESSION['foto']           = $foto;
            
            if ($remember) {
                // Устанавливаем cookie на 30 дней
                $cookie_expire = time() + (30 * 24 * 60 * 60);
                setcookie('remember_user', $id, $cookie_expire, '/', '', false, true);
            } else {
                if (isset($_COOKIE['remember_user'])) {
                    setcookie('remember_user', '', time() - 3600, '/');
                }
            }
    
            echo json_encode(['success' => true, 'message' => 'Авторизация успешна', 'redirect' => 'userpage.php?id=' . $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Неверный логин или пароль']);
        }
        $stmt->close();
    }

    function handleRegister($mysqli) {
        $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING) ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING) ?? '';
        $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING) ?? '';
        $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_STRING) ?? '';
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? '';
        
        $stmt = $mysqli->prepare("SELECT id FROM user WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Пользователь с таким логином уже существует']);
            return;
        }
        $stmt->close();
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $mysqli->prepare("INSERT INTO user (login, password, first_name, last_name, company, e_mail) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $login, $hashed_password, $first_name, $last_name, $company, $email);
            $stmt->execute();
            
            $user_id = $stmt->insert_id;
            $stmt->close();
            
            session_start();
            $_SESSION['user_id']    = $user_id;
            $_SESSION['username']   = $login;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name']  = $last_name;
            $_SESSION['company']    = $company;
            $_SESSION['email']      = $email;
            $_SESSION['is_admin']   = 0;
            $_SESSION['foto']       = null;

            if (!empty($email)) {
                include 'send_email.php';
                sendEmail($email, 'register_success', [], $mysqli);
            }
            
            echo json_encode(['success' => true, 'message' => 'Регистрация успешна! Вы автоматически вошли в систему.', 'redirect' => 'userpage.php?id=' . $user_id]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка при регистрации: ' . $e->getMessage()]);
        }
    }

    function handleForgotPassword($mysqli) {
        $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING) ?? '';
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? '';
        
        if (empty($login) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
            return;
        }
        
        $stmt = $mysqli->prepare("SELECT id, e_mail, login, first_name, last_name FROM user WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $stmt->bind_result($user_id, $db_email, $db_login, $first_name, $last_name);
        
        $user_exists = false;
        $user_data = [];
        
        if ($stmt->fetch()) {
            $user_exists = true;
            $user_data = [
                'id' => $user_id,
                'email' => $db_email,
                'login' => $db_login,
                'first_name' => $first_name,
                'last_name' => $last_name
            ];
        }
        $stmt->close();
        
        // Очищаем ожидающие результаты
        clearStoredResults($mysqli);    
        
        if ($user_exists && $email === $user_data['email']) {
            $reset_token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 час
            
            $delete_stmt = $mysqli->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
            $delete_stmt->bind_param("i", $user_data['id']);
            $delete_result = $delete_stmt->execute();
            $delete_stmt->close();
            
            if (!$delete_result) {
                echo json_encode(['success' => false, 'message' => 'Ошибка при очистке старых токенов']);
                return;
            }
            
            $insert_stmt = $mysqli->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("iss", $user_data['id'], $reset_token, $expires_at);
            
            if ($insert_stmt->execute()) {
                $reset_link = "https://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $reset_token . "&user_id=" . $user_data['id'];
                
                $formatted_username = htmlspecialchars($user_data['first_name'] . ' ' . mb_substr($user_data['last_name'], 0, 1, 'UTF-8') . '.');
                
                include 'send_email.php';
                $email_sent = sendEmail($email, 'forgot_password', [
                    'reset_link' => $reset_link,
                    'login' => $user_data['login'],
                    'formatted_username' => $formatted_username
                ], $mysqli);
                
                if ($email_sent) {
                    echo json_encode(['success' => true, 'message' => 'Инструкции по восстановлению пароля отправлены на вашу почту']);
                } else {
                    $cleanup_stmt = $mysqli->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
                    $cleanup_stmt->bind_param("s", $reset_token);
                    $cleanup_stmt->execute();
                    $cleanup_stmt->close();
                    
                    echo json_encode(['success' => false, 'message' => 'Ошибка при отправке письма. Попробуйте позже.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Ошибка при создании токена сброса']);
            }
            $insert_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Пользователь с такими данными не найден']);
        }
    }
?>