<?php if (!defined('ABSPATH')) exit; ?>

<div class="mn-trainer" id="mn-trainer-m3">
    <div id="mn-m3-intro" class="mn-m3-screen mn-m3-screen--active">
        <div class="mn-m3-intro-content">
            <div class="mn-m3-icon-wrap">🏪</div>
            <h2 class="mn-trainer__title">Учет покупок</h2>
            <p class="mn-trainer__subtitle">Забеги в киоск, купи то, что нравится, а затем правильно внеси эти траты в свою электронную таблицу.</p>
            <button class="mn-btn-start" id="mn-m3-start-btn">Пойти за покупками 🛒</button>
        </div>
    </div>

    <div id="mn-m3-shop" class="mn-m3-screen">
        <div class="mn-shop-header">
            <div>
                <h2 class="mn-trainer__title">Киоск MoneyNeko</h2>
                <p class="mn-trainer__subtitle">Выбери от 2 до 5 товаров</p>
            </div>
            <div class="mn-cart-summary">
                В корзине: <span id="mn-cart-count">0</span> шт.
            </div>
        </div>

        <div class="mn-products-grid" id="mn-products-grid">
        </div>

        <div class="mn-shop-footer">
            <button class="mn-btn-start mn-btn-locked" id="mn-m3-checkout-btn">На кассу 💳</button>
        </div>
    </div>

    <div id="mn-m3-excel" class="mn-m3-screen">
        <h2 class="mn-trainer__title">Внеси данные в таблицу</h2>
        <p class="mn-trainer__subtitle">Перенеси данные из чека в электронную таблицу. Укажи правильные категории и суммы.</p>

        <div class="mn-excel-layout">
            <div class="mn-receipt-wrap">
                <div class="mn-receipt">
                    <div class="mn-receipt-header">ЧЕК №001<br>MoneyNeko Маркет</div>
                    <ul class="mn-receipt-items" id="mn-receipt-items">
                    </ul>
                    <div class="mn-receipt-total">
                        ИТОГО: <span id="mn-receipt-sum">0</span> ₽
                    </div>
                </div>
            </div>

            <div class="mn-spreadsheet-wrap">
                <table class="mn-spreadsheet">
                    <thead>
                    <tr>
                        <th class="mn-col-num">№</th>
                        <th class="mn-col-name">Название операции</th>
                        <th class="mn-col-cat">Категория</th>
                        <th class="mn-col-sum">Сумма (₽)</th>
                    </tr>
                    </thead>
                    <tbody id="mn-excel-tbody">
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right; font-weight: bold; padding-right: 15px;">ИТОГО РАСХОДОВ:</td>
                        <td><input type="number" id="mn-excel-total-input" placeholder="0" class="mn-cell-input mn-total-input"></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mn-excel-footer">
            <button class="mn-btn-start" id="mn-m3-check-btn">Проверить таблицу 📊</button>
        </div>
    </div>

    <div class="mn-trainer__result" id="mn-m3-result" style="display:none;">
        <div class="mn-result__inner">
            <div class="mn-result__icon" id="mn-m3-result-icon"></div>
            <div class="mn-result__score" id="mn-m3-result-score"></div>
            <div class="mn-result__text" id="mn-m3-result-text"></div>
            <button class="mn-result__retry" id="mn-m3-retry-btn">Исправить ошибку</button>
        </div>
    </div>
</div>