<?php
/**
 * Журнал активности пользователей + тепловая карта (GitHub-style) для профиля
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// ==========================================
// ХРАНЕНИЕ ЖУРНАЛА
// ==========================================

if ( ! function_exists( 'mn_get_activity_log' ) ) {
    function mn_get_activity_log( $user_id ) {
        $raw  = get_user_meta( $user_id, '_mn_activity_log', true );
        $data = $raw ? json_decode( $raw, true ) : array();
        return is_array( $data ) ? $data : array();
    }
}

if ( ! function_exists( 'mn_save_activity_log' ) ) {
    function mn_save_activity_log( $user_id, $data ) {
        // храним только последние ~380 дней, чтобы meta не раздувалась
        $cutoff = date( 'Y-m-d', strtotime( '-380 days' ) );
        foreach ( $data as $date => $count ) {
            if ( $date < $cutoff ) unset( $data[ $date ] );
        }
        update_user_meta( $user_id, '_mn_activity_log', wp_json_encode( $data ) );
    }
}

if ( ! function_exists( 'mn_log_activity' ) ) {
    function mn_log_activity( $user_id, $weight = 1 ) {
        $today = current_time( 'Y-m-d' );
        $log   = mn_get_activity_log( $user_id );
        $log[ $today ] = isset( $log[ $today ] ) ? $log[ $today ] + $weight : $weight;
        mn_save_activity_log( $user_id, $log );
    }
}

// ==========================================
// СБОР ДАННЫХ
// ==========================================
// 1) Факт визита — +1 за первый заход в день.
// 2) Реальный прогресс — сверяем баланс "очков прохождения" (progress-points)
//    с тем, что было при прошлой загрузке страницы. Разница = сколько XP
//    пользователь заработал (урок, тренажёр, тест, стрик-бонус — не важно откуда).
//    Это надёжнее, чем ловить конкретный хук GamiPress: баланс очков точно
//    актуален всегда, его же показывает блок "Ваши очки" в профиле.
add_action( 'init', function () {
    if ( is_admin() || ! is_user_logged_in() ) return;

    $user_id = get_current_user_id();
    $today   = current_time( 'Y-m-d' );

    // Визит
    $last_visit = get_user_meta( $user_id, '_mn_last_activity_date', true );
    if ( $last_visit !== $today ) {
        mn_log_activity( $user_id, 1 );
        update_user_meta( $user_id, '_mn_last_activity_date', $today );
    }

    // Реальный прогресс по очкам
    if ( class_exists( 'GamiPress' ) ) {
        $current_points = (int) gamipress_get_user_points( $user_id, 'progress-points' );
        $snapshot_raw   = get_user_meta( $user_id, '_mn_points_snapshot', true );

        if ( $snapshot_raw === '' ) {
            // Первый запуск для этого пользователя — просто фиксируем точку
            // отсчёта, дельту не считаем (истории до этого момента нет)
            update_user_meta( $user_id, '_mn_points_snapshot', $current_points );
        } else {
            $last_points = (int) $snapshot_raw;
            $delta = $current_points - $last_points;

            if ( $delta > 0 ) {
                mn_log_activity( $user_id, $delta );
            }
            if ( $delta !== 0 ) {
                update_user_meta( $user_id, '_mn_points_snapshot', $current_points );
            }
        }
    }
}, 20 );

// ==========================================
// РЕНДЕР ТЕПЛОВОЙ КАРТЫ
// ==========================================

if ( ! function_exists( 'mn_activity_level' ) ) {
    function mn_activity_level( $count ) {
        if ( $count <= 0 )  return 0;
        if ( $count <= 1 )  return 1; // просто зашёл, без очков
        if ( $count <= 9 )  return 2; // небольшая активность (частично прошёл)
        if ( $count <= 24 ) return 3; // урок целиком и больше
        return 4;                    // урок + тренажёр + тест и подобное
    }
}

if ( ! function_exists( 'mn_render_activity_heatmap' ) ) {
    /**
     * @param int $user_id
     * @param int $weeks_count Сколько недель показывать (20 ≈ 5 месяцев, 53 ≈ год)
     */
    function mn_render_activity_heatmap( $user_id, $weeks_count = 20 ) {
        $log = mn_get_activity_log( $user_id );

        // Короткие названия месяцев на русском — не полагаемся на локаль сервера
        $months_ru = array(
                1 => 'Янв', 2 => 'Фев', 3 => 'Мар', 4 => 'Апр', 5 => 'Май', 6 => 'Июн',
                7 => 'Июл', 8 => 'Авг', 9 => 'Сен', 10 => 'Окт', 11 => 'Ноя', 12 => 'Дек',
        );

        $today = new DateTime( current_time( 'Y-m-d' ) );
        $dow   = (int) $today->format( 'w' ); // 0 = воскресенье
        $end   = clone $today;
        $end->modify( '+' . ( 6 - $dow ) . ' days' );
        $start = clone $end;
        $start->modify( '-' . ( $weeks_count * 7 - 1 ) . ' days' );

        $weeks  = array();
        $cursor = clone $start;
        for ( $w = 0; $w < $weeks_count; $w++ ) {
            $week = array();
            for ( $d = 0; $d < 7; $d++ ) {
                $date_str = $cursor->format( 'Y-m-d' );
                $count    = isset( $log[ $date_str ] ) ? (int) $log[ $date_str ] : 0;
                $week[]   = array(
                        'date'  => $date_str,
                        'count' => $count,
                        'level' => ( $cursor > $today ) ? -1 : mn_activity_level( $count ),
                );
                $cursor->modify( '+1 day' );
            }
            $weeks[] = $week;
        }

        ob_start();
        ?>
        <div class="mn-heatmap">
            <div class="mn-heatmap-months">
                <?php
                $last_month = '';
                foreach ( $weeks as $week ) {
                    $month_num = (int) date( 'n', strtotime( $week[0]['date'] ) );
                    $month     = $months_ru[ $month_num ];
                    if ( $month !== $last_month ) {
                        echo '<span class="mn-heatmap-month">' . esc_html( $month ) . '</span>';
                        $last_month = $month;
                    } else {
                        echo '<span class="mn-heatmap-month mn-heatmap-month--empty"></span>';
                    }
                }
                ?>
            </div>
            <div class="mn-heatmap-body">
                <div class="mn-heatmap-daylabels">
                    <span>Пн</span><span></span><span>Ср</span><span></span><span>Пт</span><span></span><span></span>
                </div>
                <div class="mn-heatmap-grid">
                    <?php foreach ( $weeks as $week ) : ?>
                        <div class="mn-heatmap-col">
                            <?php foreach ( $week as $day ) :
                                if ( $day['level'] === -1 ) {
                                    echo '<span class="mn-heatmap-cell mn-heatmap-cell--future"></span>';
                                    continue;
                                }
                                ?>
                                <span class="mn-heatmap-cell mn-heatmap-cell--lvl<?php echo (int) $day['level']; ?>"
                                      title="<?php echo esc_attr( $day['count'] . ' очков активности — ' . date( 'd.m.Y', strtotime( $day['date'] ) ) ); ?>"></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mn-heatmap-legend">
                <span>Меньше</span>
                <span class="mn-heatmap-cell mn-heatmap-cell--lvl0"></span>
                <span class="mn-heatmap-cell mn-heatmap-cell--lvl1"></span>
                <span class="mn-heatmap-cell mn-heatmap-cell--lvl2"></span>
                <span class="mn-heatmap-cell mn-heatmap-cell--lvl3"></span>
                <span class="mn-heatmap-cell mn-heatmap-cell--lvl4"></span>
                <span>Больше</span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}