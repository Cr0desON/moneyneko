<?php if (!defined('ABSPATH')) exit; ?>

<div class="mn2-wrapper" id="mn-trainer-m4">

    <div id="mn2-phase-intro" class="mn2-card mn2-active-phase">
        <div class="mn2-badge">Задание</div>
        <h2 class="mn2-title">В поисках потерянного чека</h2>
        <p class="mn2-text" style="text-align: left;">
            Студент Ваня решил взять финансы под контроль. Для начала нужно найти все его чеки в комнате и разделить их на три категории:
            <br><br>
            <b>1. Обязательные:</b> Базовые нужды для жизни и учебы.<br>
            <b>2. Необязательные:</b> Комфорт, без которого можно обойтись.<br>
            <b>3. Желательные:</b> Развлечения и "хотелки".
        </p>
        <p class="mn2-text">
            Помоги найти все чеки. Но чем быстрее ты найдешь, тем лучше. Таймер запустится, как только ты нажмешь кнопку ниже!
        </p>
        <button class="mn2-btn mn2-btn-primary" id="mn2-btn-start">Начать поиск 🔍</button>
    </div>

    <div id="mn2-phase-search" class="mn2-phase-hidden mn2-card">
        <div class="mn2-search-header">
            <h3>Найдено чеков: <span id="mn2-found-count">0</span></h3>
            <div class="mn2-timer-display">⏱ <span id="mn2-timer">00:00</span></div>
            <button class="mn2-btn mn2-btn-secondary mn2-btn-sm" id="mn2-btn-finish-early">Завершить поиск</button>
        </div>
        
        <div class="mn2-room-container" id="mn2-room" style="background-image: url('<?php echo esc_url( MN_HUB_URL . 'trainers/m4-hidden-object/room-bg.jpg' ); ?>');">
        </div>
    </div>

    <div id="mn2-phase-categorize" class="mn2-phase-hidden mn2-card">
        <h3 class="mn2-title" style="margin-bottom: 25px;">Распредели чеки по категориям</h3>
        
        <div class="mn2-cat-grid">
            <div class="mn2-purchases" id="mn2-purchases-list"></div>

            <div class="mn2-zones">
                <div class="mn2-zone mn2-zone-req" data-cat="mandatory">
                    <div class="mn2-zone-title">🏠 Обязательные</div>
                    <div class="mn2-zone-items" id="mn2-z-mandatory"></div>
                </div>
                <div class="mn2-zone mn2-zone-nonreq" data-cat="non_mandatory">
                    <div class="mn2-zone-title">☕ Необязательные</div>
                    <div class="mn2-zone-items" id="mn2-z-non_mandatory"></div>
                </div>
                <div class="mn2-zone mn2-zone-desire" data-cat="desirable">
                    <div class="mn2-zone-title">🎮 Желательные</div>
                    <div class="mn2-zone-items" id="mn2-z-desirable"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mn2-popup" id="mn2-popup">
        <div class="mn2-popup-card" style="text-align: center;">
            <h4 id="mn2-popup-title" style="font-size: 20px; margin-bottom: 5px;">Чек</h4>
            <p style="margin-bottom: 20px; color: #666;">К какой категории относится эта трата?</p>
            <div class="mn2-popup-buttons">
                <button class="mn2-btn mn2-btn-req" data-choice="mandatory">🏠 Обязательная</button>
                <button class="mn2-btn mn2-btn-nonreq" data-choice="non_mandatory">☕ Необязательная</button>
                <button class="mn2-btn mn2-btn-desire" data-choice="desirable">🎮 Желательная</button>
            </div>
            <button class="mn2-popup-close" id="mn2-popup-close">✕</button>
        </div>
    </div>

    <div class="mn2-popup" id="mn2-result-popup">
        <div class="mn2-popup-card" style="text-align: center;">
            <div style="font-size: 50px; margin-bottom: 10px;" id="mn2-result-emoji">🎉</div>
            <h3 id="mn2-result-score" style="font-size: 22px; margin-bottom: 10px;">Правильно: 0 / 8</h3>
            <p id="mn2-result-text" style="color: #666; margin-bottom: 20px;">Молодец!</p>
            
            <div id="mn2-summary-box" class="mn2-summary" style="display:none;">
                <p style="font-size: 18px;">Вы нашли все чеки за: <b id="mn2-summary-time">00:00</b> ⏱</p>
            </div>

            <button class="mn2-btn mn2-btn-primary" id="mn2-btn-retry">Попробовать снова</button>
        </div>
    </div>

</div>