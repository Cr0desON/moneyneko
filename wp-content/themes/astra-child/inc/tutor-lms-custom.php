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

function mn_get_protected_pages() {
    return array(
        63, // Доска лидеров
        // сюда можно добавлять другие защищённые страницы
    );
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

// Список типов записей и условий, доступ к которым требует авторизации
add_filter( 'mn_protected_content_types', function ( $types ) {
    $types[] = 'lesson';
    $types[] = 'tutor_quiz';
    $types[] = 'tutor_assignments';
    $types[] = 'mn_trainer';
    return $types;
} );

add_filter( 'mn_is_protected_request', function ( $is_protected ) {
    if ( function_exists( 'tutor_utils' ) && method_exists( tutor_utils(), 'is_tutor_dashboard' ) ) {
        $is_protected = $is_protected || tutor_utils()->is_tutor_dashboard();
    }
    return $is_protected;
} );