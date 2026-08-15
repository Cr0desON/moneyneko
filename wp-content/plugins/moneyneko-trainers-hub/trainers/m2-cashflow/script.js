(function () {
    'use strict';

    const CARDS = [
        { name: 'Стипендия', amount: 5000, type: 'income', emoji: '🎓' },
        { name: 'Проезд', amount: 1000, type: 'expense', emoji: '🚌' },
        { name: 'Еда в столовой', amount: 4000, type: 'expense', emoji: '🍱' },
        { name: 'Кино', amount: 500, type: 'expense', emoji: '🍿' },
        { name: 'Подарок на ДР', amount: 3000, type: 'income', emoji: '🎁' },
        { name: 'Одежда', amount: 2000, type: 'expense', emoji: '👕' },
        { name: 'Подработка', amount: 2000, type: 'income', emoji: '💻' },
        { name: 'Интернет', amount: 500, type: 'expense', emoji: '🌐' },
        { name: 'Кешбэк', amount: 500, type: 'income', emoji: '💳' },
        { name: 'Кофе', amount: 500, type: 'expense', emoji: '☕' }
    ];

    let state = {
        fallingQueue: [], caughtCards: [], catLane: 0, currentFallingNode: null,
        gameInterval: null, totalIncome: 0, totalExpense: 0, actualCashflow: ''
    };

    const $ = id => document.getElementById(id);
    const screens = { intro: $('mn-m2-intro'), catch: $('mn-m2-catch'), sort: $('mn-m2-sort'), calc: $('mn-m2-calc') };

    if (!screens.intro) return;

    function showScreen(screenName) {
        Object.values(screens).forEach(s => s.classList.remove('mn-m2-screen--active'));
        screens[screenName].classList.add('mn-m2-screen--active');
    }

    $('mn-m2-start-btn').addEventListener('click', () => {
        document.body.classList.add('mn-hide-sidebar');

        state.fallingQueue = [...CARDS].sort(() => Math.random() - 0.5);
        state.caughtCards = [];
        $('mn-caught-count').textContent = '0';
        showScreen('catch');
        startCatchGame();
    });

    const cat = $('mn-cat');
    const catchArea = $('mn-catch-area');

    function startCatchGame() {
        document.addEventListener('keydown', handleKey);
        $('mn-btn-left').addEventListener('click', () => moveCat(0));
        $('mn-btn-right').addEventListener('click', () => moveCat(1));
        spawnCard();
    }

    function moveCat(lane) {
        state.catLane = lane;
        cat.style.left = lane === 0 ? '25%' : '75%';
    }

    function handleKey(e) {
        // Поддержка стрелочек, A/D и русских Ф/В
        if (['ArrowLeft', 'a', 'A', 'ф', 'Ф'].includes(e.key)) moveCat(0);
        if (['ArrowRight', 'd', 'D', 'в', 'В'].includes(e.key)) moveCat(1);
    }

    function spawnCard() {
        if (state.fallingQueue.length === 0) {
            endCatchGame(); return;
        }

        const cardData = state.fallingQueue.shift();
        const cardNode = document.createElement('div');
        cardNode.className = 'mn-falling-card';
        cardNode.innerHTML = `<span class="emoji">${cardData.emoji}</span><strong>${cardData.name}</strong><br>${cardData.amount} ₽`;

        const spawnLane = Math.random() > 0.5 ? 1 : 0;
        cardNode.style.left = spawnLane === 0 ? '25%' : '75%';
        cardNode.dataset.lane = spawnLane;

        catchArea.appendChild(cardNode);
        state.currentFallingNode = cardNode;

        let posY = -100;
        const targetY = catchArea.offsetHeight - 90;

        state.gameInterval = setInterval(() => {
            posY += 4;
            cardNode.style.top = posY + 'px';

            if (posY >= targetY) {
                clearInterval(state.gameInterval);
                cardNode.remove();

                if (state.catLane == cardNode.dataset.lane) {
                    state.caughtCards.push(cardData);
                    $('mn-caught-count').textContent = state.caughtCards.length;
                    cat.style.transform = 'translateX(-50%) scale(1.1)';
                    setTimeout(() => cat.style.transform = 'translateX(-50%) scale(1)', 150);
                } else {
                    state.fallingQueue.push(cardData);
                }
                setTimeout(spawnCard, 200);
            }
        }, 20);
    }

    function endCatchGame() {
        document.removeEventListener('keydown', handleKey);
        setTimeout(() => {
            initSortPhase();
            showScreen('sort');
        }, 500);
    }

    function initSortPhase() {
        const source = $('mn-sort-source');
        source.innerHTML = '';
        state.totalIncome = 0;
        state.totalExpense = 0;

        state.caughtCards.forEach((item, index) => {
            const card = document.createElement('div');
            card.className = 'mn-draggable-card';
            card.draggable = true;
            card.dataset.index = index;
            card.dataset.type = item.type;
            card.innerHTML = `
                <div class="mn-card-emoji">${item.emoji}</div>
                <div class="mn-card-details">
                    <span class="mn-card-name">${item.name}</span>
                    <span class="mn-card-amount">${item.amount} ₽</span>
                </div>
            `;
            card.addEventListener('dragstart', handleDragStart);
            card.addEventListener('dragend', handleDragEnd);
            card.addEventListener('click', handleMobileClick);
            source.appendChild(card);
        });

        const zones = document.querySelectorAll('.mn-sort-zone');
        zones.forEach(zone => {
            zone.addEventListener('dragover', handleDragOver);
            zone.addEventListener('dragleave', handleDragLeave);
            zone.addEventListener('drop', handleDrop);
        });
    }

    let draggedCard = null;

    function handleDragStart(e) {
        draggedCard = this;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', this.dataset.index);
        setTimeout(() => this.classList.add('dragging'), 0);
    }

    function handleDragEnd() {
        this.classList.remove('dragging');
        draggedCard = null;
        checkSortCompletion();
    }

    function handleDragOver(e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; this.classList.add('drag-over'); }
    function handleDragLeave() { this.classList.remove('drag-over'); }

    function handleDrop(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        if (!draggedCard) return;

        const expectedType = draggedCard.dataset.type;
        const targetType = this.dataset.type;

        if (expectedType === targetType) {
            this.querySelector('.mn-zone-items').appendChild(draggedCard);
            draggedCard.draggable = false;
            draggedCard.style.cursor = 'default';
            draggedCard.style.pointerEvents = 'none';

            const amount = state.caughtCards[draggedCard.dataset.index].amount;
            if (targetType === 'income') state.totalIncome += amount;
            if (targetType === 'expense') state.totalExpense += amount;
        } else {
            draggedCard.style.borderColor = '#e05252';
            draggedCard.style.transform = 'translateX(5px)';
            setTimeout(() => { draggedCard.style.borderColor = ''; draggedCard.style.transform = ''; }, 300);
        }
    }

    let selectedCardForMobile = null;
    function handleMobileClick() {
        if (!this.draggable) return;
        if (selectedCardForMobile) selectedCardForMobile.style.boxShadow = '';
        selectedCardForMobile = this;
        this.style.boxShadow = '0 0 0 3px #5b67f8';
    }

    document.querySelectorAll('.mn-sort-zone').forEach(zone => {
        zone.addEventListener('click', function() {
            if (!selectedCardForMobile) return;
            const expectedType = selectedCardForMobile.dataset.type;
            const targetType = this.dataset.type;

            if (expectedType === targetType) {
                this.querySelector('.mn-zone-items').appendChild(selectedCardForMobile);
                selectedCardForMobile.draggable = false;
                selectedCardForMobile.style.boxShadow = '';
                selectedCardForMobile.style.cursor = 'default';
                selectedCardForMobile.style.pointerEvents = 'none';

                const amount = state.caughtCards[selectedCardForMobile.dataset.index].amount;
                if (targetType === 'income') state.totalIncome += amount;
                if (targetType === 'expense') state.totalExpense += amount;

                selectedCardForMobile = null;
                checkSortCompletion();
            } else {
                selectedCardForMobile.style.borderColor = '#e05252';
                setTimeout(() => selectedCardForMobile.style.borderColor = '', 300);
            }
        });
    });

    function checkSortCompletion() {
        const sourceCards = document.querySelectorAll('#mn-sort-source .mn-draggable-card');
        if (sourceCards.length === 0) {
            $('mn-m2-calc-btn').classList.remove('mn-btn-locked');
        }
    }

    $('mn-m2-calc-btn').addEventListener('click', () => {
        $('mn-calc-income-sum').textContent = `${state.totalIncome} ₽`;
        $('mn-calc-expense-sum').textContent = `${state.totalExpense} ₽`;
        const diff = state.totalIncome - state.totalExpense;
        if (diff > 0) state.actualCashflow = 'positive';
        else if (diff < 0) state.actualCashflow = 'negative';
        else state.actualCashflow = 'zero';
        showScreen('calc');
    });

    document.querySelectorAll('.mn-calc-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const answer = e.target.dataset.answer;
            const resultBlock = $('mn-m2-result');
            const retryBtn = $('mn-m2-retry-btn');

            if (answer === state.actualCashflow) {
                $('mn-m2-result-icon').textContent = '🎉';
                $('mn-m2-result-score').textContent = 'Абсолютно верно!';
                $('mn-m2-result-text').textContent = `Доходы минус Расходы = ${state.totalIncome - state.totalExpense} ₽. Это отличный результат!`;

                retryBtn.textContent = 'Перейти дальше ➔';
                retryBtn.style.backgroundColor = '#48bb78';
                retryBtn.onclick = function() {
                    const nativeNextBtn = document.querySelector('.tutor-course-topic-single-footer .tutor-single-course-content-next a, .tutor-course-topic-single-footer .tutor-single-course-content-next button, .tutor-topbar-mark-btn');
                    if (nativeNextBtn) {
                        nativeNextBtn.click();
                    } else {
                        alert('Отличная работа! Теперь нажмите кнопку завершения урока в верхней или нижней части экрана.');
                        $('mn-m2-result').style.display = 'none';
                    }
                };
            } else {
                $('mn-m2-result-icon').textContent = '🤔';
                $('mn-m2-result-score').textContent = 'Ошибочка...';
                $('mn-m2-result-text').textContent = 'Посмотри внимательно на доски. Доходы минус Расходы... Какой получается знак?';

                retryBtn.textContent = 'Попробовать ещё раз';
                retryBtn.style.backgroundColor = '#f56565';
                retryBtn.onclick = function() {
                    $('mn-m2-result').style.display = 'none';
                };
            }
            resultBlock.style.display = 'flex';
        });
    });

})();