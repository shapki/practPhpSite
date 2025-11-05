<?php
    $host = 'localhost';
    $dbname = 'shapkin_praktikum';
    $username = 'shapkin_shapkin';
    $password = '126-PkghTemp-1235';

    try {
        $mysqli = new mysqli($host, $username, $password, $dbname);
        
        if ($mysqli->connect_error) {
            throw new Exception('Ошибка подключения к Базе Данных: ' . $mysqli->connect_error);
        }
        
        $mysqli->set_charset("utf8mb4");
        
    } catch (Exception $e) {
        echo json_encode(['isError' => false, 'message' => 'Ошибка подключения к Базе Данных']);
        exit();
    }
?>