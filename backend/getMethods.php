<?php

function getUsers($connect) {
    $users = mysqli_query($connect, "SELECT * FROM users");
    $usersList = [];
    while ($user = mysqli_fetch_assoc($users)) {
        $usersList[] = $user;
    }
    return json_encode($usersList);
}

function getUserById($connect, $id) {
    $id = mysqli_real_escape_string($connect, $id);
    
    $user = mysqli_query($connect, "SELECT * FROM users WHERE id = '$id'");
    
    if (!$user) {
        http_response_code(404);
        return json_encode(['error' => 'Ошибка запроса']);
    }
    
    $userData = mysqli_fetch_assoc($user);
    
    if (!$userData) {
        return json_encode(['error' => 'Пользователь не найден']);
    }
    
    return json_encode($userData);
}

function getRoutes($connect, $start_point, $end_point, $departure_date) {
    // Проверка входных данных
    if (!$start_point || !$end_point || !$departure_date) {
        return json_encode([
            'status' => false,
            'error' => 'Не все параметры указаны'
        ]);
    }

    $query = "SELECT * FROM routes 
              WHERE start_point = ? 
              AND end_point = ? 
              AND departure_date = ?
              AND is_active = 1
              ORDER BY total_time ASC";
              
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'sss', $start_point, $end_point, $departure_date);
    
    if (!mysqli_stmt_execute($stmt)) {
        return json_encode([
            'status' => false,
            'error' => 'Ошибка при выполнении запроса'
        ]);
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
    $routes = [];
    while ($route = mysqli_fetch_assoc($result)) {
        $route['stops'] = json_decode($route['stops'], true);
        $routes[] = $route;
    }
    
    return json_encode([
        'status' => true,
        'data' => $routes
    ]);
}

function getStops($connect) {
    $query = "SELECT * FROM stops WHERE is_active = 1";
    $result = mysqli_query($connect, $query);
    
    $stops = [];
    while ($stop = mysqli_fetch_assoc($result)) {
        $stop['coordinates'] = json_decode($stop['coordinates'], true);
        $stops[] = $stop;
    }
    
    return json_encode($stops);
}

function getAllRoutes($connect) {
    $query = "SELECT * FROM routes WHERE is_active = 1 ORDER BY departure_date, departure_time";
    $result = mysqli_query($connect, $query);
    
    if (!$result) {
        return json_encode([
            'status' => false,
            'error' => 'Ошибка при получении маршрутов: ' . mysqli_error($connect)
        ]);
    }
    
    $routes = [];
    while ($route = mysqli_fetch_assoc($result)) {
        $routes[] = $route;
    }
    
    return json_encode([
        'status' => true,
        'data' => $routes
    ]);
}