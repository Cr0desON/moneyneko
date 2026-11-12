<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Подключение скрипта для умного редиректа Tutor LMS при нажатии "Дальше"
 */
add_action( 'wp_enqueue_scripts', 'moneyneko_tutor_custom_scripts' );
function moneyneko_tutor_custom_scripts() {
    // Подключаем скрипт только на страницах курсов/уроков Tutor LMS, чтобы не грузить его везде
    if ( function_exists('tutor_utils') && ( is_singular( tutor()->course_post_type ) || is_singular( 'lesson' ) || is_singular( 'mn_trainer' ) ) ) {
        wp_enqueue_script( 
            'moneyneko-tutor-redirect', 
            get_stylesheet_directory_uri() . '/js/tutor-redirect.js', 
            array(), // без зависимостей от jQuery
            file_exists( get_stylesheet_directory() . '/js/tutor-redirect.js' ) ? (string) filemtime( get_stylesheet_directory() . '/js/tutor-redirect.js' ) : '1.0.1',
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
    if ( ! is_singular( array( 'lesson', 'tutor_quiz', 'mn_trainer' ) ) ) return;

    $post       = get_post( get_queried_object_id() );
    $is_trainer = $post && 'mn_trainer' === $post->post_type;
    if ( $post && 'lesson' === $post->post_type ) {
        $is_trainer = has_shortcode( $post->post_content, 'mn_trainer' ) || false !== strpos( $post->post_content, '[mn_trainer' );
    }

    wp_enqueue_script(
        'mn-tutor-topbar-buttons',
        get_stylesheet_directory_uri() . '/js/tutor-topbar-buttons.js',
        array(),
        file_exists( get_stylesheet_directory() . '/js/tutor-topbar-buttons.js' ) ? (string) filemtime( get_stylesheet_directory() . '/js/tutor-topbar-buttons.js' ) : '1.0.1',
        true
    );

    wp_localize_script( 'mn-tutor-topbar-buttons', 'mnTutorTopbar', array(
        'gameUrl' => home_url( '/game/' ),
        'isQuiz'  => is_singular( 'tutor_quiz' ),
        'isTrainer' => $is_trainer,
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
