<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<?php
$streak_last_two = (int) $streak % 100;
$streak_last     = (int) $streak % 10;
$streak_label    = 'дней';
if ( $streak_last_two < 11 || $streak_last_two > 14 ) {
    if ( 1 === $streak_last ) {
        $streak_label = 'день';
    } elseif ( $streak_last >= 2 && $streak_last <= 4 ) {
        $streak_label = 'дня';
    }
}
?>

<aside class="mn-game-sidebar" aria-label="Игровая навигация">
    <div class="mn-game-brand">
        <div class="mn-game-cat-avatar">
            <img src="<?php echo esc_url( home_url( '/image/funcat.png' ) ); ?>" alt="Грош, маскот MoneyNeko" class="cat-image" id="mn-tour-cat">
        </div>
        <span class="mn-game-brand-name">MoneyNeko</span>
    </div>

    <div class="mn-game-sidebar-stats">
        <?php if ( is_user_logged_in() ) : ?>
            <div class="mn-sidebar-stat streak-badge" id="mn-tour-streak">
                <span class="mn-sidebar-stat-icon" aria-hidden="true">🔥</span>
                <span><?php echo esc_html( $streak . ' ' . $streak_label ); ?></span>
            </div>
            <div class="mn-sidebar-stat progress-xp-badge" id="mn-tour-points">
                <span class="mn-sidebar-stat-icon mn-xp-icon" aria-hidden="true">XP</span>
                <span><?php echo esc_html( $progress_points ); ?> XP</span>
            </div>
        <?php endif; ?>
    </div>

    <nav class="mn-game-sidebar-nav" aria-label="Разделы">
        <a href="<?php echo esc_url( home_url( '/главная/game/' ) ); ?>"
           class="mn-sidebar-link<?php echo 'map' === $active_page ? ' is-active' : ''; ?>"
           id="mn-tour-map" <?php echo 'map' === $active_page ? 'aria-current="page"' : ''; ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 7 6-3 6 3 6-3v13l-6 3-6-3-6 3V7Z"/><path d="M9 4v13m6-10v13"/></svg>
            <span>Карта</span>
        </a>
        <a href="<?php echo esc_url( home_url( '/profile/' ) ); ?>"
           class="mn-sidebar-link sketch-btn-profile<?php echo 'profile' === $active_page ? ' is-active' : ''; ?>"
           id="mn-tour-profile" <?php echo 'profile' === $active_page ? 'aria-current="page"' : ''; ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
            <span>Профиль</span>
        </a>
        <a href="<?php echo esc_url( home_url( '/доска-лидеров/' ) ); ?>"
           class="mn-sidebar-link sketch-btn-leaderboard<?php echo 'leaderboard' === $active_page ? ' is-active' : ''; ?>"
           id="mn-tour-leaderboard" <?php echo 'leaderboard' === $active_page ? 'aria-current="page"' : ''; ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 21h8m-4-4v4M7 4h10v5a5 5 0 0 1-10 0V4Z"/><path d="M7 6H4v2a4 4 0 0 0 4 4m9-6h3v2a4 4 0 0 1-4 4"/></svg>
            <span>Лидерборд</span>
        </a>
        <a href="<?php echo esc_url( home_url( '/cat-room/' ) ); ?>"
           class="mn-sidebar-link sketch-btn-cat-room<?php echo 'cat-room' === $active_page ? ' is-active' : ''; ?>"
           id="mn-tour-cat-room" <?php echo 'cat-room' === $active_page ? 'aria-current="page"' : ''; ?>>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 11 9-7 9 7v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-9Z"/><path d="M9 21v-6h6v6M8 11h.01M16 11h.01"/></svg>
            <span>Комната кота</span>
        </a>
    </nav>

    <?php if ( is_user_logged_in() ) : ?>
        <button type="button" id="mn-tutorial-help" class="mn-tutorial-help" title="Показать обучение" aria-label="Показать обучение">?</button>
    <?php endif; ?>
</aside>
