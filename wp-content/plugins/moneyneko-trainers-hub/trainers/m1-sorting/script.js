/**
* MoneyNeko Trainer — Модуль 1
* Механика: клик → попап → выбор категории → фидбек
*/
(function () {
    'use strict';
 
    // ─── Данные: покупки ──────────────────────────────────────────────────────
    const PURCHASES = [
        { name: 'Проездной билет', emoji: '🚌', correct: 'mandatory', hint: 'Транспорт — базовая необходимость' },
        { name: 'Обед в столовой', emoji: '🍽️', correct: 'mandatory', hint: 'Питание — обязательная статья' },
        { name: 'Новые джинсы', emoji: '👖', correct: 'discretionary', hint: 'Одежда сверх необходимого' },
        { name: 'Кофе на вынос', emoji: '☕', correct: 'discretionary', hint: 'Кофе в кафе — приятность' },
        { name: 'Интернет (тариф)', emoji: '📶', correct: 'mandatory', hint: 'Нужен для учёбы и работы' },
        { name: 'Билет в кино', emoji: '🎬', correct: 'discretionary', hint: 'Развлечения — дискреционные траты' },
        { name: 'Лекарства из аптеки', emoji: '💊', correct: 'mandatory', hint: 'Здоровье — всегда обязательно' },
        { name: 'Подписка на стриминг', emoji: '📺', correct: 'discretionary', hint: 'Можно отложить без ущерба' },
    ];
 
    // ─── Состояние ────────────────────────────────────────────────────────────
    let state = {
        purchases: [],
        answered: {},
        activePurchase: null
    };
 
    // ─── Узлы DOM ─────────────────────────────────────────────────────────────
    const $ = id => document.getElementById(id);
    const purchaseList   = $('mn-purchases-list');
    const zoneMandatory  = $('mn-zone-mandatory');
    const zoneDiscretion = $('mn-zone-discretionary');
    const popup          = $('mn-popup');
    const popupTitle     = $('mn-popup-title');
    const popupClose     = $('mn-popup-close');
    const resultBlock    = $('mn-result');
    const resultIcon     = $('mn-result-icon');
    const resultScore    = $('mn-result-score');
    const resultText     = $('mn-result-text');
    const retryBtn       = $('mn-retry-btn');
    const progressFill   = $('mn-progress-fill');
    const progressLabel  = $('mn-progress-label');
    const nextBtn        = $('mn-tutor-next-btn');
 
    // Если виджет не найден — выходим
    if (!purchaseList) return;
 
    // ─── Инициализация ────────────────────────────────────────────────────────
    function init() {
        state.purchases = shuffle([...PURCHASES]);
        state.answered = {};
        state.activePurchase = null;
 
        renderPurchases();
        clearZones();
        updateProgress();
        lockNextButton();
 
        if (resultBlock) resultBlock.style.display = 'none';
        if (popup) popup.classList.remove('mn-popup--visible');
    }
 
    // ─── Рендер покупок ───────────────────────────────────────────────────────
    function renderPurchases() {
        if (!purchaseList) return;
        purchaseList.innerHTML = '';
 
        state.purchases.forEach((item, index) => {
            const card = document.createElement('div');
            card.className = 'mn-purchase-card';
            card.dataset.index = index;
            card.innerHTML = `
<span class="mn-purchase-card__emoji">${item.emoji}</span>
<span class="mn-purchase-card__name">${item.name}</span>
<span class="mn-purchase-card__status"></span>
            `;
            card.addEventListener('click', () => onPurchaseClick(index));
            purchaseList.appendChild(card);
        });
    }
 
    // ─── Клик на покупку ─────────────────────────────────────────────────────
    function onPurchaseClick(index) {
        if (state.answered[index] !== undefined) return;
        if (!popup || !popupTitle) return;
 
        state.activePurchase = { index, ...state.purchases[index] };
        popupTitle.textContent = `${state.purchases[index].emoji} ${state.purchases[index].name}`;
        popup.classList.add('mn-popup--visible');
    }
 
    // ─── Выбор категории в попапе ─────────────────────────────────────────────
    function onChoiceClick(choice) {
        if (!state.activePurchase) return;
 
        const { index } = state.activePurchase;
        const item = state.purchases[index];
        const isCorrect = choice === item.correct;
 
        state.answered[index] = choice;
 
        // Обновляем карточку
        const card = purchaseList?.querySelector(`[data-index="${index}"]`);
        if (card) {
            card.classList.add(isCorrect ? 'mn-purchase-card--correct' : 'mn-purchase-card--wrong');
            const statusEl = card.querySelector('.mn-purchase-card__status');
            if (statusEl) statusEl.textContent = isCorrect ? '✓' : '✗';
        }
 
        // Добавляем в зону
        addToZone(item, choice, isCorrect);
 
        // Закрываем попап
        if (popup) popup.classList.remove('mn-popup--visible');
        state.activePurchase = null;
 
        updateProgress();
 
        // Все отвечены?
        if (Object.keys(state.answered).length === state.purchases.length) {
            setTimeout(showResult, 400);
        }
    }
 
    // ─── Добавляем в зону ─────────────────────────────────────────────────────
    function addToZone(item, zone, isCorrect) {
        const target = zone === 'mandatory' ? zoneMandatory : zoneDiscretion;
        if (!target) return;
 
        const chip = document.createElement('div');
        chip.className = `mn-zone-chip ${isCorrect ? 'mn-zone-chip--ok' : 'mn-zone-chip--err'}`;
        chip.textContent = `${item.emoji} ${item.name}`;
        target.appendChild(chip);
    }
 
    // ─── Прогресс ─────────────────────────────────────────────────────────────
    function updateProgress() {
        const total = state.purchases.length;
        const answered = Object.keys(state.answered).length;
        const pct = total ? (answered / total) * 100 : 0;
 
        if (progressFill) progressFill.style.width = pct + '%';
        if (progressLabel) progressLabel.textContent = `${answered} / ${total}`;
    }
 
    // ─── Итог ────────────────────────────────────────────────────────────────
    function showResult() {
        if (!resultBlock || !resultIcon || !resultScore || !resultText) return;
 
        const total = state.purchases.length;
        const correct = state.purchases.filter(
            (item, i) => state.answered[i] === item.correct
        ).length;
        const pct = Math.round((correct / total) * 100);
 
        resultIcon.textContent = pct === 100 ? '🎉' : pct >= 60 ? '👍' : '🤔';
        resultScore.textContent = `${correct} из ${total} правильно (${pct}%)`;
 
        if (pct === 100) {
            resultText.textContent = 'Отлично! Ты отлично разбираешься в категориях расходов.';
            unlockNextButton();
        } else if (pct >= 60) {
            resultText.textContent = 'Неплохо! Повтори материал и попробуй ещё раз для лучшего результата.';
            unlockNextButton();
        } else {
            resultText.textContent = 'Попробуй ещё раз — вспомни разницу между обязательными и дискреционными расходами.';
        }
 
        resultBlock.style.display = 'flex';
    }
 
    // ─── Кнопка "Дальше" ─────────────────────────────────────────────────────
    function lockNextButton() {
        if (!nextBtn) return;
        nextBtn.classList.add('mn-btn-locked');
        nextBtn.addEventListener('click', onNextClick);
    }
 
    function unlockNextButton() {
        if (!nextBtn) return;
        nextBtn.classList.remove('mn-btn-locked');
        const url = nextBtn.dataset.next;
        if (url && url !== '#') nextBtn.href = url;
        nextBtn.style.pointerEvents = 'auto';
        nextBtn.style.opacity = '1';
        nextBtn.style.cursor = 'pointer';
    }
 
    function onNextClick(e) {
        if (nextBtn?.classList.contains('mn-btn-locked')) {
            e.preventDefault();
            nextBtn.classList.add('mn-btn-shake');
            setTimeout(() => nextBtn?.classList.remove('mn-btn-shake'), 500);
        }
    }
 
    // ─── Очистка зон ─────────────────────────────────────────────────────────
    function clearZones() {
        if (zoneMandatory) zoneMandatory.innerHTML = '';
        if (zoneDiscretion) zoneDiscretion.innerHTML = '';
    }
 
    // ─── Попап: события ──────────────────────────────────────────────────────
    document.querySelectorAll('.mn-popup__btn').forEach(btn => {
        btn.addEventListener('click', () => onChoiceClick(btn.dataset.choice));
    });
 
    if (popupClose) {
        popupClose.addEventListener('click', () => {
            popup?.classList.remove('mn-popup--visible');
            state.activePurchase = null;
        });
    }
 
    if (popup) {
        popup.addEventListener('click', e => {
            if (e.target === popup) {
                popup.classList.remove('mn-popup--visible');
                state.activePurchase = null;
            }
        });
    }
 
    // ─── Повтор ───────────────────────────────────────────────────────────────
    if (retryBtn) {
        retryBtn.addEventListener('click', (e) => {
            e.preventDefault();
            init();
        });
    }
 
    // ─── Helpers ─────────────────────────────────────────────────────────────
    function shuffle(arr) {
        for (let i = arr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }
        return arr;
    }
 
    // ─── Старт ───────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', init);
    // На случай, если DOM уже загружен
    if (document.readyState !== 'loading') init();
 
})();