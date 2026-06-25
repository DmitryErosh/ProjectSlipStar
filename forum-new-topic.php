<?php
require_once 'forum-config.php';

// Проверяем авторизацию
if (!isLoggedIn()) {
    header('Location: auth.html');
    exit;
}

$user = getUserData();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (strlen($title) < 3) {
        $error = 'Заголовок должен быть минимум 3 символа';
    } elseif (strlen($title) > 200) {
        $error = 'Заголовок не может быть длиннее 200 символов';
    } else {
        try {
            createTopic($title, $description, $user['id'], $user['username']);
            $success = 'Тема создана!';
            header('Location: forum.php');
            exit;
        } catch (Exception $e) {
            $error = 'Ошибка создания темы: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новая тема · Форум</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .new-topic-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .new-topic-card {
            background: linear-gradient(145deg, rgba(20, 20, 35, 0.9), rgba(10, 10, 20, 0.95));
            border: 1px solid rgba(0, 255, 200, 0.08);
            border-radius: 30px;
            padding: 40px;
        }

        .new-topic-card h1 {
            font-size: 2rem;
            background: linear-gradient(135deg, #00ffc8, #ff00c8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .new-topic-card .subtitle {
            color: #667788;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #8899aa;
            font-size: 0.85rem;
            margin-bottom: 6px;
            letter-spacing: 1px;
        }

        .form-group input,
        .form-group textarea {
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
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #00ffc8;
            box-shadow: 0 0 20px rgba(0, 255, 200, 0.05);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn-primary, .btn-secondary {
            padding: 12px 28px;
            border-radius: 30px;
            text-decoration: none;
            border: none;
            font-size: 1rem;
            font-family: inherit;
            cursor: pointer;
            transition: 0.3s;
            font-weight: 600;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00ffc8, #00ccaa);
            color: #0a0a0f;
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

        .error-message {
            background: rgba(255, 0, 100, 0.08);
            border: 1px solid rgba(255, 0, 100, 0.1);
            border-radius: 12px;
            padding: 12px 16px;
            color: #ff4466;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="new-topic-container">
            <div class="new-topic-card">
                <h1>➕ Создать тему</h1>
                <p class="subtitle">Опишите тему и задайте вопрос сообществу</p>

                <?php if ($error): ?>
                    <div class="error-message">❌ <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="title">Заголовок темы *</label>
                        <input type="text" id="title" name="title" required minlength="3" maxlength="200" 
                               placeholder="Например: Как создать свой киберпанк-проект?" 
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="description">Описание (необязательно)</label>
                        <textarea id="description" name="description" placeholder="Краткое описание темы..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">🚀 Создать тему</button>
                        <a href="forum.php" class="btn-secondary">← Отмена</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>