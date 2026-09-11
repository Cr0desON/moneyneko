<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Подключение скрипта для умного редиректа Tutor LMS при нажатии "Дальше"
 */
add_action( 'wp_enqueue_scripts', 'moneyneko_tutor_custom_scripts' );
function moneyneko_tutor_custom_scripts() {
    // Подключаем скрипт только на страницах курсов/уроков Tutor LMS, чтобы не грузить его везде
    if ( function_exists('tutor_utils') && ( is_singular( tutor()->course_post_type ) || is_singular( 'lesson' ) ) ) {
        wp_enqueue_script( 
            'moneyneko-tutor-redirect', 
            get_stylesheet_directory_uri() . '/js/tutor-redirect.js', 
            array(), // без зависимостей от jQuery
            '1.0', 
            true // true означает, что скрипт загрузится в футере
        );
    }
}

add_action( 'wp_enqueue_scripts', function () {
    if ( ! is_singular( array( 'lesson', 'mn_trainer' ) ) ) return;

    wp_enqueue_script(
        'mn-tutor-topbar-buttons',
        get_stylesheet_directory_uri() . '/js/tutor-topbar-buttons.js',
        array(),
        '1.0.0',
        true
    );

    wp_localize_script( 'mn-tutor-topbar-buttons', 'mnTutorTopbar', array(
        'gameUrl' => home_url( '/game/' ),
    ) );
} );