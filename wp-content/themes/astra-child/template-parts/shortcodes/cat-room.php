<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<section class="mn-cat-room" aria-labelledby="mn-cat-room-title">
    <header class="mn-cat-room__header">
        <span class="mn-cat-room__title-paw" aria-hidden="true">
            <svg viewBox="0 0 64 64"><ellipse cx="13" cy="26" rx="7" ry="10" transform="rotate(-30 13 26)"/><ellipse cx="27" cy="15" rx="7" ry="10" transform="rotate(-10 27 15)"/><ellipse cx="43" cy="16" rx="7" ry="10" transform="rotate(14 43 16)"/><ellipse cx="54" cy="29" rx="7" ry="10" transform="rotate(34 54 29)"/><path d="M13 51c0-12 8-21 19-21s19 9 19 21c0 8-7 12-19 12S13 59 13 51Z"/></svg>
        </span>
        <h1 id="mn-cat-room-title">Комната Гроша</h1>
        <span class="mn-cat-room__title-paw" aria-hidden="true">
            <svg viewBox="0 0 64 64"><ellipse cx="13" cy="26" rx="7" ry="10" transform="rotate(-30 13 26)"/><ellipse cx="27" cy="15" rx="7" ry="10" transform="rotate(-10 27 15)"/><ellipse cx="43" cy="16" rx="7" ry="10" transform="rotate(14 43 16)"/><ellipse cx="54" cy="29" rx="7" ry="10" transform="rotate(34 54 29)"/><path d="M13 51c0-12 8-21 19-21s19 9 19 21c0 8-7 12-19 12S13 59 13 51Z"/></svg>
        </span>
    </header>

    <div class="mn-cat-room__scene">
        <div class="mn-cat-room__cat-wrap">
            <img
                class="mn-cat-room__cat"
                src="<?php echo esc_url( home_url( $cat_image_url ) ); ?>"
                alt="Грош, настроение: <?php echo esc_attr( $mood_points ); ?> из 100"
            >
        </div>
    </div>

    <div class="mn-cat-room__mood" aria-label="Настроение Гроша">
        <span class="mn-cat-room__mood-title">Настроение Гроша</span>
        <div class="mn-cat-room__progress" role="progressbar" aria-label="Настроение Гроша" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( $mood_points ); ?>">
            <span style="width: <?php echo esc_attr( $mood_points ); ?>%;"></span>
        </div>
        <strong class="mn-cat-room__mood-value"><?php echo esc_html( $mood_points ); ?><small>/100</small></strong>
    </div>

    <div class="btn-container">
        <a href="<?php echo esc_url( home_url( '/главная/game/' ) ); ?>" class="game-btn">
            <svg class="mn-cat-room__button-paw" viewBox="0 0 64 64" aria-hidden="true"><ellipse cx="13" cy="26" rx="7" ry="10" transform="rotate(-30 13 26)"/><ellipse cx="27" cy="15" rx="7" ry="10" transform="rotate(-10 27 15)"/><ellipse cx="43" cy="16" rx="7" ry="10" transform="rotate(14 43 16)"/><ellipse cx="54" cy="29" rx="7" ry="10" transform="rotate(34 54 29)"/><path d="M13 51c0-12 8-21 19-21s19 9 19 21c0 8-7 12-19 12S13 59 13 51Z"/></svg>
            <span>На страницу игры</span>
        </a>
    </div>
</section>
