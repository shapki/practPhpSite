<?php
    session_start();

    session_unset();
    session_destroy();

    if (isset($_COOKIE['remember_user'])) {
        setcookie('remember_user', '', time() - 3600, '/');
    }

    if (isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Выход выполнен успешно']);
        exit;
    } else {
        header('Location: ../index.php');
        exit;
    }
?>