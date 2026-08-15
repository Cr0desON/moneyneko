(function () {
    'use strict';

    // Цены убраны, осталась только нужная для геймплея логика
    const RECEIPTS_DATA = [
        { id: 1, name: 'Счёт за интернет', emoji: '🌐', correct: 'mandatory', top: '41%', left: '22%' },
        { id: 2, name: 'Обед в фастфуде', emoji: '🍔', correct: 'non_mandatory', top: '74%', left: '13%' },
        { id: 3, name: 'Кофе на вынос', emoji: '☕', correct: 'non_mandatory', top: '83%', left: '14%' },
        { id: 4, name: 'Учебники', emoji: '📚', correct: 'mandatory', top: '94%', left: '10%' },
        { id: 5, name: 'Билет в кино', emoji: '🎬', correct: 'desirable', top: '70%', left: '50%' },
        { id: 6, name: 'Новые кроссовки', emoji: '👟', correct: 'desirable', top: '88%', left: '40%' },
        { id: 7, name: 'Корм для кота', emoji: '🐈', correct: 'mandatory', top: '66%', left: '63%' },
        { id: 8, name: 'Химчистка куртки', emoji: '🧥', correct: 'non_mandatory', top: '18%', left: '80%' }
    ];

    let state = { foundIds: [], answered: {}, activeId: null };
    let timerInterval = null;
    let secondsElapsed = 0;

    const $ = id => document.getElementById(id);
    const roomContainer = $('mn2-room');
    const purchasesList = $('mn2-purchases-list');
    const popup = $('mn2-popup');

    // Функция скрытия сайдбара
    function hideTutorSidebar() {
        document.body.classList.add('mn2-hide-sidebar');
    }

    function init() {
        if (!roomContainer) return;

        $('mn2-btn-start').onclick = startSearch;
        $('mn2-popup-close').onclick = () => popup.style.display = 'none';

        $('mn2-btn-finish-early').onclick = function() {
            if (state.foundIds.length < RECEIPTS_DATA.length) {
                alert('Ты нашел не все чеки! Поищи ещё (их спрятано 8 штук).');
            } else {
                startCategorization();
            }
        };

        document.querySelectorAll('#mn2-popup .mn2-popup-buttons button').forEach(btn => {
            btn.onclick = () => handleChoice(btn.dataset.choice);
        });

        renderHotspots();
    }

    function startSearch() {
        $('mn2-phase-intro').style.display = 'none';
        $('mn2-phase-search').style.display = 'block';
        
        hideTutorSidebar();
        
        secondsElapsed = 0;
        $('mn2-timer').textContent = '00:00';
        if (timerInterval) clearInterval(timerInterval);
        
        timerInterval = setInterval(() => {
            secondsElapsed++;
            let m = Math.floor(secondsElapsed / 60).toString().padStart(2, '0');
            let s = (secondsElapsed % 60).toString().padStart(2, '0');
            if ($('mn2-timer')) $('mn2-timer').textContent = `${m}:${s}`;
        }, 1000);
    }

    function renderHotspots() {
        roomContainer.innerHTML = '';
        RECEIPTS_DATA.forEach(item => {
            const btn = document.createElement('button');
            btn.className = 'mn2-hotspot';
            btn.style.top = item.top;
            btn.style.left = item.left;
            btn.onclick = () => findReceipt(item.id, btn);
            roomContainer.appendChild(btn);
        });
    }

    function findReceipt(id, btnElement) {
        if (state.foundIds.includes(id)) return;
        state.foundIds.push(id);
        
        btnElement.className = 'mn2-hotspot-found';
        btnElement.innerHTML = '✓';
        $('mn2-found-count').textContent = state.foundIds.length;
    }

    function startCategorization() {
        clearInterval(timerInterval);
        $('mn2-summary-time').textContent = $('mn2-timer').textContent;
        $('mn2-phase-search').style.display = 'none';
        $('mn2-phase-categorize').style.display = 'block';
        renderFoundPurchases();
    }

    function renderFoundPurchases() {
        purchasesList.innerHTML = '';
        const foundItems = RECEIPTS_DATA.filter(item => state.foundIds.includes(item.id));
        
        foundItems.forEach(item => {
            const card = document.createElement('div');
            card.className = 'mn2-p-card';
            card.dataset.id = item.id;
            card.innerHTML = `<span>${item.emoji} ${item.name}</span> <span class="status"></span>`;
            card.onclick = () => { if (!state.answered[item.id]) openPopup(item); };
            purchasesList.appendChild(card);
        });
    }

    function openPopup(item) {
        state.activeId = item.id;
        $('mn2-popup-title').textContent = `${item.emoji} ${item.name}`;
        popup.style.display = 'flex';
    }

    function handleChoice(choice) {
        const item = RECEIPTS_DATA.find(i => i.id === state.activeId);
        const isCorrect = item.correct === choice;
        state.answered[item.id] = choice;

        const card = purchasesList.querySelector(`[data-id="${item.id}"]`);
        card.classList.add(isCorrect ? 'mn2-ok' : 'mn2-err');
        card.querySelector('.status').textContent = isCorrect ? '✓' : '✗';

        const zone = $(`mn2-z-${choice}`);
        const chip = document.createElement('div');
        chip.className = `mn2-chip ${isCorrect ? 'mn2-chip-ok' : 'mn2-chip-err'}`;
        chip.textContent = `${item.emoji} ${item.name}`;
        zone.appendChild(chip);

        popup.style.display = 'none';
        
        if (Object.keys(state.answered).length === state.foundIds.length) {
            setTimeout(showResult, 600);
        }
    }

    function showResult() {
        const totalFound = state.foundIds.length;
        let correctCount = 0;

        state.foundIds.forEach(id => {
            const item = RECEIPTS_DATA.find(i => i.id === id);
            if (state.answered[id] === item.correct) {
                correctCount++;
            }
        });

        const pct = totalFound ? Math.round((correctCount / totalFound) * 100) : 0;
        const retryBtn = $('mn2-btn-retry');

        $('mn2-result-score').textContent = `Правильно: ${correctCount} из ${totalFound}`;
        $('mn2-summary-box').style.display = 'none'; 
        
        if (pct >= 80 && totalFound === RECEIPTS_DATA.length) {
            $('mn2-result-emoji').textContent = '🎉';
            $('mn2-result-text').textContent = 'Потрясающе! Все чеки найдены и распределены правильно.';
            $('mn2-summary-box').style.display = 'block'; 
            
            retryBtn.textContent = 'Перейти дальше ➔';
            retryBtn.style.background = '#4caf50'; 
            
            retryBtn.onclick = function() {
                const nativeNextBtn = document.querySelector('.tutor-course-topic-single-footer .tutor-single-course-content-next a, .tutor-course-topic-single-footer .tutor-single-course-content-next button, .tutor-topbar-mark-btn');
                
                if (nativeNextBtn) {
                    nativeNextBtn.click();
                } else {
                    alert('Отличная работа! Теперь нажмите кнопку завершения урока в верхней или нижней части экрана.');
                    $('mn2-result-popup').style.display = 'none'; 
                }
            };
        } else {
            $('mn2-result-emoji').textContent = '🤔';
            $('mn2-result-text').textContent = 'Чтобы пройти дальше, не ошибайся в категориях (минимум 80% правильных).';
            
            retryBtn.textContent = 'Попробовать снова';
            retryBtn.style.background = '#5b67f8'; 
            retryBtn.onclick = resetGame;
        }

        $('mn2-result-popup').style.display = 'flex';
    }

    function resetGame() {
        state = { foundIds: [], answered: {}, activeId: null };
        if (timerInterval) clearInterval(timerInterval);
        $('mn2-result-popup').style.display = 'none';
        $('mn2-phase-categorize').style.display = 'none';
        $('mn2-phase-search').style.display = 'none';
        $('mn2-phase-intro').style.display = 'block';
        $('mn2-summary-box').style.display = 'none';
        ['mandatory', 'non_mandatory', 'desirable'].forEach(z => {
            const zone = $(`mn2-z-${z}`);
            if (zone) zone.innerHTML = '';
        });
        renderHotspots();
        document.body.classList.remove('mn2-hide-sidebar'); // Возвращаем меню если решили перезапустить
    }

    document.addEventListener('DOMContentLoaded', init);
})();