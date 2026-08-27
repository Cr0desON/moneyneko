<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="sketch-landing-wrapper">
    <div class="top-buttons-container">
        <a href="<?php echo esc_url( home_url( '/profile-2/' ) ); ?>" class="sketch-btn-profile">Профиль</a>
        <a href="<?php echo esc_url( home_url( '/доска-лидеров/' ) ); ?>" class="sketch-btn-leaderboard">Доска лидеров</a>
    </div>

    <div class="sketch-bg-deco deco-left">📈 💳<br>📊</div>
    <div class="sketch-bg-deco deco-right">🍕 🧮<br>🪙 🐖</div>
    
    <div class="sketch-hero-section">
        <div class="cat-container">
            <!-- ПОЛОСА НАСТРОЕНИЯ -->
            <div class="mood-progress-wrapper" title="Настроение кота: <?php echo $mood_points; ?>/100">
                <div class="mood-progress-text"><?php echo $mood_points; ?> / 100</div>
                <div class="mood-progress-container">
                    <div class="mood-progress-fill" style="width: <?php echo $mood_points; ?>%;"></div>
                </div>
            </div>

            <img src="<?php echo esc_url( $cat_image_url ); ?>" alt="Манеки-нэко" class="cat-image">

            <?php if ( is_user_logged_in() ) : ?>
                <div class="right-bottom-badges">
                    <div class="streak-badge">
                        🔥 <?php echo $streak; ?> <span class="badge-label">дней</span>
                    </div>
                    <div class="progress-xp-badge">
                        <?php echo $progress_points; ?> <span class="badge-label">XP</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <a href="<?php echo esc_url( home_url( '/courses/moneyneko-full-course/lessons/твой-первый-шаг-к-финансовой-свободе-2/' ) ); ?>" class="sketch-btn-start">Начать обучение</a>
    </div>

    <div class="sketch-modules-grid">
        <a href="<?php echo esc_url( home_url( '/courses/moneyneko-full-course/lessons/тема-1-cash-flow-как-отслеживать-притоки-и-отто/' ) ); ?>" class="sketch-module mod-1">
            <div class="module-title">Модуль 1.</div>
            <div class="module-desc">Учет денежных<br>потоков</div>
            <div class="module-icon">💰</div>
        </a>

        <a href="<?php echo esc_url( home_url( '/courses/moneyneko-full-course/lessons/тема-1-zero-based-budgeting-бюджет-с-нуля/' ) ); ?>" class="sketch-module mod-2">
            <div class="module-title">Модуль 2.</div>
            <div class="module-desc">Гибкое<br>планирование</div>
            <div class="module-icon">📅</div>
        </a>

        <a href="<?php echo esc_url( home_url( '/courses/moneyneko-full-course/lessons/тема-1-ликвидность-способность-отвеча/' ) ); ?>" class="sketch-module mod-3">
            <div class="module-title">Модуль 3.</div>
            <div class="module-desc">Управление<br>ликвидностью и<br>покупкой<br>безопасности</div>
            <div class="module-icon">🛡️</div>
        </a>

        <a href="<?php echo esc_url( home_url( '/courses/moneyneko-full-course/lessons/тема-1-совокупная-стоимость-владения-tco/' ) ); ?>" class="sketch-module mod-4">
            <div class="module-title">Модуль 4.</div>
            <div class="module-desc">Рациональное<br>принятие<br>решений</div>
            <div class="module-icon">💡</div>
        </a>

        <a href="<?php echo esc_url( home_url( '/courses/moneyneko-full-course/lessons/тема-1-налоговый-вычет-за-обучение-и-ле/' ) ); ?>" class="sketch-module mod-5">
            <div class="module-title">Модуль 5.</div>
            <div class="module-desc">Инструменты<br>оптимизации<br>и автоматизации</div>
            <div class="module-icon">⚙️</div>
        </a>
    </div>
</div>