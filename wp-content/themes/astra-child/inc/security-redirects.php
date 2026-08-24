<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Скрыть верхнюю админ-панель для всех, кроме администраторов
add_filter( 'show_admin_bar', function( $show ) {
    return current_user_can( 'administrator' ) ? true : false;
} );

// Редирект после регистрации студента → на главную
add_filter( 'tutor_student_register_redirect_url', function( $url ) {
    return home_url( '/' );
} );

// Редирект после входа → на страницу /game
add_filter( 'tutor_login_redirect_url', function( $url ) {
    return home_url( '/game' );
} );

// Защита страницы /game: доступ только для авторизованных пользователей
add_action( 'template_redirect', function() {
    if ( is_page( 'game' ) && ! is_user_logged_in() ) {
        wp_redirect( esc_url_raw( home_url( '/login?redirect_to=' . urlencode( get_permalink() ) ) ) );
        exit;
    }
} );

// Редирект авторизованных пользователей со страниц Регистрации и Входа на Игру
add_action( 'template_redirect', function() {
    if ( ! is_user_logged_in() ) {
        return;
    }
    // Список ID страниц, откуда нужно редиректить (545 — Рег, 551 — Вход)
    $restricted_pages = array( 537, 548 );
    if ( is_page( $restricted_pages ) ) {
        wp_redirect( home_url( '/главная/game/' ) );
        exit;
    }
} );