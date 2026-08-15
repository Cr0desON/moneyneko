(function () {
    'use strict';

    // ─── ДАННЫЕ С РЕАЛЬНЫМИ КАТЕГОРИЯМИ ────────────────────────────────────
    const CATEGORIES = [
        { id: 'food', name: 'Еда и напитки' },
        { id: 'stationery', name: 'Канцелярия' },
        { id: 'electronics', name: 'Электроника' },
        { id: 'entertainment', name: 'Развлечения' }
    ];

    const PRODUCTS = [
        { id: 1, name: 'Кофе Латте', price: 150, emoji: '☕', category: 'food' },
        { id: 2, name: 'Круассан', price: 120, emoji: '🥐', category: 'food' },
        { id: 3, name: 'Шаурма', price: 250, emoji: '🌯', category: 'food' },
        { id: 4, name: 'Тетрадь', price: 180, emoji: '📓', category: 'stationery' },
        { id: 5, name: 'Ручка', price: 50, emoji: '🖊️', category: 'stationery' },
        { id: 6, name: 'Наушники', price: 1500, emoji: '🎧', category: 'electronics' },
        { id: 7, name: 'Билет в кино', price: 400, emoji: '🍿', category: 'entertainment' },
        { id: 8, name: 'Свежевыжатый сок', price: 200, emoji: '🥤', category: 'food' }
    ];

    let state = {
        cart: [],
        totalSum: 0
    };

    const $ = id => document.getElementById(id);
    const screens = {
        intro: $('mn-m3-intro'),
        shop: $('mn-m3-shop'),
        excel: $('mn-m3-excel')
    };

    if (!screens.intro) return;

    function showScreen(screenName) {
        Object.values(screens).forEach(s => s.classList.remove('mn-m3-screen--active'));
        screens[screenName].classList.add('mn-m3-screen--active');
    }

    // ─── 1. СТАРТ И СКРЫТИЕ САЙДБАРА ───────────────────────────────────────
    $('mn-m3-start-btn').addEventListener('click', () => {
        document.body.classList.add('mn-hide-sidebar'); // Прячем меню

        renderShop();
        showScreen('shop');
    });

    // ─── 2. ЛОГИКА КИОСКА ──────────────────────────────────────────────────
    function renderShop() {
        const grid = $('mn-products-grid');
        grid.innerHTML = '';
        state.cart = [];
        updateCartUI();

        PRODUCTS.forEach(prod => {
            const card = document.createElement('div');
            card.className = 'mn-product-card';
            card.innerHTML = `
                <div class="mn-prod-emoji">${prod.emoji}</div>
                <div class="mn-prod-name">${prod.name}</div>
                <div class="mn-prod-price">${prod.price} ₽</div>
            `;

            card.addEventListener('click', () => {
                const index = state.cart.findIndex(item => item.id === prod.id);
                if (index > -1) {
                    state.cart.splice(index, 1);
                    card.classList.remove('selected');
                } else {
                    if (state.cart.length >= 5) {
                        alert('Слишком много покупок! Давай остановимся на пяти.');
                        return;
                    }
                    state.cart.push(prod);
                    card.classList.add('selected');
                }
                updateCartUI();
            });

            grid.appendChild(card);
        });
    }

    function updateCartUI() {
        $('mn-cart-count').textContent = state.cart.length;
        const checkoutBtn = $('mn-m3-checkout-btn');

        if (state.cart.length >= 2 && state.cart.length <= 5) {
            checkoutBtn.classList.remove('mn-btn-locked');
        } else {
            checkoutBtn.classList.add('mn-btn-locked');
        }
    }

    $('mn-m3-checkout-btn').addEventListener('click', () => {
        setupExcelPhase();
        showScreen('excel');
    });

    // ─── 3. ЛОГИКА ТАБЛИЦЫ (EXCEL) ──────────────────────────────────────────
    function setupExcelPhase() {
        const receiptList = $('mn-receipt-items');
        receiptList.innerHTML = '';
        state.totalSum = 0;

        state.cart.forEach(item => {
            state.totalSum += item.price;
            receiptList.innerHTML += `<li><span>${item.name}</span><span>${item.price}</span></li>`;
        });
        $('mn-receipt-sum').textContent = state.totalSum;

        const tbody = $('mn-excel-tbody');
        tbody.innerHTML = '';

        let productOptions = '<option value="" disabled selected>-- Выберите --</option>';
        PRODUCTS.forEach(p => {
            productOptions += `<option value="${p.id}">${p.name}</option>`;
        });

        let categoryOptions = '<option value="" disabled selected>-- Категория --</option>';
        CATEGORIES.forEach(c => {
            categoryOptions += `<option value="${c.id}">${c.name}</option>`;
        });

        for (let i = 0; i < state.cart.length; i++) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${i + 1}</td>
                <td><select class="mn-cell-select mn-excel-item">${productOptions}</select></td>
                <td><select class="mn-cell-select mn-excel-cat">${categoryOptions}</select></td>
                <td><input type="number" class="mn-cell-input mn-excel-amount" placeholder="0"></td>
            `;
            tbody.appendChild(tr);
        }
    }

    // ─── 4. ПРОВЕРКА ДАННЫХ В ТАБЛИЦЕ ──────────────────────────────────────
    $('mn-m3-check-btn').addEventListener('click', () => {
        document.querySelectorAll('.mn-cell-error').forEach(el => el.classList.remove('mn-cell-error'));

        let hasError = false;
        let filledItems = [];

        const rows = document.querySelectorAll('#mn-excel-tbody tr');

        rows.forEach(row => {
            const itemSelect = row.querySelector('.mn-excel-item');
            const catSelect = row.querySelector('.mn-excel-cat');
            const amountInput = row.querySelector('.mn-excel-amount');

            const itemId = parseInt(itemSelect.value);
            const categoryId = catSelect.value;
            const amount = parseInt(amountInput.value);

            if (!itemId || !categoryId || isNaN(amount)) {
                hasError = true;
                if (!itemId) itemSelect.classList.add('mn-cell-error');
                if (!categoryId) catSelect.classList.add('mn-cell-error');
                if (isNaN(amount)) amountInput.classList.add('mn-cell-error');
                return;
            }

            const realProduct = PRODUCTS.find(p => p.id === itemId);

            if (realProduct.price !== amount) {
                hasError = true;
                amountInput.classList.add('mn-cell-error');
            }

            if (realProduct.category !== categoryId) {
                hasError = true;
                catSelect.classList.add('mn-cell-error');
            }

            filledItems.push(itemId);
        });

        const totalInput = $('mn-excel-total-input');
        if (parseInt(totalInput.value) !== state.totalSum) {
            hasError = true;
            totalInput.classList.add('mn-cell-error');
        }

        const cartIds = state.cart.map(i => i.id).sort();
        const filledIds = filledItems.sort();

        if (JSON.stringify(cartIds) !== JSON.stringify(filledIds)) {
            hasError = true;
        }

        const resultBlock = $('mn-m3-result');
        const retryBtn = $('mn-m3-retry-btn');

        if (hasError) {
            $('mn-m3-result-icon').textContent = '📝';
            $('mn-m3-result-score').textContent = 'В отчете ошибка!';
            $('mn-m3-result-text').textContent = 'Красным подсвечены ячейки, где есть неточность. Проверь чек, названия, категории и итоговую сумму.';

            retryBtn.textContent = 'Исправить ошибки';
            retryBtn.style.backgroundColor = '#f56565';
            retryBtn.onclick = function() {
                resultBlock.style.display = 'none';
            };
        } else {
            $('mn-m3-result-icon').textContent = '💼';
            $('mn-m3-result-score').textContent = 'Идеальная бухгалтерия!';
            $('mn-m3-result-text').textContent = 'Таблица заполнена абсолютно верно. Теперь ты умеешь фиксировать свои расходы!';

            // КНОПКА "ПЕРЕЙТИ ДАЛЬШЕ"
            retryBtn.textContent = 'Перейти дальше ➔';
            retryBtn.style.backgroundColor = '#48bb78';
            retryBtn.onclick = function() {
                const nativeNextBtn = document.querySelector('.tutor-course-topic-single-footer .tutor-single-course-content-next a, .tutor-course-topic-single-footer .tutor-single-course-content-next button, .tutor-topbar-mark-btn');

                if (nativeNextBtn) {
                    nativeNextBtn.click();
                } else {
                    alert('Отличная работа! Теперь нажмите кнопку завершения урока в верхней или нижней части экрана.');
                    resultBlock.style.display = 'none';
                }
            };
        }

        resultBlock.style.display = 'flex';
    });

})();