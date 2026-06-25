<?php
require_once 'forum-config.php';

$topic_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$topic_id) {
    header('Location: forum.php');
    exit;
}

$topic = getTopic($topic_id);
if (!$topic) {
    header('Location: forum.php');
    exit;
}

$user = getUserData();
$is_admin = isAdmin();

// Обработка отправки сообщения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $content = trim($_POST['content'] ?? '');
    if (strlen($content) >= 3) {
        try {
            createPost($topic_id, $user['id'], $user['username'], $content);
            header("Location: forum-topic.php?id=$topic_id");
            exit;
        } catch (Exception $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    } else {
        $error = 'Сообщение должно быть минимум 3 символа';
    }
}

// Получаем сообщения
$posts = getPosts($topic_id);

// Увеличиваем просмотры
$stmt = $pdo->prepare("UPDATE forum_topics SET views = views + 1 WHERE id = ?");
$stmt->execute([$topic_id]);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($topic['title']); ?> · Форум</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .topic-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .topic-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px 0;
            border-bottom: 1px solid rgba(0, 255, 200, 0.1);
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .topic-header .topic-info h1 {
            font-size: 2rem;
            color: #ccddee;
            margin-bottom: 5px;
        }

        .topic-header .topic-info .meta {
            color: #667788;
            font-size: 0.8rem;
            font-family: 'Courier New', monospace;
        }

        .topic-header .topic-info .meta span {
            margin-right: 15px;
        }

        .topic-header .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-secondary {
            padding: 8px 20px;
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 255, 200, 0.1);
            color: #8899aa;
            text-decoration: none;
            transition: 0.3s;
            font-size: 0.9rem;
        }

        .btn-secondary:hover {
            border-color: #00ffc8;
            color: #00ffc8;
        }

        .btn-danger {
            padding: 8px 20px;
            border-radius: 30px;
            background: rgba(255, 0, 100, 0.05);
            border: 1px solid rgba(255, 0, 100, 0.1);
            color: #ff4466;
            text-decoration: none;
            transition: 0.3s;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .btn-danger:hover {
            background: rgba(255, 0, 100, 0.1);
            border-color: #ff4466;
        }

        /* ===== СООБЩЕНИЯ ===== */
        .post {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(0, 255, 200, 0.04);
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .post:hover {
            border-color: rgba(0, 255, 200, 0.08);
        }

        .post .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .post .post-author {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .post .post-author .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00ffc8, #ff00c8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            color: #0a0a0f;
            font-weight: 700;
        }

        .post .post-author .name {
            color: #ccddee;
            font-weight: 600;
        }

        .post .post-author .role {
            font-size: 0.55rem;
            padding: 2px 8px;
            border-radius: 12px;
            background: rgba(0, 255, 200, 0.08);
            color: #00ffc8;
        }

        .post .post-author .role.admin {
            background: rgba(255, 200, 0, 0.12);
            color: #ffcc00;
            border: 1px solid rgba(255, 200, 0, 0.15);
        }

        .post .post-date {
            color: #445566;
            font-size: 0.7rem;
            font-family: 'Courier New', monospace;
        }

        .post .post-content {
            color: #ccddee;
            line-height: 1.8;
            word-wrap: break-word;
        }

        .post .post-content p {
            margin-bottom: 10px;
        }

        .post .post-content p:last-child {
            margin-bottom: 0;
        }

        .post .post-footer {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.03);
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .post .post-footer .edited {
            color: #445566;
            font-size: 0.7rem;
            font-style: italic;
        }

        .post .post-footer button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 12px;
            transition: 0.3s;
            font-family: inherit;
        }

        .post .post-footer .edit-btn {
            color: #00ffc8;
        }

        .post .post-footer .edit-btn:hover {
            background: rgba(0, 255, 200, 0.08);
        }

        .post .post-footer .delete-btn {
            color: #ff4466;
        }

        .post .post-footer .delete-btn:hover {
            background: rgba(255, 68, 102, 0.08);
        }

        /* ===== ФОРМА ОТВЕТА ===== */
        .reply-form {
            margin-top: 30px;
            padding: 25px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(0, 255, 200, 0.06);
            border-radius: 16px;
        }

        .reply-form h3 {
            color: #8899aa;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .reply-form textarea {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(0, 255, 200, 0.08);
            border-radius: 12px;
            color: #ccddee;
            font-size: 1rem;
            font-family: inherit;
            transition: 0.3s;
            outline: none;
            resize: vertical;
            min-height: 100px;
        }

        .reply-form textarea:focus {
            border-color: #00ffc8;
            box-shadow: 0 0 20px rgba(0, 255, 200, 0.05);
        }

        .reply-form .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .btn-primary {
            padding: 10px 24px;
            background: linear-gradient(135deg, #00ffc8, #00ccaa);
            color: #0a0a0f;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            font-family: inherit;
            font-size: 0.95rem;
        }

        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(0, 255, 200, 0.2);
        }

        .error-message {
            background: rgba(255, 0, 100, 0.08);
            border: 1px solid rgba(255, 0, 100, 0.1);
            border-radius: 12px;
            padding: 12px 16px;
            color: #ff4466;
            margin-bottom: 15px;
        }

        .login-to-reply {
            text-align: center;
            padding: 30px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(0, 255, 200, 0.06);
            border-radius: 16px;
            margin-top: 30px;
        }

        .login-to-reply .icon {
            font-size: 3rem;
            display: block;
            margin-bottom: 10px;
        }

        .login-to-reply a {
            color: #00ffc8;
            text-decoration: none;
        }

        .login-to-reply a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .topic-header {
                flex-direction: column;
            }
            .topic-header .topic-info h1 {
                font-size: 1.5rem;
            }
            .post .post-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="topic-container">
            <!-- ===== ШАПКА ТЕМЫ ===== -->
            <div class="topic-header">
                <div class="topic-info">
                    <h1><?php echo htmlspecialchars($topic['title']); ?></h1>
                    <div class="meta">
                        <span>👤 <?php echo htmlspecialchars($topic['username'] ?? 'Удалён'); ?></span>
                        <span>📅 <?php echo date('d.m.Y H:i', strtotime($topic['created_at'])); ?></span>
                        <span>👁️ <?php echo $topic['views']; ?> просмотров</span>
                        <span>💬 <?php echo $topic['replies']; ?> ответов</span>
                    </div>
                </div>
                <div class="actions">
                    <a href="forum.php" class="btn-secondary">← Назад</a>
                    <?php if ($is_admin): ?>
                        <button onclick="deleteTopic(<?php echo $topic['id']; ?>)" class="btn-danger">🗑️ Удалить тему</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ===== СООБЩЕНИЯ ===== -->
            <?php foreach ($posts as $post): ?>
                <div class="post" id="post-<?php echo $post['id']; ?>">
                    <div class="post-header">
                        <div class="post-author">
                            <div class="avatar">
                                <?php echo substr(htmlspecialchars($post['username'] ?? '?'), 0, 1); ?>
                            </div>
                            <span class="name"><?php echo htmlspecialchars($post['username'] ?? 'Удалён'); ?></span>
                            <?php if ($post['user_id'] === 1): ?>
                                <span class="role admin">👑 ADMIN</span>
                            <?php endif; ?>
                        </div>
                        <span class="post-date">
                            <?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?>
                            <?php if ($post['is_edited']): ?>
                                <span style="color:#445566; font-style:italic;">(ред.)</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                    </div>
                    <?php if ($is_admin || $post['user_id'] === ($user['id'] ?? -1)): ?>
                        <div class="post-footer">
                            <?php if ($is_admin || $post['user_id'] === ($user['id'] ?? -1)): ?>
                                <button class="edit-btn" onclick="editPost(<?php echo $post['id']; ?>)">✏️ Редактировать</button>
                            <?php endif; ?>
                            <?php if ($is_admin): ?>
                                <button class="delete-btn" onclick="deletePost(<?php echo $post['id']; ?>)">🗑️ Удалить</button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- ===== ФОРМА ОТВЕТА ===== -->
            <?php if (isLoggedIn() && $topic['status'] !== 'closed'): ?>
                <div class="reply-form">
                    <h3>💬 Написать ответ</h3>
                    <?php if (isset($error)): ?>
                        <div class="error-message">❌ <?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <textarea name="content" placeholder="Введите ваше сообщение..." required minlength="3"></textarea>
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">🚀 Отправить</button>
                        </div>
                    </form>
                </div>
            <?php elseif ($topic['status'] === 'closed'): ?>
                <div class="login-to-reply">
                    <span class="icon">🔒</span>
                    <p>Эта тема закрыта для ответов.</p>
                </div>
            <?php else: ?>
                <div class="login-to-reply">
                    <span class="icon">🔐</span>
                    <p>Чтобы ответить, <a href="auth.html">войдите</a> или <a href="auth.html">зарегистрируйтесь</a>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // ===== УДАЛЕНИЕ ТЕМЫ =====
        function deleteTopic(id) {
            if (!confirm('Вы уверены, что хотите удалить эту тему и все сообщения?')) return;
            
            fetch('forum-delete-topic.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ topic_id: id })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'forum.php';
                } else {
                    alert('Ошибка: ' + data.message);
                }
            })
            .catch(e => alert('Ошибка: ' + e.message));
        }

        // ===== УДАЛЕНИЕ СООБЩЕНИЯ =====
        function deletePost(id) {
            if (!confirm('Удалить это сообщение?')) return;
            
            fetch('forum-delete-post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: id })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Ошибка: ' + data.message);
                }
            })
            .catch(e => alert('Ошибка: ' + e.message));
        }

        // ===== РЕДАКТИРОВАНИЕ СООБЩЕНИЯ =====
        function editPost(id) {
            const newContent = prompt('Введите новый текст сообщения:');
            if (newContent === null) return;
            if (newContent.trim().length < 3) {
                alert('Сообщение должно быть минимум 3 символа');
                return;
            }
            
            fetch('forum-edit-post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    post_id: id, 
                    content: newContent.trim() 
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Ошибка: ' + data.message);
                }
            })
            .catch(e => alert('Ошибка: ' + e.message));
        }

        console.log('%c💬 Тема: <?php echo addslashes($topic['title']); ?>', 'font-size:16px; color:#00ffc8;');
        console.log('%c👑 Админ может удалять и редактировать сообщения', 'color:#ffcc00;');
    </script>
</body>
</html>