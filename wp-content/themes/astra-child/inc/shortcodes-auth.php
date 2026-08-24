<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

// --- ФОРМА ВХОДА ---
function custom_login_form_shortcode() {
    $reset_success = '';
    if ( isset($_GET['password_reset']) && $_GET['password_reset'] === 'success' ) {
        $reset_success = 'Пароль успешно изменён! Теперь вы можете войти с новым паролем.';
    }
    $login_error = '';

    if ( isset($_POST['custom_login_submit']) ) {
        $username = sanitize_user($_POST['log']);
        $password = $_POST['pwd'];
        $remember = isset($_POST['rememberme']) ? true : false;

        if ( empty($username) || empty($password) ) {
            $login_error = 'Пожалуйста, заполните все поля.';
        } else {
            $creds = array(
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => $remember,
            );

            $user = wp_signon($creds, false);

            if ( is_wp_error($user) ) {
                $login_error = 'Неверный логин или пароль. Попробуйте ещё раз.';
            } else {
                $redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : home_url('/game');
                wp_redirect($redirect_to);
                exit;
            }
        }
    }

    ob_start();
    include get_stylesheet_directory() . '/template-parts/shortcodes/auth-login.php';
    return ob_get_clean();
}
add_shortcode('custom_login', 'custom_login_form_shortcode');


// --- ПРИВЕТСТВИЕ И ВЫХОД (Оригинальные) ---
function welcome_message_shortcode() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        return '<h2>Привет, ' . esc_html($user->display_name) . '! 👋</h2>';
    }
    return '<h2>Привет, гость! 👋</h2>';
}
add_shortcode('welcome_message', 'welcome_message_shortcode');

function logout_link_shortcode() {
    return '<a href="' . wp_logout_url(home_url('/')) . '" class="btn-secondary">🚪 Выйти</a>';
}
add_shortcode('logout_link', 'logout_link_shortcode');


// --- ЗАПРОС НА ВОССТАНОВЛЕНИЕ ПАРОЛЯ ---
function custom_password_recovery_shortcode() {
    $recovery_message = '';

    if ( isset($_POST['recover_password']) && isset($_POST['user_login']) ) {
        $user_login = sanitize_text_field($_POST['user_login']);
        $user_data = get_user_by('login', $user_login) ?: get_user_by('email', $user_login);

        if ( $user_data ) {
            $key = get_password_reset_key($user_data);
            if ( ! is_wp_error($key) ) {
                $reset_link = home_url('/reset-password?reset_key=' . $key . '&reset_login=' . $user_data->user_login);
                $subject = sprintf(__('Восстановление пароля для %s'), get_bloginfo('name'));
                $message = sprintf(__('Здравствуйте, %s!'), $user_data->user_login) . "\r\n\r\n";
                $message .= __('Вы (или кто-то другой) запросили сброс пароля для вашего аккаунта.') . "\r\n\r\n";
                $message .= __('Чтобы установить новый пароль, перейдите по ссылке:') . "\r\n\r\n";
                $message .= $reset_link . "\r\n\r\n";
                $message .= __('Спасибо!') . "\r\n";

                wp_mail($user_data->user_email, wp_specialchars_decode($subject), $message);
                $recovery_message = 'Если такой пользователь существует, на его почту отправлена инструкция по восстановлению пароля.';
            }
        } else {
            $recovery_message = 'Если такой пользователь существует, на его почту отправлена инструкция.';
        }
    }

    ob_start();
    include get_stylesheet_directory() . '/template-parts/shortcodes/auth-recovery.php';
    return ob_get_clean();
}
add_shortcode( 'password_recovery', 'custom_password_recovery_shortcode' );


// --- ПЕРЕХВАТЧИК И ФОРМА УСТАНОВКИ НОВОГО ПАРОЛЯ ---
function custom_reset_password_handler() {
    if ( isset($_GET['action']) && $_GET['action'] === 'resetpass' && isset($_GET['key']) && isset($_GET['login']) ) {
        $key = sanitize_text_field($_GET['key']);
        $login = sanitize_text_field($_GET['login']);
        wp_redirect(home_url('/reset-password?reset_key=' . $key . '&reset_login=' . $login));
        exit;
    }
}
add_action('login_init', 'custom_reset_password_handler');

function custom_reset_password_shortcode() {
    $is_invalid_key = false;
    $invalid_title = 'Неверная ссылка';
    $error_message = '';
    $user = null;
    $reset_key = '';
    $reset_login = '';

    if ( ! isset($_GET['reset_key']) || ! isset($_GET['reset_login']) ) {
        $is_invalid_key = true;
    } else {
        $reset_key = sanitize_text_field($_GET['reset_key']);
        $reset_login = sanitize_text_field($_GET['reset_login']);

        if ( isset($_GET['error']) ) {
            if ( $_GET['error'] === 'password_mismatch' ) $error_message = 'Пароли не совпадают. Пожалуйста, попробуйте ещё раз.';
            if ( $_GET['error'] === 'password_short' ) $error_message = 'Пароль должен быть не менее 6 символов.';
            if ( $_GET['error'] === 'invalid_key' ) $error_message = 'Ссылка недействительна или истекла.';
        }

        $user = check_password_reset_key($reset_key, $reset_login);
        if ( ! $user || is_wp_error($user) ) {
            $is_invalid_key = true;
            $invalid_title = 'Ссылка истекла';
        }
    }

    if ( ! $is_invalid_key && isset($_POST['custom_password_reset_submit']) ) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ( strlen($new_password) < 6 ) {
            $error_message = 'Пароль должен быть не менее 6 символов.';
        } elseif ( $new_password !== $confirm_password ) {
            $error_message = 'Пароли не совпадают. Пожалуйста, попробуйте ещё раз.';
        } elseif ( !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/', $new_password) ) {
            $error_message = 'Пароль должен содержать хотя бы одну заглавную букву, одну строчную букву и одну цифру.';
        } else {
            reset_password($user, $new_password);
            wp_redirect(home_url('/login?password_reset=success'));
            exit;
        }
    }

    ob_start();
    include get_stylesheet_directory() . '/template-parts/shortcodes/auth-reset.php';
    return ob_get_clean();
}
add_shortcode('custom_reset_password', 'custom_reset_password_shortcode');