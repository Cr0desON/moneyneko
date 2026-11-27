<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Wrap the map and its neighboring sections in one shared navigation shell. */
if ( ! function_exists( 'moneyneko_render_app_page' ) ) {
    function moneyneko_render_app_page( $active_page, $template_file, $template_vars = array() ) {
        $allowed_pages = array( 'map', 'profile', 'leaderboard', 'cat-room' );
        if ( ! in_array( $active_page, $allowed_pages, true ) ) {
            return '';
        }

        $template_file = sanitize_file_name( $template_file );
        $main_template_path = get_stylesheet_directory() . '/template-parts/shortcodes/' . $template_file;
        if ( ! file_exists( $main_template_path ) ) {
            return '';
        }

        $user_id        = get_current_user_id();
        $progress_points = 0;
        $streak           = 0;

        if ( $user_id ) {
            if ( class_exists( 'GamiPress' ) ) {
                $progress_points = (int) gamipress_get_user_points( $user_id, 'progress-points' );
            }

            $streak = (int) get_user_meta( $user_id, '_mn_login_streak', true );
            if ( 0 === $streak ) {
                $streak = 1;
            }
        }

        if ( is_array( $template_vars ) ) {
            extract( $template_vars, EXTR_SKIP );
        }

        ob_start();
        include get_stylesheet_directory() . '/template-parts/shortcodes/app-shell.php';
        return ob_get_clean();
    }
}

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

        return moneyneko_render_app_page( 'leaderboard', 'leaderboard.php', array(
            'users'      => $users,
            'meta_key'   => $meta_key,
            'current_id' => $current_id,
        ) );
    }
    add_shortcode( 'custom_leaderboard', 'custom_leaderboard_render' );
}

// --- ПРОФИЛЬ ПОЛЬЗОВАТЕЛЯ ---
if ( ! function_exists( 'custom_user_profile_render' ) ) {
    function custom_user_profile_render() {
        if ( ! is_user_logged_in() ) {
            // Если гость — подгружаем HTML для гостя
            return moneyneko_render_app_page( 'profile', 'profile-guest.php' );
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

        return moneyneko_render_app_page( 'profile', 'profile-user.php', array(
            'current_user' => $current_user,
            'user_id'      => $user_id,
            'meta_key'     => $meta_key,
            'user_points'  => $user_points,
            'top_users'    => $top_users,
        ) );
    }
    add_shortcode( 'custom_user_profile', 'custom_user_profile_render' );
}

// --- ИГРОВОЙ ДАШБОРД (Страница Игры) ---
if ( ! function_exists( 'custom_homepage_landing_render' ) ) {
    function custom_homepage_landing_render() {
        return moneyneko_render_app_page( 'map', 'game-dashboard.php' );
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

// --- КОМНАТА ГРОША ---
if ( ! function_exists( 'moneyneko_cat_room_render' ) ) {
    function moneyneko_cat_room_render() {
        $mood_points   = 50;
        $cat_image_url = '/image/funcat.png';

        if ( is_user_logged_in() && class_exists( 'GamiPress' ) ) {
            $mood_points = (int) gamipress_get_user_points( get_current_user_id(), 'mood-points' );
            $mood_points = max( 0, min( 100, $mood_points ) );
        }

        if ( function_exists( 'moneyneko_get_dynamic_cat_image' ) ) {
            $cat_image_url = moneyneko_get_dynamic_cat_image( $mood_points );
        }

        return moneyneko_render_app_page( 'cat-room', 'cat-room.php', array(
            'mood_points'   => $mood_points,
            'cat_image_url' => $cat_image_url,
        ) );
    }
    add_shortcode( 'moneyneko_cat_room', 'moneyneko_cat_room_render' );
}
