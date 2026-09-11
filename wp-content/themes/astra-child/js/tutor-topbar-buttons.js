(function () {
    'use strict';

    function closeSidebar() {
        document.body.classList.remove('mn-toc-open');
        document.body.classList.remove('tutor-overflow-hidden');

        var contentWrapper = document.querySelector('.tutor-course-single-content-wrapper');
        if (contentWrapper) {
            contentWrapper.classList.remove('tutor-course-single-sidebar-open');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var header = document.querySelector(
            '.tutor-course-topic-single-header, .tutor-single-page-top-bar'
        );
        if (!header) return;

        header.querySelectorAll('a[href*="/courses/"]').forEach(function (link) {
            if (window.mnTutorTopbar && mnTutorTopbar.gameUrl) {
                link.setAttribute('href', mnTutorTopbar.gameUrl);
            }
        });

        var togglers = header.querySelectorAll(
            '.tutor-course-topics-sidebar-offcanvas-toggler, [tutor-course-topics-sidebar-offcanvas-toggler]'
        );
        togglers.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                if (document.body.classList.contains('mn-hide-sidebar')) {
                    return;
                }
                document.body.classList.toggle('mn-toc-open');
                document.body.classList.toggle('tutor-overflow-hidden');
            });
        });
    });

    // Крестик для закрытия
    document.addEventListener('click', function (e) {
        if (e.target.closest('[tutor-hide-course-single-sidebar]')) {
            e.preventDefault();
            closeSidebar();
        }
    });

    // Закрытие по клику вне панели
    document.addEventListener('click', function (e) {
        if (
            document.body.classList.contains('mn-toc-open') &&
            !e.target.closest(
                '.tutor-lesson-sidebar, .tutor-course-single-sidebar-wrapper, ' +
                '.tutor-course-topics-sidebar-offcanvas-toggler, [tutor-course-topics-sidebar-offcanvas-toggler]'
            )
        ) {
            closeSidebar();
        }
    });
})();