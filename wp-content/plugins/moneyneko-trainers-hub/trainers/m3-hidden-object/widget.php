<?php if (!defined('ABSPATH')) exit; ?>

<div class="mn3-wrapper" id="mn-trainer-m3">

    <div id="mn3-phase-intro" class="mn3-card mn3-active-phase mn3-intro">
        <div class="mn3-intro-content">
            <div class="mn3-icon-wrap">🧾</div>
            <h2 class="mn3-title">В поисках потерянного чека</h2>
            <p class="mn3-subtitle">
                Студент Ваня решил взять финансы под контроль. Найди все его чеки в комнате и раздели их на три категории!
            </p>

            <div class="mn3-instruction">
                <strong>Категории трат:</strong><br>
                1. 🏠 <b>Обязательные</b> — базовые нужды для жизни и учебы.<br>
                2. ☕ <b>Необязательные</b> — комфорт, без которого можно обойтись.<br>
                3. 🎮 <b>Желательные</b> — развлечения и «хотелки».<br>
                <br>
                ⏱ Чем быстрее найдёшь чеки, тем лучше. Таймер запустится после нажатия кнопки!
            </div>

            <button class="mn3-btn-start" id="mn3-btn-start">Начать поиск 🔍</button>
        </div>
    </div>

    <div id="mn3-phase-search" class="mn3-phase-hidden mn3-card">
        <div class="mn3-search-header">
            <h3>Найдено чеков: <span id="mn3-found-count">0</span></h3>
            <div class="mn3-timer-display">⏱ <span id="mn3-timer">00:00</span></div>
            <button class="mn3-btn mn3-btn-secondary mn3-btn-sm" id="mn3-btn-finish-early">Завершить поиск</button>
        </div>
        
        <div class="mn3-room-container" id="mn3-room" style="background-image: url('<?php echo esc_url( MN_HUB_URL . 'trainers/m3-hidden-object/room-bg.jpg' ); ?>');">
        </div>
    </div>

    <div id="mn3-phase-categorize" class="mn3-phase-hidden mn3-card">
        <h3 class="mn3-title" style="margin-bottom: 25px;">Распредели чеки по категориям</h3>
        
        <div class="mn3-cat-grid">
            <div class="mn3-purchases" id="mn3-purchases-list"></div>

            <div class="mn3-zones">
                <div class="mn3-zone mn3-zone-req" data-cat="mandatory">
                    <div class="mn3-zone-title">🏠 Обязательные</div>
                    <div class="mn3-zone-items" id="mn3-z-mandatory"></div>
                </div>
                <div class="mn3-zone mn3-zone-nonreq" data-cat="non_mandatory">
                    <div class="mn3-zone-title">☕ Необязательные</div>
                    <div class="mn3-zone-items" id="mn3-z-non_mandatory"></div>
                </div>
                <div class="mn3-zone mn3-zone-desire" data-cat="desirable">
                    <div class="mn3-zone-title">🎮 Желательные</div>
                    <div class="mn3-zone-items" id="mn3-z-desirable"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mn3-popup" id="mn3-popup">
        <div class="mn3-popup-card" style="text-align: center;">
            <h4 id="mn3-popup-title" style="font-size: 20px; margin-bottom: 5px;">Чек</h4>
            <p style="margin-bottom: 20px; color: #666;">К какой категории относится эта трата?</p>
            <div class="mn3-popup-buttons">
                <button class="mn3-btn mn3-btn-req" data-choice="mandatory">🏠 Обязательная</button>
                <button class="mn3-btn mn3-btn-nonreq" data-choice="non_mandatory">☕ Необязательная</button>
                <button class="mn3-btn mn3-btn-desire" data-choice="desirable">🎮 Желательная</button>
            </div>
            <button class="mn3-popup-close" id="mn3-popup-close">✕</button>
        </div>
    </div>

    <div class="mn3-popup" id="mn3-result-popup">
        <div class="mn3-popup-card" style="text-align: center;">
            <div style="font-size: 50px; margin-bottom: 10px;" id="mn3-result-emoji">🎉</div>
            <h3 id="mn3-result-score" style="font-size: 22px; margin-bottom: 10px;">Правильно: 0 / 8</h3>
            <p id="mn3-result-text" style="color: #666; margin-bottom: 20px;">Молодец!</p>
            
            <div id="mn3-summary-box" class="mn3-summary" style="display:none;">
                <p style="font-size: 18px;">Вы нашли все чеки за: <b id="mn3-summary-time">00:00</b> ⏱</p>
            </div>

            <button class="mn3-btn mn3-btn-primary" id="mn3-btn-retry">Попробовать снова</button>
        </div>
    </div>

</div>