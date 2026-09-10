(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var header = document.querySelector(
            '.tutor-course-topic-single-header, .tutor-single-page-top-bar'
        );
        var contentWrapper = document.querySelector('.tutor-course-single-content-wrapper');

        if (!header) return;

        // 1) Кнопка "выход" -> вместо страницы курса ведём на /game/
        header.querySelectorAll('a[href*="/courses/"]').forEach(function (link) {
            if (window.mnTutorTopbar && mnTutorTopbar.gameUrl) {
                link.setAttribute('href', mnTutorTopbar.gameUrl);
            }
        });

        // 2) Кнопка "содержание" (мобильный офф-канвас)
        var togglers = header.querySelectorAll(
            '.tutor-course-topics-sidebar-offcanvas-toggler, [tutor-course-topics-sidebar-offcanvas-toggler]'
        );
        togglers.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                // Убраны e.preventDefault() и e.stopPropagation(),
                // чтобы не мешать нативному скрипту Tutor LMS открыть сайдбар.

                document.body.classList.toggle('mn-toc-open');
                document.body.classList.toggle('tutor-overflow-hidden');

                // Мы больше не переключаем tutor-course-single-sidebar-open здесь вручную.
                // Tutor LMS сам успешно повесит этот класс.
            });
        });

        // 3) Крестик закрытия — вешаем обработчик напрямую на кнопку.
        document.querySelectorAll('.tutor-hide-course-single-sidebar').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setTimeout(function () {
                    document.body.classList.remove('mn-toc-open');
                    document.body.classList.remove('tutor-overflow-hidden');

                    // А вот здесь (при закрытии) исправляем баг Tutor LMS
                    if (contentWrapper) {
                        contentWrapper.classList.remove('tutor-course-single-sidebar-open');
                    }
                }, 0);
            });
        });
    });

    // Закрытие по клику вне панели
    document.addEventListener('click', function (e) {
        var contentWrapper = document.querySelector('.tutor-course-single-content-wrapper');

        if (
            document.body.classList.contains('mn-toc-open') &&
            !e.target.closest(
                '.tutor-lesson-sidebar, .tutor-course-single-sidebar-wrapper, ' +
                '.tutor-course-topics-sidebar-offcanvas-toggler, [tutor-course-topics-sidebar-offcanvas-toggler]'
            )
        ) {
            document.body.classList.remove('mn-toc-open');
            document.body.classList.remove('tutor-overflow-hidden');

            // Также принудительно очищаем класс при клике мимо панели
            if (contentWrapper) {
                contentWrapper.classList.remove('tutor-course-single-sidebar-open');
            }
        }
    });
})();