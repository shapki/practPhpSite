<?php
    session_start();
    header('Content-Type: application/json');

    if (isset($_SESSION['user_id'])) {
        // Пользователь авторизован
        $response = [
            'logged_in' => true,
            'user_id' => $_SESSION['user_id'],
            'first_name' => $_SESSION['first_name'] ?? '',
            'last_name' => $_SESSION['last_name'] ?? '',
            'foto' => $_SESSION['foto'] ?? '',
            'username' => $_SESSION['username'] ?? ''
        ];
    } else {
        // Пользователь не авторизован
        $response = [
            'logged_in' => false
        ];
    }

    echo json_encode($response);
?>