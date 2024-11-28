<?php
require 'connect.php';
require 'getMethods.php';
require 'postMethods.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];

// Получаем данные из тела запроса для POST-запросов
$jsonData = file_get_contents('php://input');
$postData = json_decode($jsonData, true) ?? [];

switch($method) {
    case 'GET':
        $q = isset($_GET['q']) ? $_GET['q'] : '';
        $separateQuery = explode('/', trim($q, '/'));
        
        switch($separateQuery[0]) {
            case 'users':
                if(isset($separateQuery[1])) {
                    echo getUserById($connect, $separateQuery[1]);
                } else {
                    echo getUsers($connect);
                }
                break;
                
            case 'routes':
                if(isset($separateQuery[1]) && $separateQuery[1] === 'all') {
                    echo getAllRoutes($connect);
                }
                break;
        }
        break;
        
    case "POST":
        $q = isset($_GET['q']) ? $_GET['q'] : '';
        $separateQuery = explode('/', trim($q, '/'));
        
        switch($separateQuery[0]) {
            case 'users':
                if (isset($separateQuery[1]) && $separateQuery[1] === 'login') {
                    echo loginUser($connect, $_POST);
                } else {
                    echo createUser($connect, $_POST);
                }
                break;
                
            case 'routes':
                if (isset($separateQuery[1]) && $separateQuery[1] === 'create') {
                    if (empty($postData)) {
                        error_log("Пустые данные маршрута");
                        echo json_encode(['status' => false, 'error' => 'Нет данных для создания маршрута']);
                        break;
                    }
                    error_log("Данные для создания маршрута: " . json_encode($postData));
                    echo createRoute($connect, $postData);
                } else {
                    if (empty($postData)) {
                        echo json_encode(['error' => 'Нет данных для поиска маршрутов']);
                        break;
                    }
                    echo getRoutes(
                        $connect, 
                        $postData['start_point'], 
                        $postData['end_point'], 
                        $postData['departure_date']
                    );
                }
                break;
                
            case 'orders':
                echo createOrder($connect, $postData);
                break;
        }
        break;
}

