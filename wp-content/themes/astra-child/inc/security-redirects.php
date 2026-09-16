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

//Редирект на нашу страницу логина
add_action( 'template_redirect', function () {
    if ( is_user_logged_in() ) {
        return;
    }

    $protected_types = apply_filters( 'mn_protected_content_types', array() );

    // Если список пуст — значит фильтр не сработал, ничего не блокируем
    if ( empty( $protected_types ) ) {
        return;
    }

    $is_protected     = is_singular( $protected_types );
    $is_protected     = apply_filters( 'mn_is_protected_request', $is_protected );

    if ( $is_protected ) {
        $current_url = home_url( add_query_arg( null, null ) );
        wp_safe_redirect( home_url( '/login/?redirect_to=' . urlencode( $current_url ) ) );
        exit;
    }
}, 1 );

add_filter( 'login_url', function ( $login_url, $redirect ) {
    global $pagenow;

    // Не трогаем ссылки, которые формирует сам wp-login.php
    // (подтверждение почты администратора, сброс пароля и т.п.)
    if ( is_admin() || ( isset( $pagenow ) && $pagenow === 'wp-login.php' ) ) {
        return $login_url;
    }

    $login_url = home_url( '/login/' );
    if ( ! empty( $redirect ) ) {
        $login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
    }
    return $login_url;
}, 10, 2 );

add_filter( 'mn_is_protected_request', function ( $is_protected ) {
    if ( is_page( mn_get_protected_pages() ) ) {
        $is_protected = true;
    }
    return $is_protected;
} );

// Если через форму /wp-admin/ вошёл НЕ администратор — сразу кидаем на игру
add_filter( 'login_redirect', function ( $redirect_to, $request, $user ) {
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        if ( ! in_array( 'administrator', $user->roles, true ) ) {
            return home_url( '/game' );
        }
    }
    return $redirect_to;
}, 10, 3 );

// Если НЕ администратор уже залогинен и вручную открывает /wp-admin/ — тоже на игру
add_action( 'admin_init', function () {
    // Пропускаем ajax-запросы (wp-admin/admin-ajax.php), чтобы не сломать работу сайта
    if ( wp_doing_ajax() ) {
        return;
    }
    if ( ! current_user_can( 'administrator' ) ) {
        wp_redirect( home_url( '/game' ) );
        exit;
    }
} );