<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<section class="mn-cat-room" aria-labelledby="mn-cat-room-title">
    <header class="mn-cat-room__header">
        <h1 id="mn-cat-room-title">Комната Гроша</h1>
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
        <div class="mn-cat-room__mood-score">
            <span>Настроение Гроша</span>
            <strong><?php echo esc_html( $mood_points ); ?><small>/100</small></strong>
        </div>
        <div class="mn-cat-room__progress" role="progressbar" aria-label="Настроение Гроша" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( $mood_points ); ?>">
            <span style="width: <?php echo esc_attr( $mood_points ); ?>%;"></span>
        </div>
    </div>

    <div class="btn-container">
        <a href="<?php echo esc_url( home_url( '/главная/game/' ) ); ?>" class="game-btn">На страницу игры</a>
    </div>
</section>
