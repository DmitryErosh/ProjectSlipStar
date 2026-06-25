<?php
require_once 'config.php';

// Функции для форума
function getTopics($limit = 50, $offset = 0) {
    global $pdo;
    // Используем прямое вставление чисел, т.к. LIMIT и OFFSET не работают с плейсхолдерами в некоторых версиях MySQL
    $stmt = $pdo->prepare("
        SELECT t.*, u.username as author 
        FROM forum_topics t
        LEFT JOIN users u ON t.user_id = u.id
        ORDER BY 
            CASE WHEN t.status = 'pinned' THEN 0 ELSE 1 END,
            t.last_activity DESC
        LIMIT " . intval($limit) . " OFFSET " . intval($offset) . "
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getTopic($id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT t.*, u.username as author 
        FROM forum_topics t
        LEFT JOIN users u ON t.user_id = u.id
        WHERE t.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getPosts($topic_id, $limit = 50, $offset = 0) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, u.username as author 
        FROM forum_posts p
        LEFT JOIN users u ON p.user_id = u.id
        WHERE p.topic_id = ?
        ORDER BY p.created_at ASC
        LIMIT " . intval($limit) . " OFFSET " . intval($offset) . "
    ");
    $stmt->execute([$topic_id]);
    return $stmt->fetchAll();
}

function createTopic($title, $description, $user_id, $username) {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO forum_topics (title, description, user_id, username) 
        VALUES (?, ?, ?, ?)
    ");
    return $stmt->execute([$title, $description, $user_id, $username]);
}

function createPost($topic_id, $user_id, $username, $content) {
    global $pdo;
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("
            INSERT INTO forum_posts (topic_id, user_id, username, content) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$topic_id, $user_id, $username, $content]);
        
        $stmt = $pdo->prepare("
            UPDATE forum_topics 
            SET replies = replies + 1, last_activity = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$topic_id]);
        
        $pdo->commit();
        return true;
    } catch(Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function deleteTopic($topic_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM forum_topics WHERE id = ?");
    return $stmt->execute([$topic_id]);
}

function deletePost($post_id) {
    global $pdo;
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT topic_id FROM forum_posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $topic = $stmt->fetch();
        
        if (!$topic) {
            throw new Exception("Пост не найден");
        }
        
        $stmt = $pdo->prepare("DELETE FROM forum_posts WHERE id = ?");
        $stmt->execute([$post_id]);
        
        $stmt = $pdo->prepare("
            UPDATE forum_topics 
            SET replies = replies - 1 
            WHERE id = ?
        ");
        $stmt->execute([$topic['topic_id']]);
        
        $pdo->commit();
        return true;
    } catch(Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function updatePost($post_id, $content) {
    global $pdo;
    $stmt = $pdo->prepare("
        UPDATE forum_posts 
        SET content = ?, updated_at = NOW(), is_edited = 1 
        WHERE id = ?
    ");
    return $stmt->execute([$content, $post_id]);
}

function getTotalTopics() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM forum_topics");
    return $stmt->fetchColumn();
}

function getTotalPosts($topic_id = null) {
    global $pdo;
    if ($topic_id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM forum_posts WHERE topic_id = ?");
        $stmt->execute([$topic_id]);
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM forum_posts");
    }
    return $stmt->fetchColumn();
}
?>