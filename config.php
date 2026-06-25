<?php
// ===== КОНФИГУРАЦИЯ БАЗЫ ДАННЫХ =====
$db_host = 'sql211.infinityfree.com';
$db_name = 'if0_42267186_slipstar';  // ЗАМЕНИ НА СВОЁ ИМЯ БД
$db_user = 'if0_42267186';
$db_pass = 'Ie2nh3antuwnB';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// ===== СЕССИЯ =====
session_start();

// ===== ФУНКЦИИ =====
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserData() {
    if (!isLoggedIn()) return null;
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function getUserRole() {
    $user = getUserData();
    return $user ? $user['role'] : null;
}

function isAdmin() {
    return getUserRole() === 'admin';
}
?>