/**
 * Логика туториала MoneyNeko (Driver.js).
 * Тексты и шаги — в js/tutorial-steps.js, настройки и состояние — из inc/tutorial.php (mnTutorial).
 *
 * Два независимых режима:
 *  1) Автопоказ (replay = false) — только новым пользователям (cfg.enabled).
 *     Прогресс пишется в user_meta: страница пройдена / пропущена.
 *  2) Повторный показ по кнопке «?» (replay = true) — в базу НИЧЕГО не пишет.
 *     Включается на всю вкладку (sessionStorage), поэтому тур идёт по страницам цепочкой
 *     (игра → урок → лидерборд → профиль), в том числе у «старых» пользователей.
 */
(function () {
    'use strict';

    var cfg       = window.mnTutorial;
    var allSteps  = window.mnTutorialSteps;
    // IIFE-сборка Driver.js кладёт функцию в window.driver.js.driver
    var driverLib = window.driver && window.driver.js && window.driver.js.driver;

    // Если что-то не загрузилось — кнопка «?» хотя бы скажет об этом, а не будет «мёртвой»
    if (!cfg || !allSteps || !driverLib) {
        document.addEventListener('click', function (e) {
            if (e.target.closest && e.target.closest('#mn-tutorial-help')) {
                alert('Обучение не загрузилось. Обновите страницу (Ctrl+F5) и попробуйте ещё раз.');
            }
        });
        return;
    }

    var PAGES      = ['game', 'leaderboard', 'module', 'profile'];
    var REPLAY_KEY = 'mn_tutorial_replay'; // sessionStorage: JSON-массив страниц, где повтор уже показан
    var CAT_TITLE  = '<span class="mn-tour-cat" aria-hidden="true">🐱</span> Грош';
    var current    = null; // активный экземпляр тура

    // ---------- Режим повторного показа (живёт в sessionStorage, в базу не пишет) ----------

    function getReplay() {
        try {
            var raw = sessionStorage.getItem(REPLAY_KEY);
            return raw ? JSON.parse(raw) : null; // null = режим выключен
        } catch (e) { return null; }
    }

    function setReplay(list) {
        try {
            if (list === null) { sessionStorage.removeItem(REPLAY_KEY); }
            else { sessionStorage.setItem(REPLAY_KEY, JSON.stringify(list)); }
        } catch (e) { /* нет sessionStorage — повтор будет работать только на текущей странице */ }
    }

    // Отмечаем страницу как «повтор показан»; когда прошли все страницы — режим выключается сам
    function replayMarkDone(page) {
        var list = getReplay();
        if (!list) { return; }
        if (list.indexOf(page) === -1) { list.push(page); }
        var allDone = PAGES.every(function (p) { return list.indexOf(p) !== -1; });
        setReplay(allDone ? null : list);
    }

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
        var list = pages === 'all' ? PAGES : [pages];
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

    // beforeLeave вызывается перед переходом по ссылке на шаге с go: true
    function buildSteps(page, beforeLeave) {
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
                if (!el) { return; } // элемента нет на этой версии страницы — пропускаем шаг
                step.element = el;

                // Шаг-переход: подсвеченная ссылка кликабельна, кнопка в окошке ведёт по ней же
                if (def.go && el.href) {
                    step.popover.onNextClick = function () {
                        beforeLeave();
                        window.location.href = el.href;
                    };
                    step.onHighlighted = function () {
                        // Driver.js блокирует клики по подсвеченному элементу (глобальная опция) — снимаем блок с этой ссылки
                        el.classList.remove('driver-no-interaction');
                        el.addEventListener('click', beforeLeave, { once: true });
                    };
                }
            }
            out.push(step);
        });

        return out;
    }

    function startTour(page, replay) {
        var skipped = false;
        var tour;

        function beforeLeave() {
            if (replay) { replayMarkDone(page); } else { saveSeen(page); }
        }

        var steps = buildSteps(page, beforeLeave);
        if (!steps.length) {
            if (replay) { alert('Не удалось показать обучение: на странице не найдены нужные элементы.'); }
            return;
        }

        if (current) { current.destroy(); }

        function skip() {
            skipped = true;
            if (replay) { setReplay(null); }   // «Закрыть» в повторном показе выключает весь повтор
            else { saveSeen('all'); }          // «Пропустить» у нового пользователя — навсегда и везде
            tour.destroy();
        }

        tour = driverLib({
            steps: steps,
            popoverClass: 'mn-tour-popover',
            showProgress: steps.length > 1,
            progressText: '{{current}} / {{total}}',
            allowClose: false,                // выход только через «Пропустить» / «Закрыть»
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
                if (skipped) { return; }
                // дошёл до конца → запоминаем страницу
                if (replay) { replayMarkDone(page); } else { saveSeen(page); }
            }
        });

        current = tour;
        tour.drive();
    }

    // ---------- Инициализация ----------

    function init() {
        var page = detectPage();

        // Кнопка «?» на странице игры: включаем повторный показ на всю вкладку и запускаем тур
        var helpBtn = document.getElementById('mn-tutorial-help');
        if (helpBtn) {
            helpBtn.addEventListener('click', function () {
                setReplay([]);
                startTour('game', true);
            });
        }

        if (!page) { return; }

        var replayList = getReplay();
        var shouldRun = replayList
            ? replayList.indexOf(page) === -1                  // идёт повтор: на этой странице он ещё не показан
            : (cfg.enabled && cfg.seen.indexOf(page) === -1);  // обычный автопоказ для нового пользователя

        if (shouldRun) {
            setTimeout(function () { startTour(page, !!replayList); }, 500);
        }
    }

    if (document.readyState === 'complete') {
        init();
    } else {
        window.addEventListener('load', init);
    }

    // Для разработки: window.mnTutorialDebug('leaderboard') — показать тур страницы вручную
    window.mnTutorialDebug = function (page) {
        startTour(page || detectPage(), true);
    };
})();