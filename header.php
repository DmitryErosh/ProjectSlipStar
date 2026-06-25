<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Остальные мета-теги -->
</head>
<body>
    <!-- ТВОЙ ХЕДЕР И НАВИГАЦИЯ -->
    <header class="header" role="banner">
        <div class="logo" aria-label="Project Slip Star">
            Project Slip_Star
            <span>// v3.0</span>
        </div>
        <nav class="nav-terminal" role="navigation" aria-label="Основная навигация">
            <a href="index.html" class="nav-link">[ГЛАВНАЯ]</a>
            <a href="#projects" class="nav-link" data-target="projects">[ПРОЕКТЫ]</a>
            <a href="#stats" class="nav-link" data-target="stats">[СТАТИСТИКА]</a>
            <a href="#blog" class="nav-link" data-target="blog">[ЛАБА]</a>
            <a href="about.html" class="nav-link">[ОБ АВТОРЕ]</a>
            <a href="feedback.html" class="nav-link">[ОБРАТНАЯ СВЯЗЬ]</a>
            <a href="https://github.com/DmitryErosh" target="_blank" rel="noopener noreferrer" class="nav-link">[GITHUB]</a>
            <?php if (isLoggedIn()): 
                $user = getUserData();
                $roleIcon = $user['role'] === 'admin' ? '👑' : '👤';
                $roleClass = $user['role'] === 'admin' ? 'admin' : '';
            ?>
                <span class="nav-status">
                    <span class="status-dot"></span>
                    <span class="username"><?php echo htmlspecialchars($user['username']); ?></span>
                    <span class="role-badge <?php echo $roleClass; ?>"><?php echo $roleIcon . ' ' . strtoupper($user['role']); ?></span>
                    <a href="logout.php" class="logout-link">[выход]</a>
                </span>
            <?php else: ?>
                <a href="auth.html" class="nav-link" style="color: #00ffc8;">[ВХОД]</a>
            <?php endif; ?>
        </nav>
    </header>
    <!-- ОСТАЛЬНОЙ КОНТЕНТ -->