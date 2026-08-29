<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="mn-header-wrapper">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mn-header-left">
        <img src="/image/funcat.png" alt="MoneyNeko Logo" class="mn-header-logo">
        <span class="mn-header-title">moneyneko</span>
    </a>

    <div class="mn-header-right">
        <?php if ( ! $is_logged_in ) : ?>
            <a href="<?php echo esc_url( home_url( '/student-registration-page/' ) ); ?>" class="mn-btn mn-btn-blue">Регистрация</a>
            <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="mn-btn mn-btn-blue">Войти</a>
        <?php else : ?>
            <a href="<?php echo esc_url( home_url( '/profile/' ) ); ?>" class="mn-btn mn-btn-outline">Профиль</a>
            <?php echo get_avatar( $current_user->ID, 45, '', '', array( 'class' => 'mn-header-avatar' ) ); ?>
        <?php endif; ?>
    </div>
</div>