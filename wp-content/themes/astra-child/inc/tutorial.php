<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Туториал для новых пользователей (Driver.js + Грош).
 *
 * Логика:
 *  - при регистрации пользователю ставится флаг _mn_tutorial_enabled — только такие
 *    пользователи видят туториал автоматически (старые пользователи его не получат,
 *    но могут вызвать кнопкой «?» на странице игры);
 *  - в _mn_tutorial_seen хранится список страниц, на которых туториал уже пройден
 *    или пропущен (game, leaderboard, module, profile) — работает на любом устройстве;
 *  - тексты и шаги лежат в js/tutorial-steps.js, логика — в js/tutorial.js.
 */

const MN_TUTORIAL_ENABLED_META = '_mn_tutorial_enabled';
const MN_TUTORIAL_SEEN_META    = '_mn_tutorial_seen';

/** Ключи страниц, у которых есть свой туториал. */
function mn_tutorial_pages() {
    return array( 'game', 'leaderboard', 'module', 'profile' );
}

/** Новому пользователю включаем туториал. */
add_action( 'user_register', 'mn_tutorial_enable_for_new_user' );
function mn_tutorial_enable_for_new_user( $user_id ) {
    update_user_meta( $user_id, MN_TUTORIAL_ENABLED_META, '1' );
    update_user_meta( $user_id, MN_TUTORIAL_SEEN_META, array() );
}

/** Страницы, на которых туториал уже пройден/пропущен. */
function mn_tutorial_get_seen( $user_id ) {
    $seen = get_user_meta( $user_id, MN_TUTORIAL_SEEN_META, true );
    if ( ! is_array( $seen ) ) {
        return array();
    }
    return array_values( array_intersect( $seen, mn_tutorial_pages() ) );
}

/** Подключение Driver.js, стилей и скриптов туториала (только для авторизованных). */
add_action( 'wp_enqueue_scripts', 'mn_tutorial_enqueue' );
function mn_tutorial_enqueue() {
    if ( ! is_user_logged_in() ) {
        return;
    }

    $dir = get_stylesheet_directory();
    $uri = get_stylesheet_directory_uri();
    $ver = function ( $path ) use ( $dir ) {
        return file_exists( $dir . $path ) ? (string) filemtime( $dir . $path ) : '1.0.0';
    };

    // Библиотека лежит в теме (без CDN), версия 1.3.1, лицензия MIT
    wp_enqueue_style( 'mn-driver', $uri . '/css/vendor/driver.css', array(), '1.3.1' );
    wp_enqueue_style( 'mn-tutorial', $uri . '/css/tutorial.css', array( 'mn-driver', 'astra-child-style' ), $ver( '/css/tutorial.css' ) );

    wp_enqueue_script( 'mn-driver', $uri . '/js/vendor/driver.js.iife.js', array(), '1.3.1', true );
    wp_enqueue_script( 'mn-tutorial-steps', $uri . '/js/tutorial-steps.js', array(), $ver( '/js/tutorial-steps.js' ), true );
    wp_enqueue_script( 'mn-tutorial', $uri . '/js/tutorial.js', array( 'mn-driver', 'mn-tutorial-steps' ), $ver( '/js/tutorial.js' ), true );

    $user_id = get_current_user_id();

    wp_localize_script( 'mn-tutorial', 'mnTutorial', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'mn_tutorial' ),
        // Страницы уроков определяем на сервере, шорткод-страницы — в JS по DOM
        'page'    => is_singular( array( 'lesson', 'mn_trainer' ) ) ? 'module' : '',
        'enabled' => get_user_meta( $user_id, MN_TUTORIAL_ENABLED_META, true ) === '1',
        'seen'    => mn_tutorial_get_seen( $user_id ),
    ) );
}

/** AJAX: запомнить, что туториал на странице(ах) пройден или пропущен. */
add_action( 'wp_ajax_mn_tutorial_save', 'mn_tutorial_ajax_save' );
function mn_tutorial_ajax_save() {
    check_ajax_referer( 'mn_tutorial', 'nonce' );

    $user_id = get_current_user_id();
    $raw     = isset( $_POST['pages'] ) ? sanitize_text_field( wp_unslash( $_POST['pages'] ) ) : '';

    $requested = ( $raw === 'all' )
        ? mn_tutorial_pages()
        : array_filter( array_map( 'trim', explode( ',', $raw ) ) );

    $valid = array_values( array_intersect( $requested, mn_tutorial_pages() ) );
    if ( empty( $valid ) ) {
        wp_send_json_error( null, 400 );
    }

    $seen = array_values( array_unique( array_merge( mn_tutorial_get_seen( $user_id ), $valid ) ) );
    update_user_meta( $user_id, MN_TUTORIAL_SEEN_META, $seen );

    wp_send_json_success( $seen );
}
