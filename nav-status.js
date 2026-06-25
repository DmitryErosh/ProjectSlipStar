// ===== СТАТУС ПОЛЬЗОВАТЕЛЯ В НАВИГАЦИИ =====
async function updateNavStatus() {
    try {
        const response = await fetch('check-auth.php');
        const data = await response.json();

        // Находим контейнер для статуса
        const nav = document.querySelector('.nav-terminal');
        if (!nav) return;

        // Удаляем старый статус, если он есть
        const oldStatus = document.getElementById('userNavStatus');
        if (oldStatus) oldStatus.remove();

        if (data.loggedIn) {
            // Создаём элемент статуса
            const statusEl = document.createElement('span');
            statusEl.id = 'userNavStatus';
            statusEl.className = 'nav-status';
            
            const roleIcon = data.user.role === 'admin' ? '👑' : '👤';
            const roleText = data.user.role === 'admin' ? 'ADMIN' : 'USER';
            
            statusEl.innerHTML = `
                <span style="color: #00ffc8; margin-right: 8px;">●</span>
                <span style="color: #ccddee;">${data.user.username}</span>
                <span style="color: #667788; font-size: 0.6rem; background: rgba(0,255,200,0.1); padding: 2px 8px; border-radius: 12px; margin-left: 6px;">
                    ${roleIcon} ${roleText}
                </span>
                <a href="logout.php" style="color: #ff4466; text-decoration: none; margin-left: 10px; font-size: 0.7rem;">[выход]</a>
            `;
            
            // Вставляем перед последним элементом
            const lastLink = nav.lastElementChild;
            nav.insertBefore(statusEl, lastLink);

        } else {
            // Если не авторизован — показываем ссылку на вход
            const statusEl = document.createElement('span');
            statusEl.id = 'userNavStatus';
            statusEl.className = 'nav-status';
            statusEl.innerHTML = `
                <a href="auth.html" style="color: #00ffc8; text-decoration: none; font-size: 0.75rem;">[ВХОД]</a>
            `;
            
            const lastLink = nav.lastElementChild;
            nav.insertBefore(statusEl, lastLink);
        }

    } catch (error) {
        console.error('Ошибка обновления статуса:', error);
    }
}

// Запускаем при загрузке страницы
document.addEventListener('DOMContentLoaded', updateNavStatus);

// Обновляем статус при переходе между страницами (для SPA-эффекта)
window.addEventListener('pageshow', updateNavStatus);