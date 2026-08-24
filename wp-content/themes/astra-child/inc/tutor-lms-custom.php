<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Скрытие кнопки "Mark as Complete" и умный редирект при нажатии "Дальше"
 */
add_action('wp_head', function() {
    ?>
    <style>
        /* Усиленные селекторы (через body), чтобы перебить общие стили кнопок */
        body form.tutor-topbar-mark-btn,
        body .tutor-topbar-mark-btn,
        body .tutor-course-finish-btn-container,
        body .tutor-course-lesson-single-footer .tutor-btn-done {
            display: none !important;
        }
    </style>
    <?php
});

add_action('wp_footer', function() {
    ?>
    <script>
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

                        // Записываем ссылку в память браузера.
                        // Если Tutor LMS перезагрузит страницу после отметки, сработает Шаг 1 (выше)
                        sessionStorage.setItem('tutor_auto_redirect_url', nextUrl);

                        // Нажимаем системную кнопку "Завершить"
                        completeBtn.click();

                        // Подстраховка: если Tutor LMS не станет перезагружать страницу,
                        // мы отправим пользователя на следующий урок сами через 1.5 секунды.
                        // Этого времени достаточно, чтобы сервер успел начислить баллы в GamiPress.
                        setTimeout(function() {
                            window.location.href = nextUrl;
                        }, 1500);
                    });
                }
            }, 800);
        });
    </script>
    <?php
});