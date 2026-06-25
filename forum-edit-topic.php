<?php
require_once 'forum-config.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$topic_id = intval($data['topic_id'] ?? 0);
$title = trim($data['title'] ?? '');

if (!$topic_id || empty($title) || strlen($title) < 3) {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE forum_topics SET title = ? WHERE id = ?");
    $stmt->execute([$title, $topic_id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>