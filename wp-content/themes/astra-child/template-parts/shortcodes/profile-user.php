<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="sketch-profile">
    <div class="sketch-avatar-wrap">
        <?php echo get_avatar( $user_id, 120 ); ?>
    </div>
    <div class="sketch-greeting">
        <h1>Привет, <?php echo esc_html( $current_user->display_name ); ?>! 👋</h1>
        <p class="sketch-subtitle">Добро пожаловать в ваш личный кабинет</p>
    </div>
    <div class="sketch-section-title">🏆 Ваши очки</div>
    <div class="points-box">
        <span class="points-val"><?php echo $user_points; ?></span> XP
    </div>
    <div class="sketch-section-title">📊 Топ игроков</div>
    <div class="sketch-mini-lb">
        <table class="lb-mini-table">
            <?php
            $rank = 1;
            foreach ( $top_users as $user ) :
                $is_me = ($user->ID == $user_id);
                $pts = (int) get_user_meta( $user->ID, $meta_key, true );
                ?>
                <tr class="lb-mini-row <?php echo $is_me ? 'is-me' : ''; ?>">
                    <td width="35">#<?php echo $rank; ?></td>
                    <td>
                        <?php echo esc_html( $user->display_name ); ?>
                        <?php if($is_me) echo ' <span style="font-size:0.8em; color:#7f8c8d;">(Вы)</span>'; ?>
                    </td>
                    <td class="lb-mini-points"><?php echo $pts; ?></td>
                </tr>
                <?php $rank++; endforeach; ?>
        </table>
    </div>
    <div class="sketch-actions">
        <a href="<?php echo esc_url( home_url( '/главная/game/' ) ); ?>" class="action-btn btn-blue">🎮 Продолжить игру</a>
        <a href="<?php echo wp_logout_url( home_url() ); ?>" class="action-btn btn-white">🚪 Выйти</a>
    </div>
</div>