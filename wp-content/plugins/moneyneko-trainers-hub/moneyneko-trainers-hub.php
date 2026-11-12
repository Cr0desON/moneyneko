<?php
/**
 * Plugin Name: MoneyNeko Trainers Hub
 * Description: Единый центр управления интерактивными тренажерами MoneyNeko. Оптимизирован для высокой скорости работы.
 * Version: 1.0.2
 * Author: MoneyNeko
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MN_HUB_VERSION', '1.0.2' );
define( 'MN_HUB_PATH', plugin_dir_path( __FILE__ ) );
define( 'MN_HUB_URL', plugin_dir_url( __FILE__ ) );

// 1. РЕГИСТРАЦИЯ ЕДИНОГО ТИПА ЗАПИСЕЙ ДЛЯ ВСЕХ ТРЕНАЖЕРОВ
add_action( 'init', function () {
    register_post_type( 'mn_trainer', [
        'label'           => 'Тренажёры',
        'labels'          => [
            'name'               => 'Тренажёры',
            'singular_name'      => 'Тренажёр',
            'add_new'            => 'Добавить тренажёр',
            'add_new_item'       => 'Добавить новый тренажёр',
            'edit_item'          => 'Редактировать тренажёр',
            'new_item'           => 'Новый тренажёр',
            'view_item'          => 'Просмотреть тренажёр',
            'search_items'       => 'Поиск тренажёров',
            'not_found'          => 'Тренажёры не найдены',
        ],
        'public'          => true,
        'show_in_menu'    => 'tutor', // Интеграция в меню TutorLMS
        'supports'        => [ 'title', 'editor', 'custom-fields' ],
        'rewrite'         => [ 'slug' => 'trainer', 'with_front' => false ],
        'capability_type' => 'post',
        'has_archive'     => false,
        'exclude_from_search' => true,
        'publicly_queryable'  => true,
        'show_in_rest'    => true,
    ] );
} );

// 2. ИНТЕГРАЦИЯ С ТЕЛОМ КУРСА TUTOR LMS
add_filter( 'tutor_course_contents_post_types', 'mn_hub_add_to_tutor' );
add_filter( 'get_tutor_contents_post_types', 'mn_hub_add_to_tutor' );
function mn_hub_add_to_tutor( $post_types ) {
    $post_types[] = 'mn_trainer';
    return $post_types;
}

/** Get neighboring course items for custom trainer posts. */
function mn_hub_get_content_navigation( $content_id ) {
    $empty = (object) array( 'previous_id' => 0, 'next_id' => 0 );
    if ( ! function_exists( 'tutor_utils' ) ) {
        return $empty;
    }

    $content_id = absint( $content_id );
    if ( 'mn_trainer' !== get_post_type( $content_id ) ) {
        return tutor_utils()->get_course_prev_next_contents_by_id( $content_id );
    }

    // Tutor's get_course_id_by_subcontent() does not map custom content types.
    $course_id = tutor_utils()->get_course_id_by( 'lesson', $content_id );
    if ( ! $course_id ) {
        return $empty;
    }

    $topics_query = tutor_utils()->get_topics( $course_id );
    $topics       = ( $topics_query && ! empty( $topics_query->posts ) ) ? $topics_query->posts : array();
    $content_ids  = array();

    foreach ( $topics as $topic ) {
        $contents_query = tutor_utils()->get_course_contents_by_topic( $topic->ID, -1 );
        if ( $contents_query && ! empty( $contents_query->posts ) ) {
            foreach ( $contents_query->posts as $content ) {
                $content_ids[] = (int) $content->ID;
            }
        }
    }

    $index = array_search( $content_id, $content_ids, true );
    if ( false === $index ) {
        return $empty;
    }

    return (object) array(
        'previous_id' => isset( $content_ids[ $index - 1 ] ) ? $content_ids[ $index - 1 ] : 0,
        'next_id'     => isset( $content_ids[ $index + 1 ] ) ? $content_ids[ $index + 1 ] : 0,
    );
}

// CSS uses this state to keep the course's Next button hidden until the trainer is complete.
add_filter( 'body_class', function ( $classes ) {
    $post = get_post( get_queried_object_id() );
    if ( ! $post ) {
        return $classes;
    }

    $is_trainer = 'mn_trainer' === $post->post_type;
    if ( 'lesson' === $post->post_type ) {
        $is_trainer = false !== strpos( $post->post_content, '[mn_trainer' );
    }
    if ( ! $is_trainer ) {
        return $classes;
    }

    $is_completed = function_exists( 'tutor_utils' )
        && tutor_utils()->is_completed_lesson( $post->ID, get_current_user_id() );
    $classes[] = $is_completed ? 'mn-trainer-completed' : 'mn-trainer-incomplete';

    return $classes;
}, 20 );

// Добавляем красивую иконку со знаком вопроса в список уроков
add_filter( 'tutor_course_content_icon', function( $icon, $post_type ) {
    if ( $post_type === 'mn_trainer' ) {
        return '<span class="tutor-icon-question-mark-line"></span>';
    }
    return $icon;
}, 10, 2 );

// 3. РЕГИСТРАЦИЯ ВСЕХ РЕСУРСОВ (БЕЗ ИХ АВТОМАТИЧЕСКОЙ ЗАГРУЗКИ)
add_action( 'wp_enqueue_scripts', function () {
    // Регистрируем ресурсы Тренажера 1
    wp_register_style( 'mn-m1-style', MN_HUB_URL . 'trainers/m1-cashflow/style.css', [], MN_HUB_VERSION );
    wp_register_script( 'mn-m1-script', MN_HUB_URL . 'trainers/m1-cashflow/script.js', [], MN_HUB_VERSION, true );
    // Регистрируем ресурсы Тренажера 2
    wp_register_style( 'mn-m2-style', MN_HUB_URL . 'trainers/m2-kiosk/style.css', [], MN_HUB_VERSION );
    wp_register_script( 'mn-m2-script', MN_HUB_URL . 'trainers/m2-kiosk/script.js', [], MN_HUB_VERSION, true );
    // Регистрируем ресурсы Тренажера 3
    wp_register_style( 'mn-m3-style', MN_HUB_URL . 'trainers/m3-hidden-object/style.css', [], MN_HUB_VERSION );
    wp_register_script( 'mn-m3-script', MN_HUB_URL . 'trainers/m3-hidden-object/script.js', [], MN_HUB_VERSION, true );
} );

// 4. ПОДКЛЮЧЕНИЕ ШАБЛОНА ДЛЯ СТРАНИЦ ТРЕНАЖЕРОВ
add_filter( 'template_include', function ( $template ) {
    if ( is_singular( 'mn_trainer' ) ) {
        // Код использует единый файл шаблона для красивого вывода внутри интерфейса TutorLMS
        $custom = MN_HUB_PATH . 'trainers/single-trainer-template.php';
        if ( file_exists( $custom ) ) return $custom;
    }
    return $template;
}, 99 );

// ==========================================
// 5. ШОРТКОД ДЛЯ 1 ТРЕНАЖЕРА: [mn_trainer_m1]
// ==========================================
add_shortcode( 'mn_trainer_m1', function () {
    if ( ! is_singular( [ 'lesson', 'mn_trainer' ] ) ) return '';

    // Подключаем ресурсы
    wp_enqueue_style( 'mn-m1-style' );
    wp_enqueue_script( 'mn-m1-script' );

    ob_start();
    include MN_HUB_PATH . 'trainers/m1-cashflow/widget.php';
    return ob_get_clean();
} );


// ==========================================
// 6. ШОРТКОД ДЛЯ 2 ТРЕНАЖЕРА : [mn_trainer_m2]
// ==========================================
add_shortcode( 'mn_trainer_m2', function () {
    if ( ! is_singular( [ 'lesson', 'mn_trainer' ] ) ) return '';

    wp_enqueue_style( 'mn-m2-style' );
    wp_enqueue_script( 'mn-m2-script' );

    ob_start();
    include MN_HUB_PATH . 'trainers/m2-kiosk/widget.php';
    return ob_get_clean();
} );

// ==========================================
// 7. ШОРТКОД ДЛЯ 3 ТРЕНАЖЕРА : [mn_trainer_m3]
// ==========================================
add_shortcode( 'mn_trainer_m3', function () {
    if ( ! is_singular( [ 'lesson', 'mn_trainer' ] ) ) return '';

    // Включаем ресурсы только при вызове шорткода
    wp_enqueue_style( 'mn-m3-style' );
    wp_enqueue_script( 'mn-m3-script' );

    // Передаем путь к картинке комнаты в JS
    wp_localize_script( 'mn-m3-script', 'mnTrainerm3Data', [
        'bgUrl' => MN_HUB_URL . 'trainers/m3-hidden-object/room-bg.jpg'
    ] );

    ob_start();
    include MN_HUB_PATH . 'trainers/m3-hidden-object/widget.php';
    return ob_get_clean();
} );

// 8. Правильный редирект после прохождения урока
add_action( 'wp_enqueue_scripts', function () {
    if ( ! is_singular( [ 'lesson', 'mn_trainer' ] ) ) return;

    $id      = get_the_ID();
    $next_id = 0;
    if ( function_exists( 'tutor_utils' ) ) {
        $c       = mn_hub_get_content_navigation( $id );
        $next_id = $c->next_id ?? 0;
    }

    wp_register_script( 'mn-finish', MN_HUB_URL . 'assets/mn-finish.js', [], MN_HUB_VERSION, true );
    wp_localize_script( 'mn-finish', 'mnFinishData', [
        'nextUrl' => $next_id ? get_permalink( $next_id ) : '',
        'gameUrl' => home_url( '/game/' ),
    ] );
    wp_enqueue_script( 'mn-finish' );
}, 20 );
