<?php
    session_start();

    // Уничтожаем все данные сессии
    $_SESSION = array();

    // Если требуется уничтожить куки, удаляем и их
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Уничтожаем сессию
    session_destroy();

    // Удаляем куки запоминания пользователя
    setcookie('remember_user', '', time() - 3600, '/');

    // Перенаправляем на главную страницу
    header('Location: /');
    exit();
?>