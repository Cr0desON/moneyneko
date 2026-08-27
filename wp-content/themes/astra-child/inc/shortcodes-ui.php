<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

// --- ДОСКА ЛИДЕРОВ ---
if ( ! function_exists( 'custom_leaderboard_render' ) ) {
    function custom_leaderboard_render( $atts ) {
        if ( ! class_exists( 'GamiPress' ) ) return '';

        $atts = shortcode_atts( array(
            'limit'       => 10,
            'points_type' => 'progress-points',
        ), $atts );

        $meta_key = '_gamipress_' . sanitize_text_field( $atts['points_type'] ) . '_points';

        $users_query = new WP_User_Query( array(
            'number'   => intval( $atts['limit'] ),
            'meta_key' => $meta_key,
            'orderby'  => 'meta_value_num',
            'order'    => 'DESC',
        ) );

        $users = $users_query->get_results();
        $current_id = get_current_user_id(); // Получаем ID здесь, чтобы передать в HTML

        ob_start();
        // Подключаем наш чистый HTML-шаблон
        include get_stylesheet_directory() . '/template-parts/shortcodes/leaderboard.php';
        return ob_get_clean();
    }
    add_shortcode( 'custom_leaderboard', 'custom_leaderboard_render' );
}

// --- ПРОФИЛЬ ПОЛЬЗОВАТЕЛЯ ---
if ( ! function_exists( 'custom_user_profile_render' ) ) {
    function custom_user_profile_render() {
        ob_start();

        if ( ! is_user_logged_in() ) {
            // Если гость — подгружаем HTML для гостя
            include get_stylesheet_directory() . '/template-parts/shortcodes/profile-guest.php';
            return ob_get_clean();
        }

        // Логика для авторизованного: вычисления
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $meta_key = '_gamipress_progress-points_points';
        $user_points = (int) get_user_meta( $user_id, $meta_key, true );

        $top_users_query = new WP_User_Query( array(
            'number'   => 3,
            'meta_key' => $meta_key,
            'orderby'  => 'meta_value_num',
            'order'    => 'DESC',
        ) );
        $top_users = $top_users_query->get_results();

        // Подгружаем HTML для авторизованного юзера
        include get_stylesheet_directory() . '/template-parts/shortcodes/profile-user.php';
        return ob_get_clean();
    }
    add_shortcode( 'custom_user_profile', 'custom_user_profile_render' );
}

// --- ИГРОВОЙ ДАШБОРД (Страница Игры) ---
if ( ! function_exists( 'custom_homepage_landing_render' ) ) {
    function custom_homepage_landing_render() {
        $mood_points = 50;
        $progress_points = 0;
        $streak = 0;
        $cat_image_url = '/image/funcat.png';

        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();

            if ( class_exists( 'GamiPress' ) ) {
                $progress_points = (int) gamipress_get_user_points( $user_id, 'progress-points' );
                $mood_points = (int) gamipress_get_user_points( $user_id, 'mood-points' );
                $mood_points = max( 0, min( 100, $mood_points ) ); // Лимит 0-100
            }

            $streak = (int) get_user_meta( $user_id, '_mn_login_streak', true );
            if ( $streak === 0 ) $streak = 1;

            if ( function_exists('moneyneko_get_dynamic_cat_image') ) {
                $cat_image_url = moneyneko_get_dynamic_cat_image( $mood_points );
            }
        }

        ob_start();
        include get_stylesheet_directory() . '/template-parts/shortcodes/game-dashboard.php';
        return ob_get_clean();
    }
    add_shortcode( 'custom_homepage', 'custom_homepage_landing_render' );
}

// --- ЛЕНДИНГ (Вступительная страница) ---
if ( ! function_exists( 'custom_main_homepage_render' ) ) {
    function custom_main_homepage_render() {
        ob_start();
        include get_stylesheet_directory() . '/template-parts/shortcodes/landing-page.php';
        return ob_get_clean();
    }
    add_shortcode( 'custom_main_page', 'custom_main_homepage_render' );
}

// --- КАСТОМНАЯ ШАПКА ---
if ( ! function_exists( 'custom_moneyneko_header_render' ) ) {
    function custom_moneyneko_header_render() {
        $is_logged_in = is_user_logged_in();
        $current_user = wp_get_current_user();

        ob_start();
        include get_stylesheet_directory() . '/template-parts/shortcodes/header.php';
        return ob_get_clean();
    }
    add_shortcode( 'custom_header', 'custom_moneyneko_header_render' );
}