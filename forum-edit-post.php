<?php
require_once 'forum-config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$post_id = intval($data['post_id'] ?? 0);
$content = trim($data['content'] ?? '');

if (!$post_id || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Недостаточно данных']);
    exit;
}

// Проверяем права
$user = getUserData();
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit;
}

// Получаем автора поста
$stmt = $pdo->prepare("SELECT user_id FROM forum_posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    echo json_encode(['success' => false, 'message' => 'Пост не найден']);
    exit;
}

// Только админ или автор может редактировать
if (!isAdmin() && $post['user_id'] !== $user['id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'У вас нет прав на редактирование']);
    exit;
}

try {
    updatePost($post_id, $content);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>