<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Проверяем, авторизован ли пользователь
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Вы должны быть авторизованы для отправки сообщения'
    ]);
    exit;
}

// Получаем данные
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Нет данных'
    ]);
    exit;
}

// Валидация
$subject = trim($data['subject'] ?? '');
$message = trim($data['message'] ?? '');
$rating = intval($data['rating'] ?? 0);

if (empty($subject) || empty($message)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Заполните тему и сообщение'
    ]);
    exit;
}

if (strlen($message) < 10) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Сообщение должно содержать минимум 10 символов'
    ]);
    exit;
}

try {
    $user = getUserData();
    
    $stmt = $pdo->prepare("
        INSERT INTO feedback_messages (user_id, username, email, subject, message, rating) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $user['id'],
        $user['username'],
        $user['email'],
        $subject,
        $message,
        $rating
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Сообщение отправлено! Спасибо за обратную связь.'
    ]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка базы данных: ' . $e->getMessage()
    ]);
}
?>