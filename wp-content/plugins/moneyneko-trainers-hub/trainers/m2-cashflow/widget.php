<?php if (!defined('ABSPATH')) exit; ?>

<div class="mn-trainer" id="mn-trainer-m2">
    <div id="mn-m2-intro" class="mn-m2-screen mn-m2-screen--active">
        <div class="mn-m2-intro-content">
            <div class="mn-m2-icon-wrap">💸</div>
            <h2 class="mn-trainer__title">Управление Кеш-флоу</h2>
            <p class="mn-trainer__subtitle">Поймай все 10 карточек с расходами и доходами, распредели их и посчитай свой Кеш-флоу!</p>

            <div class="mn-m2-instruction">
                <strong>Как играть:</strong><br>
                1. 🕹️ Двигай кота влево-вправо <b>(стрелочки или A/D на ПК, кнопки на экране с телефона)</b>, чтобы поймать карточки.<br>
                2. 🖱️ Перетаскивай (или кликай) пойманные карточки в колонки Доходов или Расходов.<br>
                3. 🧮 Посчитай разницу и выбери правильный ответ!
            </div>

            <button class="mn-btn-start" id="mn-m2-start-btn">Начать игру 🎮</button>
        </div>
    </div>

    <div id="mn-m2-catch" class="mn-m2-screen">
        <div class="mn-catch-header">
            <span class="mn-catch-title">Поймано:</span>
            <span class="mn-catch-counter"><span id="mn-caught-count">0</span> / 10</span>
        </div>
        <div class="mn-catch-area" id="mn-catch-area">
            <div class="mn-lane mn-lane-left"></div>
            <div class="mn-lane mn-lane-right"></div>
            <div class="mn-cat" id="mn-cat">
                <img src="<?php echo esc_url( MN_HUB_URL . 'trainers/m2-cashflow/cat_basket.png' ); ?>" alt="Кот с корзиной">
            </div>
            <div class="mn-controls">
                <button id="mn-btn-left" class="mn-control-btn">⬅️ Влево</button>
                <button id="mn-btn-right" class="mn-control-btn">Вправо ➡️</button>
            </div>
        </div>
    </div>

    <div id="mn-m2-sort" class="mn-m2-screen">
        <div class="mn-sort-header">
            <h2 class="mn-trainer__title">Распредели карточки</h2>
            <p class="mn-trainer__subtitle">Перетащи каждую карточку в нужную колонку: Доходы или Расходы.</p>
        </div>

        <div class="mn-sort-layout">
            <div class="mn-sort-source-wrap">
                <h3>Твои карточки</h3>
                <div class="mn-sort-source" id="mn-sort-source"></div>
            </div>

            <div class="mn-sort-zones">
                <div class="mn-sort-zone mn-zone-income" id="mn-zone-income" data-type="income">
                    <div class="mn-zone-title">📈 ДОХОДЫ (+)</div>
                    <div class="mn-zone-items"></div>
                </div>
                <div class="mn-sort-zone mn-zone-expense" id="mn-zone-expense" data-type="expense">
                    <div class="mn-zone-title">📉 РАСХОДЫ (-)</div>
                    <div class="mn-zone-items"></div>
                </div>
            </div>
        </div>

        <div class="mn-sort-footer">
            <button class="mn-btn-start mn-btn-locked" id="mn-m2-calc-btn">Дальше: Подсчет</button>
        </div>
    </div>

    <div id="mn-m2-calc" class="mn-m2-screen">
        <h2 class="mn-trainer__title">Какой Кеш-флоу за месяц?</h2>
        <p class="mn-trainer__subtitle">Оцени свои финансы по формуле: Доходы - Расходы</p>

        <div class="mn-calc-layout">
            <div class="mn-calc-boards">
                <div class="mn-calc-board mn-board-income">
                    <div class="mn-board-title">Доходы</div>
                    <div class="mn-board-sum" id="mn-calc-income-sum">0 ₽</div>
                </div>
                <div class="mn-calc-board mn-board-expense">
                    <div class="mn-board-title">Расходы</div>
                    <div class="mn-board-sum" id="mn-calc-expense-sum">0 ₽</div>
                </div>
            </div>

            <div class="mn-calc-question">
                Каким получился Кеш-флоу в этом месяце?
            </div>

            <div class="mn-calc-options">
                <button class="mn-calc-btn mn-btn-positive" data-answer="positive">📈 Положительный (+)</button>
                <button class="mn-calc-btn mn-btn-zero" data-answer="zero">⚖️ Нулевой (0)</button>
                <button class="mn-calc-btn mn-btn-negative" data-answer="negative">📉 Отрицательный (-)</button>
            </div>
        </div>
    </div>

    <div class="mn-trainer__result" id="mn-m2-result" style="display:none;">
        <div class="mn-result__inner">
            <div class="mn-result__icon" id="mn-m2-result-icon"></div>
            <div class="mn-result__score" id="mn-m2-result-score"></div>
            <div class="mn-result__text" id="mn-m2-result-text"></div>
            <button class="mn-result__retry" id="mn-m2-retry-btn">Попробовать ещё раз</button>
        </div>
    </div>
</div>