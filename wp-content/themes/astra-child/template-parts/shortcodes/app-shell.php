<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="mn-game-map-page mn-app-shell<?php echo 'map' === $active_page ? ' sketch-landing-wrapper' : ''; ?>">
    <?php include get_stylesheet_directory() . '/template-parts/shortcodes/app-sidebar.php'; ?>

    <?php if ( 'map' === $active_page ) : ?>
        <?php include $main_template_path; ?>
    <?php else : ?>
        <main class="mn-game-main mn-app-main">
            <div class="mn-app-page-content mn-app-page-content--<?php echo esc_attr( $active_page ); ?>">
                <?php include $main_template_path; ?>
            </div>
        </main>
    <?php endif; ?>
</div>
