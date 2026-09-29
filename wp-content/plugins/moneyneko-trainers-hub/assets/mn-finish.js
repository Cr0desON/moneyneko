(function () {
    'use strict';
    var busy = false;

    function markControls() {
        var el = document.querySelector('.tutor-topbar-mark-btn');
        if (!el) return null; // кнопки нет: урок уже пройден
        var form = el.tagName === 'FORM' ? el : el.closest('form');
        if (!form) return null;
        var btn = el.tagName === 'FORM' ? form.querySelector('[type="submit"]') : el;
        return { form: form, btn: btn };
    }

    function getNextUrl() {
        var d = window.mnFinishData || {};
        if (d.nextUrl) return d.nextUrl;
        var a = document.querySelector('.tutor-single-course-content-next a[href]:not([href="#"])');
        return a ? a.href : '';
    }

    async function completeViaFetch(m) {
        var fd = new FormData(m.form);
        if (m.btn && m.btn.name && !fd.has(m.btn.name)) {
            fd.append(m.btn.name, m.btn.value || '');
        }
        // getAttribute, потому что form.action ломается, если внутри есть поле name="action"
        var res = await fetch(m.form.getAttribute('action') || location.href, {
            method: 'POST', body: fd, credentials: 'same-origin'
        });
        if (!res.ok) return false;
        var doc = new DOMParser().parseFromString(await res.text(), 'text/html');
        // Урок засчитан, если в ответе больше нет кнопки «Завершить»
        return !doc.querySelector('.tutor-topbar-mark-btn');
    }

    async function go() {
        if (busy) return;
        busy = true;

        var url = getNextUrl();
        var m = markControls();

        if (m) {
            var ok = false;
            try { ok = await completeViaFetch(m); } catch (e) { console.error('[MNFinish]', e); }

            if (!ok) {
                // Запасной путь: обычная отправка формы Tutor + редирект после перезагрузки
                if (url) sessionStorage.setItem('tutor_auto_redirect_url', url);
                try { m.form.requestSubmit(m.btn); return; } catch (e) {
                    console.error('[MNFinish] fallback failed', e);
                    busy = false;
                    alert('Не удалось сохранить прогресс. Обновите страницу и попробуйте ещё раз.');
                    return;
                }
            }
        }

        if (url) location.href = url; else location.reload();
    }

    window.MNFinish = { go: go };
})();