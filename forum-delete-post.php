<?php
require_once 'forum-config.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$post_id = intval($data['post_id'] ?? 0);

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'ID сообщения не указан']);
    exit;
}

try {
    deletePost($post_id);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>