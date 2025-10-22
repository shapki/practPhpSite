<?php
    session_start();
    header('Content-Type: application/json; charset=utf-8');

    ini_set('display_errors', 0);
    ini_set('log_errors', 1);

    function sendJsonResponse($success, $message = '') {
        echo json_encode([
            'success' => $success,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if (!isset($_SESSION['user_id'])) {
        sendJsonResponse(false, 'Не авторизован');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(false, 'Неверный метод запроса');
    }

    try {
        include 'db_connect.php';
    } catch (Exception $e) {
        sendJsonResponse(false, 'Ошибка подключения к базе данных');
    }

    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT) ?? 0;
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING) ?? '';

    if ($user_id <= 0) {
        sendJsonResponse(false, 'Неверный ID пользователя');
    }

    if (empty($action)) {
        sendJsonResponse(false, 'Не указано действие');
    }

    if ($_SESSION['user_id'] != $user_id && (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1)) {
        sendJsonResponse(false, 'Нет прав для редактирования');
    }

    try {
        switch ($action) {
            case 'update_name':
                updateName($mysqli, $user_id);
                break;
            case 'update_email':
                updateEmail($mysqli, $user_id);
                break;
            case 'update_company':
                updateCompany($mysqli, $user_id);
                break;
            case 'update_settings':
                updateSettings($mysqli, $user_id);
                break;
            case 'upload_avatar':
                uploadAvatar($mysqli, $user_id);
                break;
            case 'delete_avatar':
                deleteAvatar($mysqli, $user_id);
                break;
            default:
                sendJsonResponse(false, 'Неизвестное действие: ' . $action);
        }
    } catch (Exception $e) {
        sendJsonResponse(false, 'Произошла ошибка: ' . $e->getMessage());
    }

    function updateName($mysqli, $user_id) {
        $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING) ?? '';
        $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING) ?? '';
        
        $first_name = trim($first_name);
        $last_name = trim($last_name);
        
        if (empty($first_name) || empty($last_name)) {
            sendJsonResponse(false, 'Имя и фамилия не могут быть пустыми');
        }
        
        $stmt = $mysqli->prepare("UPDATE user SET first_name = ?, last_name = ? WHERE id = ?");
        $stmt->bind_param("ssi", $first_name, $last_name, $user_id);
        $result = $stmt->execute();
        $stmt->close();
        
        if ($result) {
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            sendJsonResponse(true, 'Имя успешно обновлено');
        } else {
            sendJsonResponse(false, 'Не удалось обновить имя');
        }
    }

    function updateEmail($mysqli, $user_id) {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? '';
        
        if (!empty($email) && $email === false) {
            sendJsonResponse(false, 'Неверный формат email');
        }
        
        $stmt = $mysqli->prepare("UPDATE user SET e_mail = ? WHERE id = ?");
        $stmt->bind_param("si", $email, $user_id);
        $result = $stmt->execute();
        $stmt->close();
        
        if ($result) {
            $_SESSION['email'] = $email;
            sendJsonResponse(true, 'Почта успешно обновлена');
        } else {
            sendJsonResponse(false, 'Не удалось обновить почту');
        }
    }

    function updateCompany($mysqli, $user_id) {
        $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_STRING) ?? '';
        $company = trim($company);
        
        $stmt = $mysqli->prepare("UPDATE user SET company = ? WHERE id = ?");
        $stmt->bind_param("si", $company, $user_id);
        $result = $stmt->execute();
        $stmt->close();
        
        if ($result) {
            $_SESSION['company'] = $company;
            sendJsonResponse(true, 'Компания успешно обновлена');
        } else {
            sendJsonResponse(false, 'Не удалось обновить компанию');
        }
    }

    function updateSettings($mysqli, $user_id) {
        $new_login = filter_input(INPUT_POST, 'new_login', FILTER_SANITIZE_STRING) ?? '';
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        
        $new_login = trim($new_login);
        
        if (empty($current_password)) {
            sendJsonResponse(false, 'Введите текущий пароль');
        }
        
        $stmt = $mysqli->prepare("SELECT password FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($db_password);
        $stmt->fetch();
        $stmt->close();
        
        if (!$db_password || !password_verify($current_password, $db_password)) {
            sendJsonResponse(false, 'Неверный текущий пароль');
        }
        
        $updates = [];
        $params = [];
        $types = "";
        
        if (!empty($new_login)) {
            $check_stmt = $mysqli->prepare("SELECT id FROM user WHERE login = ? AND id != ?");
            $check_stmt->bind_param("si", $new_login, $user_id);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $check_stmt->close();
                sendJsonResponse(false, 'Этот логин уже занят');
            }
            $check_stmt->close();
            
            $updates[] = "login = ?";
            $params[] = $new_login;
            $types .= "s";
        }
        
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $updates[] = "password = ?";
            $params[] = $hashed_password;
            $types .= "s";
        }
        
        if (empty($updates)) {
            sendJsonResponse(false, 'Не указаны данные для обновления');
        }
        
        $params[] = $user_id;
        $types .= "i";
        
        $sql = "UPDATE user SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $result = $stmt->execute();
        $stmt->close();
        
        if ($result) {
            if (!empty($new_login)) {
                $_SESSION['username'] = $new_login;
            }
            sendJsonResponse(true, 'Настройки успешно обновлены');
        } else {
            sendJsonResponse(false, 'Не удалось обновить настройки');
        }
    }

    function uploadAvatar($mysqli, $user_id) {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            sendJsonResponse(false, 'Ошибка загрузки файла');
        }
        
        $uploadedFile = $_FILES['avatar'];
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $uploadedFile['tmp_name']);
        finfo_close($fileInfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            sendJsonResponse(false, 'Разрешены только JPG, PNG и GIF файлы');
        }
        
        if ($uploadedFile['size'] > 5 * 1024 * 1024) {
            sendJsonResponse(false, 'Размер файла не должен превышать 5MB');
        }
        
        $stmt = $mysqli->prepare("SELECT foto FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($current_foto);
        $stmt->fetch();
        $stmt->close();
        
        if (!empty($current_foto) && file_exists($current_foto)) {
            deleteUserAvatars($user_id, $current_foto);
        }
        
        $avatarDir = '../uploads/avatars/';
        if (!file_exists($avatarDir)) {
            if (!mkdir($avatarDir, 0755, true)) {
                sendJsonResponse(false, 'Не удалось создать папку для аватаров');
            }
        }
        
        $timestamp = date('d-m-Y_H-i-s');
        $fileExtension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
        $fileName = $user_id . '_' . $timestamp . '.' . $fileExtension;
        $filePath = $avatarDir . $fileName;
        
        if (move_uploaded_file($uploadedFile['tmp_name'], $filePath)) {
            $stmt = $mysqli->prepare("UPDATE user SET foto = ? WHERE id = ?");
            $stmt->bind_param("si", $filePath, $user_id);
            if ($stmt->execute()) {
                $_SESSION['foto'] = $filePath;
                sendJsonResponse(true, 'Аватар успешно загружен');
            } else {
                unlink($filePath);
                sendJsonResponse(false, 'Ошибка при сохранении в базу данных: ' . $mysqli->error);
            }
            $stmt->close();
        } else {
            sendJsonResponse(false, 'Ошибка при сохранении файла. Проверьте права доступа к папке uploads/avatars/');
        }
    }
    
    function deleteAvatar($mysqli, $user_id) {
        $stmt = $mysqli->prepare("SELECT foto FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($current_foto);
        $stmt->fetch();
        $stmt->close();
        
        if (empty($current_foto)) {
            sendJsonResponse(false, 'Аватар не найден');
        }
        
        deleteUserAvatars($user_id, $current_foto);
        
        $stmt = $mysqli->prepare("UPDATE user SET foto = NULL WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $_SESSION['foto'] = null;
            sendJsonResponse(true, 'Аватар успешно удален');
        } else {
            sendJsonResponse(false, 'Ошибка при обновлении базы данных: ' . $mysqli->error);
        }
        $stmt->close();
    }
    
    function deleteUserAvatars($user_id, $currentAvatar) {
        $avatarDir = '../uploads/avatars/';
        
        if (!empty($currentAvatar) && file_exists($currentAvatar)) {
            if (!unlink($currentAvatar)) {
                error_log("Не удалось удалить файл: $currentAvatar");
            }
        }
        
        $pattern = $avatarDir . $user_id . '_*';
        $oldAvatars = glob($pattern);
        foreach ($oldAvatars as $oldAvatar) {
            if (file_exists($oldAvatar) && !unlink($oldAvatar)) {
                error_log("Не удалось удалить старый аватар: $oldAvatar");
            }
        }
    }
?>