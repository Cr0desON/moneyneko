document.addEventListener('DOMContentLoaded', function() {
    // 1. Сначала проверяем, не ждет ли нас редирект с прошлого шага
    const pendingRedirect = sessionStorage.getItem('tutor_auto_redirect_url');
    if (pendingRedirect) {
        sessionStorage.removeItem('tutor_auto_redirect_url'); // Очищаем память
        window.location.href = pendingRedirect; // Делаем мгновенный переход
        return; // Останавливаем скрипт, чтобы страница не успела мигнуть
    }

    // Одна кнопка сначала сохраняет прогресс, затем открывает следующий узел или карту.
    const nextBtn = document.querySelector('.tutor-single-course-content-next a, #mn-tutor-next-btn');
    if (!nextBtn) return;

    const label = nextBtn.querySelector('.tutor-mr-8, .tutor-ml-8');
    if (label) label.textContent = 'Далее';
    else nextBtn.textContent = 'Далее';
    nextBtn.setAttribute('aria-label', 'Далее');

    nextBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (window.MNFinish && typeof window.MNFinish.go === 'function') {
            window.MNFinish.go();
            return;
        }

        const data = window.mnFinishData || window.mnTutorTopbar || {};
        window.location.href = data.nextUrl || data.gameUrl || '/game/';
    });
});
