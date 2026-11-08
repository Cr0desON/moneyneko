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
 *
 * Шаги-переходы (см. js/tutorial-steps.js):
 *  - go: true            — подсвеченная ссылка кликабельна, тур продолжится на странице, куда она ведёт;
 *  - go: true, back: true — то же, но после тура той страницы пользователь автоматически возвращается
 *                           и тур продолжается со следующего шага (дальше — с части game2, game3 …).
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
    var FLOW_KEY   = 'mn_tutorial_flow';   // sessionStorage: «ушли показывать другую страницу и вернёмся»
    var FLOW_TTL   = 30 * 60 * 1000;       // забываем незавершённый переход через 30 минут
    var BACK_BTN   = 'Вернуться к игре!';  // подпись последней кнопки на странице, после которой вернёмся назад
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

    // ---------- Переход «показать другую страницу и вернуться» ----------
    // Состояние: { phase: 'out' | 'back', page, key, index, url, replay, t }
    //   out  — ушли со страницы page (часть тура key, шаг index) показывать другую страницу
    //   back — тур той страницы закончен, вернулись на url и продолжаем с шага index + 1

    function getFlow() {
        try {
            var raw = sessionStorage.getItem(FLOW_KEY);
            var f = raw ? JSON.parse(raw) : null;
            if (f && Date.now() - f.t > FLOW_TTL) { f = null; sessionStorage.removeItem(FLOW_KEY); }
            return f;
        } catch (e) { return null; }
    }

    function setFlow(f) {
        try {
            if (f === null) { sessionStorage.removeItem(FLOW_KEY); }
            else { sessionStorage.setItem(FLOW_KEY, JSON.stringify(f)); }
        } catch (e) {}
    }

    // 'game2' → 'game' (страница, к которой относится часть тура)
    function pageOf(key) { return key.replace(/\d+$/, ''); }

    // 'game' → 'game2', 'game2' → 'game3'
    function nextKey(key) {
        var m = key.match(/^(.*?)(\d*)$/);
        return m[1] + ((parseInt(m[2], 10) || 1) + 1);
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

    // Страница пройдена: у нового пользователя пишем в базу, в режиме повтора — только в sessionStorage
    function markPageDone(page, replay) {
        if (replay) { replayMarkDone(page); } else { saveSeen(page); }
    }

    // ---------- Сборка и запуск ----------

    // leave(def, index) вызывается перед переходом по ссылке на шаге с go: true
    // opts.from — с какого шага начинать; opts.returning — после этого тура вернёмся назад (кнопка «Вернуться»)
    function buildSteps(key, leave, opts) {
        var defs = allSteps[key] || [];
        var from = opts.from || 0;
        var ctx  = getContext();
        var out  = [];

        defs.forEach(function (def, index) {
            if (index < from) { return; }
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
                    var onLeave = function () { leave(def, index); };
                    step.popover.onNextClick = function () {
                        onLeave();
                        window.location.href = el.href;
                    };
                    step.onHighlighted = function () {
                        // Driver.js блокирует клики по подсвеченному элементу (глобальная опция) — снимаем блок с этой ссылки
                        el.classList.remove('driver-no-interaction');
                        el.addEventListener('click', onLeave, { once: true });
                    };
                }
            }
            out.push(step);
        });

        // Тур страницы, после которого вернёмся назад: подписываем последнюю кнопку понятно
        if (opts.returning && out.length) {
            var last = out[out.length - 1].popover;
            last.nextBtnText = BACK_BTN;
            last.doneBtnText = BACK_BTN;
        }

        return out;
    }

    // key — ключ тура: 'game', 'game2', 'leaderboard' …; opts: { from, returning, resumed }
    function startTour(key, replay, opts) {
        opts = opts || {};
        var page    = pageOf(key);
        var skipped = false;
        var tour;

        // Перед уходом по ссылке-шагу: либо запоминаем, где вернуться, либо отмечаем страницу пройденной
        function leave(def, index) {
            var flow = getFlow();
            if (opts.returning && flow && flow.phase === 'out') {
                // мы на странице, куда ушли «в гости»: ссылка ведёт обратно — тур игры продолжится с шага после ухода
                markPageDone(page, replay);
                flow.phase = 'back';
                setFlow(flow);
            } else if (def.back) {
                setFlow({ phase: 'out', page: page, key: key, index: index,
                    url: window.location.href, replay: !!replay, t: Date.now() });
            } else {
                setFlow(null);
                markPageDone(page, replay);
            }
        }

        var steps = buildSteps(key, leave, opts);
        if (!steps.length) {
            if (replay) { alert('Не удалось показать обучение: на странице не найдены нужные элементы.'); }
            return;
        }

        if (current) { current.destroy(); }

        function skip() {
            skipped = true;
            setFlow(null);
            if (replay) { setReplay(null); }   // «Закрыть» в повторном показе выключает весь повтор
            else { saveSeen('all'); }          // «Пропустить» у нового пользователя — навсегда и везде
            tour.destroy();
        }

        tour = driverLib({
            steps: steps,
            popoverClass: 'mn-tour-popover',
            showProgress: steps.length > 1 && !opts.resumed, // после возврата считать «1 / 2» не имеет смысла
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
                markPageDone(page, replay);

                // это была страница, куда мы ходили «в гости» — возвращаемся и продолжаем тур
                var flow = getFlow();
                if (opts.returning && flow && flow.phase === 'out') {
                    flow.phase = 'back';
                    setFlow(flow);
                    window.location.href = flow.url;
                }
            }
        });

        current = tour;
        tour.drive();
    }

    // Продолжаем тур после возврата: со следующего шага, а если часть закончилась — со следующей части
    function resumeFlow(flow) {
        var key  = flow.key;
        var from = flow.index + 1;

        if (from >= (allSteps[key] || []).length) {
            var next = nextKey(key);
            if (allSteps[next]) { key = next; from = 0; }
            else { markPageDone(flow.page, flow.replay); return; } // шагов больше нет — тур завершён
        }
        startTour(key, flow.replay, { from: from, resumed: true });
    }

    // ---------- Инициализация ----------

    function init() {
        var page = detectPage();

        // Кнопка «?» на странице игры: включаем повторный показ на всю вкладку и запускаем тур
        var helpBtn = document.getElementById('mn-tutorial-help');
        if (helpBtn) {
            helpBtn.addEventListener('click', function () {
                setFlow(null);
                setReplay([]);
                startTour('game', true);
            });
        }

        if (!page) { return; }

        // 1) Идёт переход «показать другую страницу и вернуться»
        var flow = getFlow();
        if (flow && flow.phase === 'back' && flow.page === page) {
            setFlow(null);
            setTimeout(function () { resumeFlow(flow); }, 500);        // вернулись — продолжаем
            return;
        }
        if (flow && flow.phase === 'out') {
            if (flow.page !== page && allSteps[page]) {
                // пришли по ссылке из тура — показываем тур этой страницы, после него вернёмся
                setTimeout(function () { startTour(page, flow.replay, { returning: true }); }, 500);
                return;
            }
            setFlow(null); // вернулись другим путём или страница без тура — переход отменяем
        }

        // 2) Обычный запуск
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