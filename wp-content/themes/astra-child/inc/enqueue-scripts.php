<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_enqueue_scripts', 'moneyneko_enqueue_styles' );
function moneyneko_enqueue_styles() {
    //Подключаем шрифты  для всего сайта
    wp_enqueue_style( 'moneyneko-fonts', 'https://fonts.googleapis.com/css2?family=Caveat:wght@400;700&family=Balsamiq+Sans:wght@400;700&display=swap', array(), null );

    wp_enqueue_style( 'astra-parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'astra-child-style', get_stylesheet_directory_uri() . '/style.css', array('astra-parent-style') );
}