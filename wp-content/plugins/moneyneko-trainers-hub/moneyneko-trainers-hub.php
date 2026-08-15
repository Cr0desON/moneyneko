<?php
/**
 * Plugin Name: MoneyNeko Trainers Hub
 * Description: Единый центр управления интерактивными тренажерами MoneyNeko. Оптимизирован для высокой скорости работы.
 * Version: 1.0.0
 * Author: MoneyNeko
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MN_HUB_VERSION', '1.0.0' );
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

// Добавляем красивую иконку со знаком вопроса в список уроков
add_filter( 'tutor_course_content_icon', function( $icon, $post_type ) {
    if ( $post_type === 'mn_trainer' ) {
        return '<span class="tutor-icon-question-mark-line"></span>';
    }
    return $icon;
}, 10, 2 );

// 3. РЕГИСТРАЦИЯ ВСЕХ РЕСУРСОВ (БЕЗ ИХ АВТОМАТИЧЕСКОЙ ЗАГРУЗКИ)
add_action( 'wp_enqueue_scripts', function () {
    // Регистрируем ресурсы Модуля 1
    wp_register_style( 'mn-m1-style', MN_HUB_URL . 'trainers/m1-sorting/style.css', [], MN_HUB_VERSION );
    wp_register_script( 'mn-m1-script', MN_HUB_URL . 'trainers/m1-sorting/script.js', [], MN_HUB_VERSION, true );
    // Регистрируем ресурсы Модуля 2
    wp_register_style( 'mn-m2-style', MN_HUB_URL . 'trainers/m2-cashflow/style.css', [], MN_HUB_VERSION );
    wp_register_script( 'mn-m2-script', MN_HUB_URL . 'trainers/m2-cashflow/script.js', [], MN_HUB_VERSION, true );
    // Регистрируем ресурсы Модуля 3 
    wp_register_style( 'mn-m3-style', MN_HUB_URL . 'trainers/m3-kiosk/style.css', [], MN_HUB_VERSION );
    wp_register_script( 'mn-m3-script', MN_HUB_URL . 'trainers/m3-kiosk/script.js', [], MN_HUB_VERSION, true );
    // Регистрируем ресурсы тренажера 4
    wp_register_style( 'mn-m4-style', MN_HUB_URL . 'trainers/m4-hidden-object/style.css', [], MN_HUB_VERSION );
    wp_register_script( 'mn-m4-script', MN_HUB_URL . 'trainers/m4-hidden-object/script.js', [], MN_HUB_VERSION, true );
} );


// 4. ШОРТКОД ДЛЯ ТРЕНАЖЕРА МОДУЛЯ 1: [mn_trainer_m1]
add_shortcode( 'mn_trainer_m1', function () {
    if ( ! is_singular( [ 'lesson', 'mn_trainer' ] ) ) return '';

    // Подключаем ресурсы только при вызове шорткода (Защита скорости сайта)
    wp_enqueue_style( 'mn-m1-style' );
    wp_enqueue_script( 'mn-m1-script' );

    ob_start();
    include MN_HUB_PATH . 'trainers/m1-sorting/widget.php';
    return ob_get_clean();
} );


// 5. ПОДКЛЮЧЕНИЕ ШАБЛОНА ДЛЯ СТРАНИЦ ТРЕНАЖЕРОВ
add_filter( 'template_include', function ( $template ) {
    if ( is_singular( 'mn_trainer' ) ) {
        // Код использует единый файл шаблона для красивого вывода внутри интерфейса TutorLMS
        $custom = MN_HUB_PATH . 'trainers/single-trainer-template.php';
        if ( file_exists( $custom ) ) return $custom;
    }
    return $template;
}, 99 );

// ==========================================
// 6. ШОРТКОД ДЛЯ ТРЕНАЖЕРА МОДУЛЯ 2: [mn_trainer_m2]
// ==========================================
add_shortcode( 'mn_trainer_m2', function () {
    if ( ! is_singular( [ 'lesson', 'mn_trainer' ] ) ) return '';

    // Подключаем ресурсы
    wp_enqueue_style( 'mn-m2-style' );
    wp_enqueue_script( 'mn-m2-script' );

    ob_start();
    include MN_HUB_PATH . 'trainers/m2-cashflow/widget.php';
    return ob_get_clean();
} );


// ==========================================
// 7. ШОРТКОД ДЛЯ ТРЕНАЖЕРА МОДУЛЯ 3: [mn_trainer_m3]
// ==========================================
add_shortcode( 'mn_trainer_m3', function () {
    if ( ! is_singular( [ 'lesson', 'mn_trainer' ] ) ) return '';

    wp_enqueue_style( 'mn-m3-style' );
    wp_enqueue_script( 'mn-m3-script' );

    ob_start();
    include MN_HUB_PATH . 'trainers/m3-kiosk/widget.php';
    return ob_get_clean();
} );


// 8. ШОРТКОД ДЛЯ ТРЕНАЖЕРА : [mn_trainer_m2]
add_shortcode( 'mn_trainer_m4', function () {
    if ( ! is_singular( [ 'lesson', 'mn_trainer' ] ) ) return '';

    // Включаем ресурсы только при вызове шорткода
    wp_enqueue_style( 'mn-m4-style' );
    wp_enqueue_script( 'mn-m4-script' );

    // Передаем путь к картинке комнаты в JS
    wp_localize_script( 'mn-m4-script', 'mnTrainerM4Data', [
        'bgUrl' => MN_HUB_URL . 'trainers/m4-hidden-object/room-bg.jpg'
    ] );

    ob_start();
    include MN_HUB_PATH . 'trainers/m4-hidden-object/widget.php';
    return ob_get_clean();
} );