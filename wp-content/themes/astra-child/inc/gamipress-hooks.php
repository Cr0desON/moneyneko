<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

// ==========================================
// 1. ЖЕСТКИЙ ЛИМИТ НАСТРОЕНИЯ (МАКСИМУМ 100)
// ==========================================
add_action( 'gamipress_awarded_points', 'moneyneko_limit_mood_points_max', 10, 5 );
function moneyneko_limit_mood_points_max( $user_id, $points, $points_type, $reason, $log_id ) {
    if ( $points_type === 'mood-points' ) {
        $current_balance = gamipress_get_user_points( $user_id, 'mood-points' );
        // Если перевалили за 100, вычитаем излишек
        if ( $current_balance > 100 ) {
            $excess = $current_balance - 100;
            gamipress_deduct_points_from_user( $user_id, $excess, 'mood-points' );
        }
    }
}

// ==========================================
// 2.1 ВЫДАЧА БАЛЛОВ ПРИ РЕГИСТРАЦИИ
// ==========================================
add_action( 'user_register', 'moneyneko_award_points_on_registration' );
function moneyneko_award_points_on_registration( $user_id ) {
    if ( class_exists( 'GamiPress' ) ) {
        // Гарантированно выдаем стартовые 50 баллов настроения сразу при создании аккаунта
        gamipress_award_points_to_user( $user_id, 50, 'mood-points' );

        // Задаем начальные значения для стрика, чтобы при первом входе не было конфликтов
        update_user_meta( $user_id, '_mn_login_streak', 1 );
        update_user_meta( $user_id, '_mn_last_login_date', current_time( 'Y-m-d' ) );
    }
}

// ==========================================
// 2.2 СИСТЕМА СТРИКОВ, БОНУСОВ И ШТРАФОВ
// ==========================================
if ( ! function_exists( 'moneyneko_track_daily_streak' ) ) {
    function moneyneko_track_daily_streak( $user_login, $user ) {
        if ( ! class_exists( 'GamiPress' ) ) return;

        $user_id = $user->ID;
        $today = current_time( 'Y-m-d' );
        $last_login = get_user_meta( $user_id, '_mn_last_login_date', true );
        $streak     = (int) get_user_meta( $user_id, '_mn_login_streak', true );

        // Если это первый вход (подстраховка, если баллы при регистрации не сработали)
        if ( empty( $last_login ) ) {
            update_user_meta( $user_id, '_mn_login_streak', 1 );
            update_user_meta( $user_id, '_mn_last_login_date', $today );
            return;
        }

        // Если сегодня уже логинился — ничего не делаем
        if ( $last_login === $today ) return;

        // Считаем пропущенные дни
        $days_missed = 0;
        if ( ! empty( $last_login ) ) {
            $diff = strtotime( $today ) - strtotime( $last_login );
            $days_missed = floor( $diff / (60 * 60 * 24) ) - 1;
        }

        // --- ЛОГИКА ШТРАФОВ ---
        if ( $days_missed > 0 ) {
            $streak = 0; // Стрик обнуляется
            $current_mood = (int) gamipress_get_user_points( $user_id, 'mood-points' );

            for ( $i = 1; $i <= $days_missed; $i++ ) {
                $base_loss = 0;

                if ( $current_mood >= 90 ) $base_loss = 15;
                elseif ( $current_mood >= 75 ) $base_loss = 12;
                elseif ( $current_mood >= 40 ) $base_loss = 6;
                elseif ( $current_mood >= 10 ) $base_loss = 2;

                if ( $base_loss > 0 ) {
                    $multiplier = 0.5 + (0.5 * $i);
                    $daily_loss = round( $base_loss * $multiplier );
                    $current_mood -= $daily_loss;
                    if ( $current_mood < 0 ) $current_mood = 0;
                }
            }

            // Синхронизируем вычет с GamiPress (с защитой)
            if ( function_exists( 'gamipress_get_user_points' ) && function_exists( 'gamipress_deduct_points_to_user' ) ) {
                $actual_mood = (int) gamipress_get_user_points( $user_id, 'mood-points' );
                $points_to_deduct = $actual_mood - $current_mood;
                if ( $points_to_deduct > 0 ) {
                    gamipress_deduct_points_to_user( $user_id, $points_to_deduct, 'mood-points' );
                }
            }
        }
        // --- ЛОГИКА БОНУСОВ ЗА СТРИК ---
        else {
            $streak++;
            if ( $streak > 1 ) {
                // Каждый 7-й день даем 10 XP, в остальные дни стрика — 5 XP
                $bonus_progress = ( $streak % 7 == 0 ) ? 10 : 5;
                gamipress_award_points_to_user( $user_id, $bonus_progress, 'progress-points' );
            }
        }

        // Обновляем данные пользователя в БД
        update_user_meta( $user_id, '_mn_login_streak', $streak );
        update_user_meta( $user_id, '_mn_last_login_date', $today );
    }

    add_action( 'wp_login', 'moneyneko_track_daily_streak', 10, 2 );
}

// ==========================================
// 2.3 ЛОГИКА СМЕНЫ НАСТРОЕНИЯ КОТА (ФОТО)
// ==========================================
function moneyneko_get_dynamic_cat_image( $mood_points ) {
    $mood_points = (int) $mood_points;

    // 0-9: Отчаяние (😭)
    if ( $mood_points >= 0 && $mood_points <= 9 ) {
        return '/image/emotions/отчаяние.png';
    }
    // 10-39: Случайная плохая эмоция
    elseif ( $mood_points >= 10 && $mood_points <= 39 ) {
        $images = array(
            '/image/emotions/грусть.png',
            '/image/emotions/обида.png',
            '/image/emotions/кошачьи_глазки.png'
        );
        return $images[ array_rand( $images ) ];
    }
    // 40-74: Случайная нейтральная эмоция
    elseif ( $mood_points >= 40 && $mood_points <= 74 ) {
        $images = array(
            '/image/emotions/задумчивый.png',
            '/image/emotions/безразличный.png',
            '/image/emotions/сонливость.png'
        );
        return $images[ array_rand( $images ) ];
    }
    // 75-89: Случайная хорошая эмоция
    elseif ( $mood_points >= 75 && $mood_points <= 89 ) {
        $images = array(
            '/image/emotions/радость.png',
            '/image/emotions/восхищение.png',
            '/image/emotions/милашка.png'
        );
        return $images[ array_rand( $images ) ];
    }
    // 90-100: Крутость (😎)
    elseif ( $mood_points >= 90 && $mood_points <= 100 ) {
        $images = array(
            '/image/emotions/крутой.png'
        );
        return $images[array_rand($images)];
    }
    return '/image/funcat.png';
}