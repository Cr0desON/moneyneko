<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="sketch-profile-unlogged">
    <h2>Упс! Вы не вошли 🙀</h2>
    <p>Чтобы увидеть свой личный кабинет и проверить очки настроения, необходимо авторизоваться в системе.</p>
    <div class="unlogged-btn-wrap">
        <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="unlogged-btn unlogged-btn-blue">Войти в аккаунт</a>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="unlogged-btn unlogged-btn-white">← На главную</a>
    </div>
</div>