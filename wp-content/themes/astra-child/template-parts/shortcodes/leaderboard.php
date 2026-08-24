<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="lb-card">
    <h2 style="text-align: center; margin: 0 0 25px 0; font-family: 'Caveat', cursive; font-size: 3.5em; color: #1a252f;">Доска Лидеров</h2>
    <div class="lb-table-wrapper">
        <table class="lb-table">
            <tbody>
            <?php
            if ( ! empty( $users ) ) {
                $rank = 1;
                foreach ( $users as $user ) {
                    $points = (int) get_user_meta( $user->ID, $meta_key, true );
                    if ( $points <= 0 ) continue;
                    $is_me = ( $current_id === (int) $user->ID );
                    $rank_class = ($rank <= 3) ? "rank-$rank" : "";
                    ?>
                    <tr class="lb-row <?php echo $rank_class; ?> <?php echo $is_me ? 'is-me' : ''; ?>">
                        <td width="60"><div class="rank-box"><?php echo $rank; ?></div></td>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar"><?php echo get_avatar( $user->ID, 45 ); ?></div>
                                <div class="user-name">
                                    <?php echo esc_html( $user->display_name ); ?>
                                    <?php if($is_me) echo '<span style="font-size:0.7em; color:#7f8c8d; margin-left:5px;">(Вы)</span>'; ?>
                                </div>
                            </div>
                        </td>
                        <td class="points-display"><?php echo number_format( $points ); ?></td>
                    </tr>
                    <?php $rank++;
                }
            } else {
                echo '<tr><td style="text-align:center; padding: 40px; font-size: 1.3em;">Тут пока пусто...</td></tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
    <div class="btn-container">
        <a href="<?php echo esc_url( home_url( '/главная/game/' ) ); ?>" class="game-btn">На страницу игры</a>
    </div>
</div>