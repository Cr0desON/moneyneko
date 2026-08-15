<?php
/**
* Шаблон виджета тренажёра
* Вставляется через шорткод [mn_trainer_m1] или в single-trainer.php
*/
if (!defined('ABSPATH')) exit;
?>
 
<div class="mn-trainer" id="mn-trainer-m1">
 
    <!-- Шапка -->
<div class="mn-trainer__header">
<div class="mn-trainer__badge">Упражнение</div>
<h2 class="mn-trainer__title">Категоризация ежедневных расходов</h2>
<p class="mn-trainer__subtitle">
            Нажми на каждую покупку и выбери её категорию:
<strong>Обязательные</strong> или <strong>Дискреционные</strong>
</p>
</div>
 
    <!-- Тело: две колонки -->
<div class="mn-trainer__body">
 
        <!-- Левая колонка: покупки -->
<div class="mn-trainer__purchases">
<div class="mn-purchases-list" id="mn-purchases-list">
<!-- Генерируется через JS -->
</div>
</div>
 
        <!-- Правая колонка: категории -->
<div class="mn-trainer__categories">
 
            <div class="mn-category mn-category--mandatory" data-category="mandatory">
<div class="mn-category__icon">🏠</div>
<div class="mn-category__label">ОБЯЗАТЕЛЬНЫЕ РАСХОДЫ</div>
<div class="mn-category__hint">Проезд, Еда, ЖКХ, Интернет…</div>
<div class="mn-category__items" id="mn-zone-mandatory"></div>
</div>
 
            <div class="mn-category mn-category--discretionary" data-category="discretionary">
<div class="mn-category__icon">🛍️</div>
<div class="mn-category__label">ДИСКРЕЦИОННЫЕ РАСХОДЫ</div>
<div class="mn-category__hint">Одежда, Кафе, Развлечения…</div>
<div class="mn-category__items" id="mn-zone-discretionary"></div>
</div>
 
        </div>
</div>
 
    <!-- Попап: выбор категории -->
<div class="mn-popup" id="mn-popup" role="dialog" aria-modal="true" aria-label="Выберите категорию">
<div class="mn-popup__card">
<div class="mn-popup__purchase-name" id="mn-popup-title"></div>
<p class="mn-popup__question">Это какой вид расхода?</p>
<div class="mn-popup__choices">
<button class="mn-popup__btn mn-popup__btn--mandatory" data-choice="mandatory">
                    🏠 Обязательный
</button>
<button class="mn-popup__btn mn-popup__btn--discretionary" data-choice="discretionary">
                    🛍️ Дискреционный
</button>
</div>
<button class="mn-popup__close" id="mn-popup-close" aria-label="Закрыть">✕</button>
</div>
</div>
 
    <!-- Результат -->
<div class="mn-trainer__result" id="mn-result" style="display:none;">
<div class="mn-result__inner">
<div class="mn-result__icon" id="mn-result-icon"></div>
<div class="mn-result__score" id="mn-result-score"></div>
<div class="mn-result__text" id="mn-result-text"></div>
<button class="mn-result__retry" id="mn-retry-btn">Попробовать ещё раз</button>
</div>
</div>
 
    <!-- Прогресс -->
<div class="mn-trainer__progress">
<div class="mn-progress-bar">
<div class="mn-progress-bar__fill" id="mn-progress-fill"></div>
</div>
<div class="mn-progress-label" id="mn-progress-label">0 / 0</div>
</div>
 
</div>