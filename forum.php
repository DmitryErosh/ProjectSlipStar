<?php
require_once 'forum-config.php';

// Получаем страницу
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$topics = getTopics($per_page, $offset);
$total_topics = getTotalTopics();
$total_pages = ceil($total_topics / $per_page);

$is_admin = isAdmin();
$user = getUserData();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форум · Slip_Star</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .forum-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .forum-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(0, 255, 200, 0.1);
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .forum-header h1 {
            font-size: 2rem;
            background: linear-gradient(135deg, #00ffc8, #ff00c8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .forum-header .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .forum-header .actions a {
            padding: 8px 20px;
            border-radius: 30px;
            text-decoration: none;
            transition: 0.3s;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00ffc8, #00ccaa);
            color: #0a0a0f;
            font-weight: 600;
        }

        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(0, 255, 200, 0.2);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 255, 200, 0.1);
            color: #8899aa;
        }

        .btn-secondary:hover {
            border-color: #00ffc8;
            color: #00ffc8;
        }

        /* ===== ТАБЛИЦА ТЕМ ===== */
        .forum-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .forum-table th {
            text-align: left;
            color: #667788;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            padding: 12px 15px;
            border-bottom: 1px solid rgba(0, 255, 200, 0.05);
            font-weight: 400;
        }

        .forum-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(0, 255, 200, 0.03);
            vertical-align: middle;
        }

        .forum-table tr:hover td {
            background: rgba(0, 255, 200, 0.02);
        }

        .forum-table .topic-title {
            color: #ccddee;
            text-decoration: none;
            font-size: 1.05rem;
            font-weight: 500;
            transition: 0.3s;
        }

        .forum-table .topic-title:hover {
            color: #00ffc8;
        }

        .forum-table .topic-title .pinned-badge {
            font-size: 0.6rem;
            padding: 2px 8px;
            border-radius: 12px;
            background: rgba(255, 200, 0, 0.12);
            color: #ffcc00;
            margin-left: 8px;
            border: 1px solid rgba(255, 200, 0, 0.15);
        }

        .forum-table .topic-description {
            color: #667788;
            font-size: 0.85rem;
            display: block;
            margin-top: 4px;
        }

        .forum-table .topic-meta {
            color: #445566;
            font-size: 0.7rem;
            font-family: 'Courier New', monospace;
        }

        .forum-table .topic-author {
            color: #8899aa;
            font-size: 0.8rem;
        }

        .forum-table .topic-stats {
            text-align: center;
            color: #667788;
            font-size: 0.8rem;
            min-width: 80px;
        }

        .forum-table .topic-stats .num {
            font-weight: 600;
            color: #ccddee;
        }

        .forum-table .admin-actions {
            display: flex;
            gap: 8px;
            margin-top: 5px;
        }

        .forum-table .admin-actions button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.8rem;
            padding: 2px 8px;
            border-radius: 12px;
            transition: 0.3s;
            font-family: inherit;
        }

        .forum-table .admin-actions .edit-btn {
            color: #00ffc8;
        }

        .forum-table .admin-actions .edit-btn:hover {
            background: rgba(0, 255, 200, 0.08);
        }

        .forum-table .admin-actions .delete-btn {
            color: #ff4466;
        }

        .forum-table .admin-actions .delete-btn:hover {
            background: rgba(255, 68, 102, 0.08);
        }

        /* ===== ПАГИНАЦИЯ ===== */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
            margin: 30px 0;
        }

        .pagination a {
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid rgba(0, 255, 200, 0.06);
            color: #667788;
            text-decoration: none;
            transition: 0.3s;
            font-size: 0.85rem;
        }

        .pagination a:hover {
            border-color: #00ffc8;
            color: #00ffc8;
        }

        .pagination a.active {
            background: rgba(0, 255, 200, 0.08);
            border-color: #00ffc8;
            color: #00ffc8;
        }

        /* ===== НЕТ ТЕМ ===== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #445566;
        }

        .empty-state .icon {
            font-size: 4rem;
            display: block;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #8899aa;
            margin-bottom: 10px;
        }

        /* ===== АДАПТИВ ===== */
        @media (max-width: 768px) {
            .forum-table {
                font-size: 0.85rem;
            }
            .forum-table td, .forum-table th {
                padding: 10px 8px;
            }
            .forum-table .topic-stats {
                min-width: 60px;
            }
            .forum-header {
                flex-direction: column;
                align-items: stretch;
            }
            .forum-header .actions {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .forum-table .topic-description {
                display: none;
            }
            .forum-table .topic-author {
                font-size: 0.65rem;
            }
        }

        /* ===== СТАТУС В НАВИГАЦИИ ===== */
        .nav-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 12px;
            background: rgba(0, 255, 200, 0.04);
            border: 1px solid rgba(0, 255, 200, 0.06);
            border-radius: 20px;
            font-size: 0.75rem;
            font-family: 'Courier New', monospace;
            margin-left: 8px;
        }

        .nav-status .status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #00ffc8;
            box-shadow: 0 0 10px rgba(0, 255, 200, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .nav-status .username {
            color: #ccddee;
            font-weight: 600;
        }

        .nav-status .role-badge {
            font-size: 0.6rem;
            padding: 2px 8px;
            border-radius: 12px;
            background: rgba(0, 255, 200, 0.08);
            color: #00ffc8;
        }

        .nav-status .role-badge.admin {
            background: rgba(255, 200, 0, 0.12);
            color: #ffcc00;
            border: 1px solid rgba(255, 200, 0, 0.15);
        }

        .nav-status .logout-link {
            color: #ff4466;
            text-decoration: none;
            font-size: 0.7rem;
            transition: 0.3s;
            margin-left: 4px;
        }

        .nav-status .logout-link:hover {
            color: #ff6688;
            text-shadow: 0 0 20px rgba(255, 0, 100, 0.2);
        }

        .topic-link {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .topic-link .topic-title-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .topic-link .topic-title-wrap .topic-status {
            font-size: 0.55rem;
            padding: 2px 8px;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .topic-link .topic-title-wrap .topic-status.closed {
            background: rgba(255, 0, 0, 0.08);
            color: #ff4466;
            border: 1px solid rgba(255, 0, 0, 0.1);
        }

        .topic-link .topic-title-wrap .topic-status.pinned {
            background: rgba(255, 200, 0, 0.08);
            color: #ffcc00;
            border: 1px solid rgba(255, 200, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="forum-container">
            <!-- ===== ШАПКА ===== -->
            <div class="forum-header">
                <h1>💬 Форум</h1>
                <div class="actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="forum-new-topic.php" class="btn-primary">➕ Новая тема</a>
                    <?php endif; ?>
                    <a href="index.html" class="btn-secondary">← На главную</a>
                </div>
            </div>

            <!-- ===== ТАБЛИЦА ===== -->
            <?php if (empty($topics)): ?>
                <div class="empty-state">
                    <span class="icon">📭</span>
                    <h3>Пока нет ни одной темы</h3>
                    <p style="color:#667788;">Будьте первым, кто создаст тему!</p>
                    <?php if (isLoggedIn()): ?>
                        <a href="forum-new-topic.php" class="btn-primary" style="display:inline-block; margin-top:15px;">➕ Создать тему</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <table class="forum-table">
                    <thead>
                        <tr>
                            <th>Тема</th>
                            <th style="text-align:center; width:80px;">Ответы</th>
                            <th style="width:120px;">Автор</th>
                            <?php if ($is_admin): ?>
                                <th style="width:100px;">Управление</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topics as $topic): ?>
                            <tr>
                                <td>
                                    <div class="topic-link">
                                        <div class="topic-title-wrap">
                                            <a href="forum-topic.php?id=<?php echo $topic['id']; ?>" class="topic-title">
                                                <?php echo htmlspecialchars($topic['title']); ?>
                                            </a>
                                            <?php if ($topic['status'] === 'pinned'): ?>
                                                <span class="topic-status pinned">📌 Закреплена</span>
                                            <?php endif; ?>
                                            <?php if ($topic['status'] === 'closed'): ?>
                                                <span class="topic-status closed">🔒 Закрыта</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($topic['description'])): ?>
                                            <span class="topic-description"><?php echo htmlspecialchars($topic['description']); ?></span>
                                        <?php endif; ?>
                                        <span class="topic-meta">
                                            <?php echo date('d.m.Y H:i', strtotime($topic['created_at'])); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="topic-stats">
                                    <span class="num"><?php echo $topic['replies']; ?></span>
                                </td>
                                <td class="topic-author">
                                    <?php echo htmlspecialchars($topic['username'] ?? 'Удалён'); ?>
                                </td>
                                <?php if ($is_admin): ?>
                                    <td>
                                        <div class="admin-actions">
                                            <button class="edit-btn" onclick="editTopic(<?php echo $topic['id']; ?>)">✏️</button>
                                            <button class="delete-btn" onclick="deleteTopic(<?php echo $topic['id']; ?>)">🗑️</button>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- ===== ПАГИНАЦИЯ ===== -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // ===== УДАЛЕНИЕ ТЕМЫ (ТОЛЬКО ДЛЯ АДМИНА) =====
        function deleteTopic(id) {
            if (!confirm('Вы уверены, что хотите удалить эту тему и все сообщения в ней?')) return;
            
            fetch('forum-delete-topic.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ topic_id: id })
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

        // ===== РЕДАКТИРОВАНИЕ ТЕМЫ (ТОЛЬКО ДЛЯ АДМИНА) =====
        function editTopic(id) {
            const newTitle = prompt('Введите новый заголовок темы:');
            if (newTitle === null) return;
            if (newTitle.trim().length < 3) {
                alert('Заголовок должен быть минимум 3 символа');
                return;
            }
            
            fetch('forum-edit-topic.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    topic_id: id, 
                    title: newTitle.trim() 
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

        console.log('%c💬 Форум загружен', 'font-size:18px; font-weight:bold; color:#00ffc8;');
        console.log('%c👑 Админ может удалять и редактировать темы', 'color:#ffcc00;');
    </script>
</body>
</html>