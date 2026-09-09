(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var header = document.querySelector(
            '.tutor-course-topic-single-header, .tutor-single-page-top-bar'
        );
        if (!header) return;

        // 1) Кнопка "выход" -> вместо страницы курса ведём на /game/
        header.querySelectorAll('a[href*="/courses/"]').forEach(function (link) {
            if (window.mnTutorTopbar && mnTutorTopbar.gameUrl) {
                link.setAttribute('href', mnTutorTopbar.gameUrl);
            }
        });

        // 2) Кнопка "содержание" (мобильный офф-канвас) -> наша собственная панель
        var togglers = header.querySelectorAll(
            '.tutor-course-topics-sidebar-offcanvas-toggler, [tutor-course-topics-sidebar-offcanvas-toggler]'
        );
        togglers.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                document.body.classList.toggle('mn-toc-open');
            });
        });

        // Закрытие по клику вне панели
        document.addEventListener('click', function (e) {
            if (
                document.body.classList.contains('mn-toc-open') &&
                !e.target.closest('.tutor-lesson-sidebar, .tutor-course-single-sidebar-wrapper, .tutor-course-topics-sidebar-offcanvas-toggler, [tutor-course-topics-sidebar-offcanvas-toggler]')
            ) {
                document.body.classList.remove('mn-toc-open');
            }
        });
    });
})();