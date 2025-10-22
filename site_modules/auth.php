<?php
    header('Content-Type: application/json');

    include 'db_connect.php';

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
        
        $stmt = $mysqli->prepare("SELECT id, login, password, first_name, last_name, administrator, foto FROM user WHERE login = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($id, $login, $hashed_password, $first_name, $last_name, $administrator, $foto);
        
        if ($stmt->fetch() && password_verify($password, $hashed_password)) {
            session_start();
            $_SESSION['user_id']        = $id;
            $_SESSION['username']       = $login;
            $_SESSION['first_name']     = $first_name;
            $_SESSION['last_name']      = $last_name;
            $_SESSION['is_admin']       = $administrator;
            $_SESSION['foto']           = $foto;
            
            if ($remember) {
                setcookie('remember_user', $id, time() + (30 * 24 * 60 * 60), '/');
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
        
        $stmt = $mysqli->prepare("SELECT id FROM user WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows === 0) {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Пользователь с таким логином не найден']);
            return;
        }
        $stmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Инструкции по восстановлению пароля отправлены на вашу почту']);
    }
?>