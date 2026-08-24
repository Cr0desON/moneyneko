document.addEventListener('DOMContentLoaded', function() {
    // 1. Сначала проверяем, не ждет ли нас редирект с прошлого шага
    const pendingRedirect = sessionStorage.getItem('tutor_auto_redirect_url');
    if (pendingRedirect) {
        sessionStorage.removeItem('tutor_auto_redirect_url'); // Очищаем память
        window.location.href = pendingRedirect; // Делаем мгновенный переход
        return; // Останавливаем скрипт, чтобы страница не успела мигнуть
    }

    // 2. Основная логика работы кнопок
    setTimeout(function() {
        const nextBtn = document.querySelector('.tutor-single-course-content-next a');
        const completeBtn = document.querySelector('.tutor-topbar-mark-btn');

        if (nextBtn && completeBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault(); // Останавливаем обычный клик
                const nextUrl = nextBtn.href; // Запоминаем, куда ведет кнопка "Дальше"

                sessionStorage.setItem('tutor_auto_redirect_url', nextUrl);
                completeBtn.click(); // Нажимаем системную кнопку "Завершить"

                // Подстраховка
                setTimeout(function() {
                    window.location.href = nextUrl;
                }, 1500);
            });
        }
    }, 800);
});