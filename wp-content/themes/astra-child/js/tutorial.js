/**
 * Логика туториала MoneyNeko (Driver.js).
 * Тексты и шаги — в js/tutorial-steps.js, настройки и состояние — из inc/tutorial.php (mnTutorial).
 */
(function () {
    'use strict';

    var cfg       = window.mnTutorial;
    // Если в tutorial-steps.js вместо window.mnTutorialSteps = {...} написано const/let mnTutorialSteps = {...},
    // переменная не попадает в window, но видна как глобальная — подхватываем и такой вариант
    var allSteps  = window.mnTutorialSteps || (typeof mnTutorialSteps !== 'undefined' ? mnTutorialSteps : undefined);
    // IIFE-сборка Driver.js кладёт функцию в window.driver.js.driver
    var driverLib = window.driver && window.driver.js && window.driver.js.driver;

    // Диагностика: если туториал «молчит», причина будет в консоли браузера (F12)
    if (!cfg || !allSteps || !driverLib) {
        console.warn('[MoneyNeko tutorial] не запущен. Не найдено:',
            !cfg ? 'window.mnTutorial (inc/tutorial.php не подключён в functions.php или пользователь не авторизован)' : '',
            !allSteps ? 'window.mnTutorialSteps (js/tutorial-steps.js не загрузился)' : '',
            !driverLib ? 'window.driver (js/vendor/driver.js.iife.js не загрузился или его «склеил/задержал» плагин оптимизации)' : '');
        // Кнопка «?» не должна быть «мёртвой»: скажем пользователю, что что-то не так
        document.addEventListener('click', function (e) {
            if (e.target.closest && e.target.closest('#mn-tutorial-help')) {
                alert('Обучение не загрузилось. Обновите страницу (Ctrl+F5) и попробуйте ещё раз.');
            }
        });
        return;
    }

    var CAT_TITLE = '<span class="mn-tour-cat" aria-hidden="true">🐱</span> Грош';
    var current   = null; // активный экземпляр тура

    // ---------- Определение страницы ----------

    function detectPage() {
        if (cfg.page) { return cfg.page; }
        if (document.querySelector('.sketch-landing-wrapper')) { return 'game'; }
        if (document.querySelector('.lb-card'))                { return 'leaderboard'; }
        if (document.querySelector('.sketch-profile'))         { return 'profile'; }
        return '';
    }

    // ---------- Вспомогательные ----------

    function isVisible(el) {
        if (!el || !el.getClientRects().length) { return false; }
        var st = window.getComputedStyle(el);
        return st.visibility !== 'hidden' && st.display !== 'none';
    }

    function findElement(selectors) {
        var list = Array.isArray(selectors) ? selectors : [selectors];
        for (var i = 0; i < list.length; i++) {
            var el = document.querySelector(list[i]);
            if (isVisible(el)) { return el; }
        }
        return null;
    }

    function getContext() {
        var moodEl = document.querySelector('.mood-progress-text');
        var mood = moodEl ? parseInt(moodEl.textContent, 10) : NaN;
        return { mood: isNaN(mood) ? 50 : mood };
    }

    // Сохраняем прогресс в user_meta (работает и с телефона, и с ПК)
    function saveSeen(pages) {
        var list = pages === 'all' ? ['game', 'leaderboard', 'module', 'profile'] : [pages];
        list.forEach(function (p) {
            if (cfg.seen.indexOf(p) === -1) { cfg.seen.push(p); }
        });

        var fd = new FormData();
        fd.append('action', 'mn_tutorial_save');
        fd.append('nonce', cfg.nonce);
        fd.append('pages', pages);
        try {
            fetch(cfg.ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin', keepalive: true })
                .catch(function () {});
        } catch (e) { /* без сети просто покажем туториал в следующий раз */ }
    }

    // ---------- Сборка и запуск ----------

    function buildSteps(page) {
        var defs = allSteps[page] || [];
        var ctx  = getContext();
        var out  = [];

        defs.forEach(function (def) {
            var step = {
                popover: {
                    title: CAT_TITLE,
                    description: typeof def.text === 'function' ? def.text(ctx) : def.text,
                    nextBtnText: def.btn,
                    doneBtnText: def.btn,
                    showButtons: ['next'],
                    side: def.side || 'bottom',
                    align: 'center'
                }
            };

            if (def.selector) {
                var el = findElement(def.selector);
                if (!el) { // элемента нет на этой версии страницы — пропускаем шаг
                    console.warn('[MoneyNeko tutorial] шаг пропущен, элемент не найден или скрыт:', def.selector);
                    return;
                }
                step.element = el;
            }
            out.push(step);
        });

        return out;
    }

    function startTour(page, replay) {
        if (current) { current.destroy(); current = null; }

        var steps = buildSteps(page);
        if (!steps.length) {
            console.warn('[MoneyNeko tutorial] для страницы «' + page + '» нет ни одного шага — на странице не найдены нужные элементы.');
            if (replay) { alert('Не удалось показать обучение: на странице не найдены нужные элементы.'); }
            return;
        }

        var skipped = false;
        var tour;

        function skip() {
            skipped = true;
            if (!replay) { saveSeen('all'); }
            tour.destroy();
        }

        tour = driverLib({
            steps: steps,
            popoverClass: 'mn-tour-popover',
            showProgress: steps.length > 1,
            progressText: '{{current}} / {{total}}',
            allowClose: false,                // выход только через «Пропустить»
            disableActiveInteraction: true,   // клики по подсвеченным ссылкам не уводят со страницы
            overlayColor: '#2c3e50',
            overlayOpacity: 0.65,
            stagePadding: 10,
            stageRadius: 12,
            smoothScroll: true,
            onPopoverRender: function (popover) {
                if (popover.wrapper.querySelector('.mn-tour-skip')) { return; }
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'mn-tour-skip';
                btn.textContent = replay ? 'Закрыть' : 'Пропустить обучение';
                btn.addEventListener('click', skip);
                popover.wrapper.appendChild(btn);
            },
            onDestroyed: function () {
                current = null;
                // дошёл до конца → запоминаем страницу (при повторном просмотре и пропуске не нужно)
                if (!replay && !skipped) { saveSeen(page); }
            }
        });

        current = tour;
        tour.drive();
    }

    // ---------- Инициализация ----------

    function init() {
        var page = detectPage();
        console.info('[MoneyNeko tutorial] страница:', page || '(не определена)',
            '| включён для пользователя:', cfg.enabled, '| уже просмотрено:', cfg.seen);

        // Кнопка «?» на странице игры — показывает весь туториал заново
        var helpBtn = document.getElementById('mn-tutorial-help');
        if (helpBtn) {
            helpBtn.addEventListener('click', function () { startTour('game', true); });
        }

        // Автозапуск: только новым пользователям и только на ещё не просмотренных страницах
        if (page && cfg.enabled && cfg.seen.indexOf(page) === -1) {
            setTimeout(function () { startTour(page, false); }, 500);
        }
    }

    if (document.readyState === 'complete') {
        init();
    } else {
        window.addEventListener('load', init);
    }
})();