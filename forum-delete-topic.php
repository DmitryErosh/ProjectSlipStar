<?php
require_once 'forum-config.php';

header('Content-Type: application/json');

// Проверяем админа
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$topic_id = intval($data['topic_id'] ?? 0);

if (!$topic_id) {
    echo json_encode(['success' => false, 'message' => 'ID темы не указан']);
    exit;
}

try {
    deleteTopic($topic_id);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>