<?php
function createUser($connect, $data) {
    // Подготовка данных с проверкой на существование
    $name = isset($data['name']) ? mysqli_real_escape_string($connect, $data['name']) : '';
    $address = isset($data['address']) ? mysqli_real_escape_string($connect, $data['address']) : '';
    $rto = isset($data['rto']) ? mysqli_real_escape_string($connect, $data['rto']) : 'РТО 022131';
    $inn = isset($data['inn']) ? mysqli_real_escape_string($connect, $data['inn']) : '';
    $kpp = isset($data['kpp']) ? mysqli_real_escape_string($connect, $data['kpp']) : '';
    $account = isset($data['account']) ? mysqli_real_escape_string($connect, $data['account']) : '';
    $bank_name = isset($data['bank_name']) ? mysqli_real_escape_string($connect, $data['bank_name']) : '';
    $bik = isset($data['bik']) ? mysqli_real_escape_string($connect, $data['bik']) : '';
    $curren_account = isset($data['corr_account']) ? mysqli_real_escape_string($connect, $data['corr_account']) : '';
    $fio = isset($data['fio']) ? mysqli_real_escape_string($connect, $data['fio']) : '';
    $phone = isset($data['phone']) ? mysqli_real_escape_string($connect, $data['phone']) : '';
    $email = isset($data['email']) ? mysqli_real_escape_string($connect, $data['email']) : '';
    $password = isset($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : '';

    // Проверка обязательных полей
    if (!$name || !$address || !$inn || !$kpp || !$account || !$bank_name || 
        !$bik || !$fio || !$phone || !$email || !$password) {
        return json_encode([
            'status' => false,
            'error' => 'Заполните все обязательные поля'
        ]);
    }
    
    // Проверка формата РТО
    if (!empty($rto) && !preg_match('/^РТО \d{6}$/', $rto)) {
        return json_encode([
            'status' => false,
            'error' => 'Недействительный номер РТО'
        ]);
    }
    
    $query = "INSERT INTO users (name, address, rto, inn, kpp, account, bank_name, 
              bik, curren_account, fio, phone, email, password) VALUES 
              (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
              
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'sssssssssssss', 
        $name, $address, $rto, $inn, $kpp, $account, $bank_name,
        $bik, $curren_account, $fio, $phone, $email, $password
    );
    
    if (mysqli_stmt_execute($stmt)) {
        return json_encode([
            'status' => true,
            'message' => 'Регистрация успешна'
        ]);
    } else {
        return json_encode([
            'status' => false,
            'error' => 'Ошибка при регистрации: ' . mysqli_error($connect)
        ]);
    }
}

function loginUser($connect, $data) {
    if (!isset($data['inn']) || !isset($data['password'])) {
        return json_encode([
            'status' => false,
            'error' => 'Введите ИНН и пароль'
        ]);
    }

    $inn = mysqli_real_escape_string($connect, $data['inn']);
    $password = $data['password'];
    
    $query = "SELECT * FROM users WHERE inn = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 's', $inn);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password'])) {
            unset($user['password']);
            return json_encode([
                'status' => true,
                'message' => 'Вход выполнен успешно',
                'user' => $user
            ]);
        }
    }
    
    return json_encode([
        'status' => false,
        'error' => 'Неверный ИНН или пароль'
    ]);
}

function createOrder($connect, $data) {
    $user_id = mysqli_real_escape_string($connect, $data['user_id']);
    $route_id = mysqli_real_escape_string($connect, $data['route_id']);
    $selected_stops = json_encode($data['selected_stops']);
    $total_price = mysqli_real_escape_string($connect, $data['total_price']);
    $status = 'pending'; // начальный статус заказа
    
    $query = "INSERT INTO orders (user_id, route_id, selected_stops, total_price, status, created_at) 
              VALUES (?, ?, ?, ?, ?, NOW())";
              
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 'iisss', 
        $user_id, $route_id, $selected_stops, $total_price, $status
    );
    
    if (mysqli_stmt_execute($stmt)) {
        return json_encode([
            'status' => true,
            'message' => 'Заказ успешно создан'
        ]);
    }
    
    return json_encode([
        'status' => false,
        'error' => 'Ошибка при создании заказа'
    ]);
}

function createRoute($connect, $data) {
    error_log("Starting createRoute function with data: " . json_encode($data));
    
    // Проверяем подключение
    if (!$connect) {
        error_log("Database connection failed");
        return json_encode([
            'status' => false,
            'error' => 'Нет подключения к базе данных'
        ]);
    }

    try {
        // Валидация входных данных
        if (empty($data['start_point']) || empty($data['end_point']) || 
            empty($data['departure_date']) || empty($data['departure_time'])) {
            throw new Exception("Отсутствуют обязательные поля");
        }

        // Проверяем существование таблицы
        $table_check = mysqli_query($connect, "SHOW TABLES LIKE 'routes'");
        if (mysqli_num_rows($table_check) == 0) {
            // Создаем таблицу, если она не существует
            $create_table_sql = "
                CREATE TABLE IF NOT EXISTS routes (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    start_point VARCHAR(255) NOT NULL,
                    end_point VARCHAR(255) NOT NULL,
                    departure_date DATE NOT NULL,
                    departure_time TIME NOT NULL,
                    total_time INT NOT NULL,
                    price DECIMAL(10,2) NOT NULL,
                    stops TEXT,
                    is_active TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
            
            if (!mysqli_query($connect, $create_table_sql)) {
                throw new Exception("Ошибка создания таблицы: " . mysqli_error($connect));
            }
        }

        // Начинаем транзакцию
        mysqli_begin_transaction($connect);

        // Подготовка данных с проверкой
        $start_point = isset($data['start_point']) ? $data['start_point'] : '';
        $end_point = isset($data['end_point']) ? $data['end_point'] : '';
        $departure_date = isset($data['departure_date']) ? $data['departure_date'] : '';
        $departure_time = isset($data['departure_time']) ? $data['departure_time'] : '';
        $total_time = isset($data['total_time']) ? (int)$data['total_time'] : 0;
        $price = isset($data['price']) ? (float)$data['price'] : 0.00;
        $stops = isset($data['stops']) ? json_encode($data['stops'], JSON_UNESCAPED_UNICODE) : '[]';
        $is_active = 1;

        // Прямой запрос для отладки
        $direct_query = "
            INSERT INTO routes (
                start_point, end_point, departure_date, departure_time, 
                total_time, price, stops, is_active
            ) VALUES (
                '$start_point', '$end_point', '$departure_date', '$departure_time',
                $total_time, $price, '$stops', $is_active
            )
        ";
        
        error_log("Direct query for debug: " . $direct_query);

        // Используем подготовленный запрос для безопасности
        $query = "
            INSERT INTO routes (
                start_point, end_point, departure_date, departure_time, 
                total_time, price, stops, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = mysqli_prepare($connect, $query);
        if (!$stmt) {
            throw new Exception("Ошибка подготовки запроса: " . mysqli_error($connect));
        }

        $bind_result = mysqli_stmt_bind_param(
            $stmt, 
            'ssssidsi',
            $start_point,
            $end_point,
            $departure_date,
            $departure_time,
            $total_time,
            $price,
            $stops,
            $is_active
        );

        if (!$bind_result) {
            throw new Exception("Ошибка привязки параметров: " . mysqli_stmt_error($stmt));
        }

        // Выполняем запрос
        if (!mysqli_stmt_execute($stmt)) {
            error_log("SQL Error: " . mysqli_stmt_error($stmt));
            throw new Exception("Ошибка выполнения запроса: " . mysqli_stmt_error($stmt));
        }

        $route_id = mysqli_insert_id($connect);
        error_log("Inserted route ID: " . $route_id);

        // Проверяем созданный маршрут
        $check_query = "SELECT * FROM routes WHERE id = ?";
        $check_stmt = mysqli_prepare($connect, $check_query);
        mysqli_stmt_bind_param($check_stmt, 'i', $route_id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        $route_data = mysqli_fetch_assoc($result);

        if (!$route_data) {
            throw new Exception("Маршрут не найден после создания");
        }

        error_log("Created route data: " . json_encode($route_data));

        mysqli_commit($connect);

        return json_encode([
            'status' => true,
            'message' => 'Маршрут успешно создан',
            'data' => $route_data
        ]);

    } catch (Exception $e) {
        mysqli_rollback($connect);
        error_log("Error in createRoute: " . $e->getMessage());
        
        return json_encode([
            'status' => false,
            'error' => $e->getMessage(),
            'debug_data' => [
                'input_data' => $data,
                'mysql_error' => mysqli_error($connect)
            ]
        ]);
    }
}