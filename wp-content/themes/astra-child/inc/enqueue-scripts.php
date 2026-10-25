<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_enqueue_scripts', 'moneyneko_enqueue_styles' );
function moneyneko_enqueue_styles() {
    // Подключаем шрифты для всего сайта
    wp_enqueue_style( 'moneyneko-fonts', 'https://fonts.googleapis.com/css2?family=Caveat:wght@400;700&family=Balsamiq+Sans:wght@400;700&display=swap', array(), null );

    wp_enqueue_style( 'astra-parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'astra-child-style', get_stylesheet_directory_uri() . '/style.css', array('astra-parent-style') );

    // Интерактивная карта лекций (Duolingo-style)
    if ( is_user_logged_in() ) {
        $dir = get_stylesheet_directory();
        $uri = get_stylesheet_directory_uri();

        wp_enqueue_style(
            'mn-map-style',
            $uri . '/css/map.css',
            array( 'astra-child-style' ),
            file_exists( $dir . '/css/map.css' ) ? (string) filemtime( $dir . '/css/map.css' ) : '1.0.0'
        );

        wp_enqueue_script(
            'mn-map-script',
            $uri . '/js/map.js',
            array(),
            file_exists( $dir . '/js/map.js' ) ? (string) filemtime( $dir . '/js/map.js' ) : '1.0.0',
            true
        );
    }
}