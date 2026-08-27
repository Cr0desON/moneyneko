<?php
// 1. ПОДКЛЮЧАЕМ СТИЛИ РОДИТЕЛЬСКОЙ ТЕМЫ ASTRA
add_action( 'wp_enqueue_scripts', 'moneyneko_enqueue_styles' );
function moneyneko_enqueue_styles() {
    wp_enqueue_style( 'astra-parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'astra-child-style', get_stylesheet_directory_uri() . '/style.css', array('astra-parent-style') );
}

// Убираем админ-бар для всех, кроме администраторов

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

// Шорткод для кастомной формы входа [custom_login]
function custom_login_form_shortcode() {
    ob_start();
    $reset_success = '';
    if (isset($_GET['password_reset']) && $_GET['password_reset'] === 'success') {
        $reset_success = 'Пароль успешно изменён! Теперь вы можете войти с новым паролем.';
    }
    $login_error = '';
    $login_success = false;

    // Обработка формы входа
    if (isset($_POST['custom_login_submit'])) {
        $username = sanitize_user($_POST['log']);
        $password = $_POST['pwd'];
        $remember = isset($_POST['rememberme']) ? true : false;

        if (empty($username) || empty($password)) {
            $login_error = 'Пожалуйста, заполните все поля.';
        } else {
            $creds = array(
                'user_login'    => $username,
                'user_password' => $password,
                'remember'      => $remember,
            );

            $user = wp_signon($creds, false);

            if (is_wp_error($user)) {
                $login_error = 'Неверный логин или пароль. Попробуйте ещё раз.';
            } else {
                // Успешный вход - перенаправляем
                $redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : home_url('/game');
                wp_redirect($redirect_to);
                exit;
            }
        }
    }
    ?>

    <div class="wp-login-form">
        <h2>Добро пожаловать!</h2>

        <?php if ($reset_success): ?>
            <div class="reset-success" style="color: #28a745; background: #e8f5e9; padding: 12px; border-radius: 4px; margin-bottom: 20px; border-left: 3px solid #28a745;">
                <?php echo esc_html($reset_success); ?>
            </div>
        <?php endif; ?>

        <?php if ($login_error): ?>
            <div class="login-error" style="color: #dc3232; background: #ffe8e8; padding: 12px; border-radius: 4px; margin-bottom: 20px; border-left: 3px solid #dc3232;">
                <?php echo esc_html($login_error); ?>
            </div>
        <?php endif; ?>

        <form name="loginform" id="loginform" action="" method="post">
            <p>
                <label for="user_login">
                    <?php echo esc_html('Никнейм или почта'); ?><br>
                    <input type="text" name="log" id="user_login" class="input" value="<?php echo isset($_POST['log']) ? esc_attr($_POST['log']) : ''; ?>" size="20" autocapitalize="off" autocomplete="username" required>
                </label>
            </p>
            <p>
                <label for="user_pass">
                    <?php echo esc_html('Пароль'); ?><br>
                    <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" autocomplete="current-password" required>
                </label>
            </p>

            <p class="forgetmenot">
                <label for="rememberme">
                    <input name="rememberme" type="checkbox" id="rememberme" value="forever" <?php checked(isset($_POST['rememberme']), true); ?>>
                    <?php echo esc_html('Запомнить меня'); ?>
                </label>
            </p>

            <p class="submit">
                <input type="submit" name="custom_login_submit" id="wp-submit" class="button button-primary button-large" value="<?php echo esc_attr('Войти'); ?>">
                <input type="hidden" name="redirect_to" value="<?php echo esc_url(home_url('/game')); ?>">
            </p>
        </form>

        <!-- Ссылка восстановления пароля -->
        <p style="text-align: center; margin-top: 15px;">
            <a href="<?php echo esc_url(home_url('/password-recovery')); ?>">Забыли пароль?</a>
        </p>

        <p style="text-align: center; margin-top: 20px;">
            Нет аккаунта? <a href="<?php echo esc_url(home_url('/student-registration-page')); ?>">Зарегистрируйся сейчас</a>
        </p>

        <p style="text-align: center; margin-top: 20px;">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">← На главную</a>
        </p>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('custom_login', 'custom_login_form_shortcode');

// Шорткод для приветствия
function welcome_message_shortcode() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        return '<h2>Привет, ' . esc_html($user->display_name) . '! 👋</h2>';
    }
    return '<h2>Привет, гость! 👋</h2>';
}
add_shortcode('welcome_message', 'welcome_message_shortcode');

// Шорткод для кнопки выхода
function logout_link_shortcode() {
    return '<a href="' . wp_logout_url(home_url('/')) . '" class="btn-secondary">🚪 Выйти</a>';
}
add_shortcode('logout_link', 'logout_link_shortcode');

// Редирект авторизованных пользователей со страниц Регистрации и Входа на Игру
add_action( 'template_redirect', function() {
    
    // 1. Если пользователь НЕ вошел — ничего не делаем
    if ( ! is_user_logged_in() ) {
        return;
    }

    // 2. Список ID страниц, откуда нужно редиректить (545 — Рег, 551 — Вход)
    $restricted_pages = array( 537, 548 );

    // 3. Проверяем, находится ли пользователь на одной из этих страниц
    if ( is_page( $restricted_pages ) ) {
        
        // 4. Перенаправляем на страницу с игрой
        wp_redirect( home_url( '/главная/game/' ) );
        exit;
    }
} );

function custom_password_recovery_shortcode() {
    ob_start();

    $recovery_message = '';
    $recovery_error = '';

    // Обработка отправки формы
    if (isset($_POST['recover_password']) && isset($_POST['user_login'])) {
        $user_login = sanitize_text_field($_POST['user_login']);
        $user_data = get_user_by('login', $user_login) ?: get_user_by('email', $user_login);

        if ($user_data) {
            // ✅ ПОЛУЧАЕМ КЛЮЧ СБРОСА ПАРОЛЯ
            $key = get_password_reset_key($user_data);

            if (!is_wp_error($key)) {
                // ✅ СОЗДАЁМ КАСТОМНУЮ ССЫЛКУ
                $reset_link = home_url('/reset-password?reset_key=' . $key . '&reset_login=' . $user_data->user_login);

                // ✅ ОТПРАВЛЯЕМ КАСТОМНОЕ ПИСЬМО
                $subject = sprintf(__('Восстановление пароля для %s'), get_bloginfo('name'));
                $message = sprintf(__('Здравствуйте, %s!'), $user_data->user_login) . "\r\n\r\n";
                $message .= __('Вы (или кто-то другой) запросили сброс пароля для вашего аккаунта.') . "\r\n\r\n";
                $message .= __('Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.') . "\r\n\r\n";
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
    ?>

    <div class="password-recovery-form">
        <h2>Восстановление пароля</h2>

        <?php if ($recovery_message): ?>
            <div style="color: #28a745; background: #e8f5e9; padding: 12px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo esc_html($recovery_message); ?>
            </div>
            <p><a href="<?php echo esc_url( home_url( '/login' ) ); ?>">← Вернуться ко входу</a></p>
        <?php else: ?>
            <form method="post">
                <p>
                    <label>
                        Введите никнейм или email:<br>
                        <input type="text" name="user_login" required style="width: 100%; padding: 10px;">
                    </label>
                </p>
                <p>
                    <input type="submit" name="recover_password" value="Получить ссылку" class="button button-primary">
                </p>
            </form>

            <p style="text-align: center; margin-top: 15px;">
                <a href="<?php echo esc_url( home_url( '/login' ) ); ?>">← Вернуться ко входу</a>
            </p>
        <?php endif; ?>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode( 'password_recovery', 'custom_password_recovery_shortcode' );

// Перехват стандартной страницы сброса пароля
function custom_reset_password_handler() {
    if (isset($_GET['action']) && $_GET['action'] === 'resetpass' && isset($_GET['key']) && isset($_GET['login'])) {
        $key = sanitize_text_field($_GET['key']);
        $login = sanitize_text_field($_GET['login']);

        $user = check_password_reset_key($key, $login);

        if (!$user) {
            // Неверная ссылка — перенаправляем на страницу восстановления
            wp_redirect(home_url('/password-recovery?error=invalid_key'));
            exit;
        }

        // Обработка формы нового пароля
        if (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password !== $confirm_password) {
                wp_redirect(home_url('/reset-password?error=password_mismatch&reset_key=' . $key . '&reset_login=' . $login));
                exit;
            }

            if (strlen($new_password) < 6) {
                wp_redirect(home_url('/reset-password?error=password_short&reset_key=' . $key . '&reset_login=' . $login));
                exit;
            }

            // Устанавливаем новый пароль
            reset_password($user, $new_password);

            // Перенаправляем на страницу входа с сообщением об успехе
            wp_redirect(home_url('/login?password_reset=success'));
            exit;
        }

        // Показываем форму — перенаправляем на кастомную страницу
        wp_redirect(home_url('/reset-password?reset_key=' . $key . '&reset_login=' . $login));
        exit;
    }
}
add_action('login_init', 'custom_reset_password_handler');

function custom_reset_password_shortcode() {
    ob_start();

    // Проверяем наличие ключа
    if (!isset($_GET['reset_key']) || !isset($_GET['reset_login'])) {
        ?>
        <div style="max-width: 400px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 8px; text-align: center;">
            <h2>Неверная ссылка</h2>
            <p style="color: #dc3232;">Ссылка для сброса пароля недействительна или истекла.</p>
            <p><a href="<?php echo home_url('/password-recovery'); ?>">Запросить новую ссылку</a></p>
        </div>
        <?php
        return ob_get_clean();
    }

    $reset_key = sanitize_text_field($_GET['reset_key']);
    $reset_login = sanitize_text_field($_GET['reset_login']);

    // Проверяем ошибки из URL
    $error_message = '';
    if (isset($_GET['error'])) {
        switch ($_GET['error']) {
            case 'password_mismatch':
                $error_message = 'Пароли не совпадают. Пожалуйста, попробуйте ещё раз.';
                break;
            case 'password_short':
                $error_message = 'Пароль должен быть не менее 6 символов.';
                break;
            case 'invalid_key':
                $error_message = 'Ссылка недействительна или истекла.';
                break;
        }
    }

    // Проверяем ключ
    $user = check_password_reset_key($reset_key, $reset_login);
    if (!$user) {
        ?>
        <div style="max-width: 400px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 8px; text-align: center;">
            <h2>Ссылка истекла</h2>
            <p style="color: #dc3232;">Ссылка для сброса пароля недействительна или истекла.</p>
            <p><a href="<?php echo home_url('/password-recovery'); ?>">Запросить новую ссылку</a></p>
        </div>
        <?php
        return ob_get_clean();
    }

    // ✅ ОБРАБОТКА ФОРМЫ
    if (isset($_POST['custom_password_reset_submit'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Проверка длины пароля
        if (strlen($new_password) < 6) {
            $error_message = 'Пароль должен быть не менее 6 символов.';
        }
        // Проверка совпадения паролей
        elseif ($new_password !== $confirm_password) {
            $error_message = 'Пароли не совпадают. Пожалуйста, попробуйте ещё раз.';
        }
        // Проверка надёжности пароля (опционально)
        elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/', $new_password)) {
            $error_message = 'Пароль должен содержать хотя бы одну заглавную букву, одну строчную букву и одну цифру.';
        }
        else {
            // ✅ Устанавливаем новый пароль
            reset_password($user, $new_password);

            // ✅ Перенаправляем на страницу входа с сообщением об успехе
            wp_redirect(home_url('/login?password_reset=success'));
            exit;
        }
    }
    ?>

    <div style="max-width: 400px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 8px;">
        <h2>Установка нового пароля</h2>
        <p style="color: #666; margin-bottom: 20px;">Введите новый пароль для аккаунта <strong><?php echo esc_html($user->user_login); ?></strong></p>

        <?php if ($error_message): ?>
            <div style="color: #dc3232; background: #ffe8e8; padding: 12px; border-radius: 4px; margin-bottom: 20px; border-left: 3px solid #dc3232;">
                <?php echo esc_html($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <p>
                <label>Новый пароль<br>
                    <input type="password" name="new_password" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;" minlength="6">
                </label>
            </p>
            <p>
                <label>Подтвердите пароль<br>
                    <input type="password" name="confirm_password" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;" minlength="6">
                </label>
            </p>
            <p style="margin-top: 25px;">
                <input type="hidden" name="reset_key" value="<?php echo esc_attr($reset_key); ?>">
                <input type="hidden" name="reset_login" value="<?php echo esc_attr($reset_login); ?>">
                <input type="submit" name="custom_password_reset_submit" value="Установить пароль" style="background: #0073aa; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; width: 100%;">
            </p>
        </form>
        <p style="text-align: center; margin-top: 15px;">
            <a href="<?php echo home_url('/login'); ?>">← Вернуться ко входу</a>
        </p>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('custom_reset_password', 'custom_reset_password_shortcode');

if ( ! function_exists( 'custom_leaderboard_render' ) ) {

    function custom_leaderboard_render( $atts ) {
        if ( ! class_exists( 'GamiPress' ) ) return '';

        $atts = shortcode_atts( array(
            'limit'       => 10,
            'points_type' => 'progress-points',
        ), $atts );

        $meta_key = '_gamipress_' . sanitize_text_field( $atts['points_type'] ) . '_points';

        $users_query = new WP_User_Query( array(
            'number'   => intval( $atts['limit'] ),
            'meta_key' => $meta_key,
            'orderby'  => 'meta_value_num',
            'order'    => 'DESC',
        ) );

        $users = $users_query->get_results();

        ob_start();
        ?>
        <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;700&family=Balsamiq+Sans:wght@400;700&display=swap" rel="stylesheet">

        <style>
            /* --- ОСНОВНОЙ БЛОК ЛИДЕРБОРДА --- */
            .lb-card {
                background-color: #fffdf5;
                border: 3px solid #2c3e50;
                border-radius: 15px 255px 15px 225px / 225px 15px 255px 15px;
                box-shadow: 8px 12px 30px rgba(0,0,0,0.12);
                padding: 40px;
                max-width: 650px;
                margin: 50px auto;
                font-family: 'Balsamiq Sans', cursive, sans-serif; /* Единый маркерный шрифт */
                position: relative;
                transform: rotate(-0.5deg);
                color: #2c3e50;
            }

            .lb-card::before {
                content: "";
                position: absolute;
                top: -15px;
                left: 50%;
                transform: translateX(-50%);
                width: 100px;
                height: 25px;
                background: rgba(255, 255, 255, 0.7);
                border: 1px solid rgba(0,0,0,0.1);
                border-radius: 4px 4px 0 0;
                z-index: 2;
                box-shadow: 1px 1px 3px rgba(0,0,0,0.05);
            }

            .lb-table-wrapper {
                background-color: #ffffff;
                border: 2px solid #2c3e50;
                border-radius: 12px;
                padding: 5px 15px;
                box-shadow: inset 0px 3px 10px rgba(0,0,0,0.04), 3px 5px 0px rgba(44,62,80,0.05);
                margin-bottom: 25px;
            }

            .lb-table { width: 100%; border-collapse: collapse; }

            /* --- СТРОКИ С ЭФФЕКТОМ ОТЪЕЗДА --- */
            .lb-row {
                border-bottom: 2px dotted rgba(44, 62, 80, 0.3);
                transition: all 0.2s ease;
            }
            .lb-row:last-child { border-bottom: none; }
            .lb-row td { padding: 15px 10px; vertical-align: middle; }

            /* ЭФФЕКТ ОТЪЕЗДА ВПРАВО */
            .lb-row:hover {
                background-color: rgba(52, 152, 219, 0.08);
                transform: translateX(10px);
                cursor: pointer;
            }

            .rank-box {
                width: 40px; height: 40px;
                display: flex; align-items: center; justify-content: center;
                border: 2px solid #2c3e50; border-radius: 50% 45% 52% 48%;
                background: #fff; font-weight: bold; font-size: 1.2em;
            }

            .rank-1 .rank-box { background: #f1c40f; color: #fff; border-color: #f1c40f; }
            .rank-2 .rank-box { background: #bdc3c7; color: #fff; border-color: #bdc3c7; }
            .rank-3 .rank-box { background: #e67e22; color: #fff; border-color: #e67e22; }

            .user-info { display: flex; align-items: center; gap: 15px; }
            .user-avatar img { border-radius: 50% 40% 50% 40%; border: 2px solid #2c3e50; box-shadow: 1px 2px 5px rgba(0,0,0,0.1); }

            .user-name { font-size: 1.3em; font-weight: bold; color: #1a252f; letter-spacing: 0.5px; }

            .points-display { text-align: right; font-weight: bold; color: #e67e22; font-size: 1.3em; padding-right: 25px !important;}

            .lb-row.is-me { background-color: rgba(255, 242, 0, 0.2) !important; border-bottom: 2px dashed rgba(255, 242, 0, 0.5); }

            .btn-container { text-align: center; margin-top: 35px; }
            .game-btn {
                display: inline-block; background-color: #3498db; color: #fff !important;
                padding: 12px 40px; text-decoration: none !important; font-size: 1.2em;
                font-weight: bold; border: 3px solid #2c3e50;
                border-radius: 255px 15px 225px 15px / 15px 225px 15px 255px;
                transition: transform 0.1s, box-shadow 0.1s;
                box-shadow: 3px 6px 0px #2c3e50; /* Синхронизировали тени */
                cursor: pointer;
            }
            .game-btn:hover { filter: brightness(1.05); }
            .game-btn:active {
                transform: translateY(4px);
                box-shadow: 0px 2px 0px #2c3e50; /* Синхронизировали нажатие */
            }

            @media (max-width: 600px) {
                .lb-card { padding: 30px 15px; }
                .lb-row:hover { transform: translateX(5px); }
                .user-name { font-size: 1.1em; }
                .points-display { font-size: 1.1em; }
            }
        </style>

        <div class="lb-card">
            <h2 style="text-align: center; margin: 0 0 25px 0; font-family: 'Caveat', cursive; font-size: 3.5em; color: #1a252f;">Доска Лидеров</h2>

            <div class="lb-table-wrapper">
                <table class="lb-table">
                    <tbody>
                    <?php
                    if ( ! empty( $users ) ) {
                        $rank = 1;
                        $current_id = get_current_user_id();
                        foreach ( $users as $user ) {
                            $points = (int) get_user_meta( $user->ID, $meta_key, true );
                            if ( $points <= 0 ) continue;
                            $is_me = ( $current_id === (int) $user->ID );
                            $rank_class = ($rank <= 3) ? "rank-$rank" : "";
                            ?>
                            <tr class="lb-row <?php echo $rank_class; ?> <?php echo $is_me ? 'is-me' : ''; ?>">
                                <td width="60"><div class="rank-box"><?php echo $rank; ?></div></td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar"><?php echo get_avatar( $user->ID, 45 ); ?></div>
                                        <div class="user-name">
                                            <?php echo esc_html( $user->display_name ); ?>
                                            <?php if($is_me) echo '<span style="font-size:0.7em; color:#7f8c8d; margin-left:5px;">(Вы)</span>'; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="points-display"><?php echo number_format( $points ); ?></td>
                            </tr>
                            <?php $rank++;
                        }
                    } else {
                        echo '<tr><td style="text-align:center; padding: 40px; font-size: 1.3em;">Тут пока пусто...</td></tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>

            <div class="btn-container">
                <a href="<?php echo esc_url( home_url( '/главная/game/' ) ); ?>" class="game-btn">На страницу игры</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    add_shortcode( 'custom_leaderboard', 'custom_leaderboard_render' );
}

if ( ! function_exists( 'custom_user_profile_render' ) ) {

    function custom_user_profile_render() {
        // --- КРАСИВЫЙ СТИЛИЗОВАННЫЙ ФОЛБЕК ДЛЯ НЕАВТОРИЗОВАННЫХ ПОЛЬЗОВАТЕЛЕЙ ---
        if ( ! is_user_logged_in() ) {
            ob_start();
            ?>
            <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;700&family=Balsamiq+Sans:wght@400;700&display=swap" rel="stylesheet">
            <style>
                .sketch-profile-unlogged {
                    background-color: #fffdf5;
                    border: 3px solid #2c3e50;
                    border-radius: 15px 255px 15px 225px / 225px 15px 255px 15px;
                    box-shadow: 8px 12px 25px rgba(0,0,0,0.08);
                    padding: 50px 40px;
                    max-width: 500px;
                    margin: 60px auto;
                    font-family: 'Balsamiq Sans', cursive, sans-serif;
                    text-align: center;
                    color: #2c3e50;
                    position: relative;
                    transform: rotate(-0.5deg);
                }
                .sketch-profile-unlogged::before {
                    content: '';
                    position: absolute;
                    top: -12px;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 24px;
                    height: 24px;
                    background-color: #e74c3c;
                    border-radius: 50%;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                    border: 2px solid #c0392b;
                }
                .sketch-profile-unlogged h2 {
                    font-family: 'Caveat', cursive;
                    font-size: 38px;
                    margin: 0 0 15px 0;
                    color: #1a252f;
                }
                .sketch-profile-unlogged p {
                    font-size: 16px;
                    line-height: 1.5;
                    margin-bottom: 30px;
                }
                .unlogged-btn-wrap {
                    display: flex;
                    flex-direction: column;
                    gap: 15px;
                    align-items: center;
                }
                .unlogged-btn {
                    text-decoration: none !important;
                    padding: 12px 35px;
                    font-size: 18px;
                    font-weight: bold;
                    border: 3px solid #2c3e50;
                    border-radius: 255px 15px 225px 15px/15px 225px 15px 255px;
                    transition: transform 0.1s, box-shadow 0.1s;
                    box-shadow: 3px 6px 0px #2c3e50;
                    display: inline-block;
                    width: 80%;
                    box-sizing: border-box;
                }
                .unlogged-btn-blue { background: #3498db; color: #fff !important; }
                .unlogged-btn-white { background: #ffffff; color: #2c3e50 !important; }
                .unlogged-btn:active {
                    transform: translateY(4px);
                    box-shadow: 0px 2px 0px #2c3e50;
                }
                .unlogged-btn:hover { filter: brightness(1.05); }
            </style>

            <div class="sketch-profile-unlogged">
                <h2>Упс! Вы не вошли 🙀</h2>
                <p>Чтобы увидеть свой личный кабинет и проверить очки настроения, необходимо авторизоваться в системе.</p>
                <div class="unlogged-btn-wrap">
                    <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="unlogged-btn unlogged-btn-blue">Войти в аккаунт</a>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="unlogged-btn unlogged-btn-white">← На главную</a>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        // --- ЛОГИКА ДЛЯ АВТОРИЗОВАННОГО ПОЛЬЗОВАТЕЛЯ ---
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        $points_type = 'progress-points';
        $meta_key = '_gamipress_' . $points_type . '_points';
        $user_points = (int) get_user_meta( $user_id, $meta_key, true );

        $top_users_query = new WP_User_Query( array(
            'number'   => 3,
            'meta_key' => $meta_key,
            'orderby'  => 'meta_value_num',
            'order'    => 'DESC',
        ) );
        $top_users = $top_users_query->get_results();

        ob_start();
        ?>
        <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;700&family=Balsamiq+Sans:wght@400;700&display=swap" rel="stylesheet">

        <style>
            /* --- ОСНОВНОЙ БЛОК ПРОФИЛЯ --- */
            .sketch-profile {
                background-color: #fffdf5;
                border: 3px solid #2c3e50;
                border-radius: 15px 255px 15px 225px / 225px 15px 255px 15px;
                box-shadow: 8px 12px 25px rgba(0,0,0,0.08);
                padding: 50px 40px 40px;
                max-width: 700px;
                margin: 40px auto;
                font-family: 'Balsamiq Sans', cursive, sans-serif; /* Перевели на Balsamiq */
                position: relative;
                color: #2c3e50;
                transform: rotate(-0.5deg);
            }

            .sketch-profile::before {
                content: '';
                position: absolute;
                top: -12px;
                left: 50%;
                transform: translateX(-50%);
                width: 24px;
                height: 24px;
                background-color: #e74c3c;
                border-radius: 50%;
                box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                z-index: 20;
                border: 2px solid #c0392b;
            }

            /* --- АВАТАР --- */
            .sketch-avatar-wrap {
                position: relative;
                display: inline-block;
                margin-bottom: 20px;
            }
            .sketch-avatar-wrap img {
                border: 5px solid #ffffff;
                outline: 2px solid #2c3e50;
                box-shadow: 2px 4px 10px rgba(0,0,0,0.15);
                transform: rotate(-3deg);
                display: block;
                border-radius: 6px;
            }
            .sketch-avatar-wrap::after {
                content: "";
                position: absolute;
                top: -10px;
                right: -15px;
                width: 50px;
                height: 20px;
                background: rgba(255, 255, 255, 0.6);
                transform: rotate(30deg);
                box-shadow: 1px 1px 3px rgba(0,0,0,0.1);
                border: 1px solid rgba(0,0,0,0.05);
            }

            /* --- ТЕКСТЫ И ЗАГОЛОВКИ --- */
            .sketch-greeting h1 {
                font-family: 'Caveat', cursive;
                font-size: 42px;
                margin: 0;
                color: #1a252f;
            }
            .sketch-subtitle {
                font-size: 16px;
                color: #7f8c8d;
                margin-bottom: 30px;
            }

            .sketch-section-title {
                font-family: 'Caveat', cursive;
                font-size: 32px;
                margin-bottom: 15px;
                display: flex;
                align-items: center;
                gap: 10px;
                color: #2c3e50;
            }

            /* --- БЛОК ОЧКОВ --- */
            .points-box {
                border: 3px solid #2c3e50;
                padding: 10px 25px;
                display: inline-block;
                border-radius: 255px 15px 225px 15px/15px 225px 15px 255px;
                margin-bottom: 40px;
                background: transparent;
                box-shadow: 3px 5px 0px rgba(44, 62, 80, 0.1);
                font-size: 16px;
            }
            .points-val {
                font-size: 26px;
                font-weight: bold;
                color: #e67e22;
            }

            /* --- МИНИ-ЛИДЕРБОРД --- */
            .sketch-mini-lb {
                background: #fff9c4;
                padding: 15px 25px;
                border: 2px solid #2c3e50; /* Сделали обводку темной */
                border-radius: 2px 15px 3px 15px / 15px 3px 15px 2px;
                box-shadow: 4px 6px 0px rgba(0,0,0,0.1);
                transform: rotate(1.5deg);
                margin-bottom: 40px;
                position: relative;
            }

            .sketch-mini-lb::before {
                content: "";
                position: absolute;
                top: -8px;
                left: 50%;
                transform: translateX(-50%);
                width: 14px;
                height: 14px;
                background: #3498db;
                border-radius: 50%;
                box-shadow: 1px 2px 3px rgba(0,0,0,0.3);
                border: 1px solid #2980b9;
            }

            .lb-mini-table { width: 100%; border-collapse: collapse; color: #2c3e50; }

            /* Стили строк синхронизированы с большой доской лидеров */
            .lb-mini-row {
                border-bottom: 2px dotted rgba(44, 62, 80, 0.2);
            }
            .lb-mini-row:last-child { border-bottom: none; }
            .lb-mini-row.is-me { background: rgba(52, 152, 219, 0.12); font-weight: bold; }
            .lb-mini-row td { padding: 12px 5px; font-size: 16px; vertical-align: middle; }

            /* Защитный отступ для очков справа */
            .lb-mini-points {
                text-align: right;
                font-weight: bold;
                color: #e67e22;
                padding-right: 15px !important;
            }

            /* --- КНОПКИ ДЕЙСТВИЙ (Полная синхронизация с Главной) --- */
            .sketch-actions {
                display: flex;
                gap: 20px;
                flex-wrap: wrap;
            }
            .action-btn {
                text-decoration: none !important;
                padding: 14px 30px;
                font-size: 18px;
                font-weight: bold;
                border: 3px solid #2c3e50;
                border-radius: 255px 15px 225px 15px/15px 225px 15px 255px;
                transition: transform 0.1s, box-shadow 0.1s;
                box-shadow: 3px 6px 0px #2c3e50;
                display: inline-block;
            }
            .btn-blue { background: #3498db; color: #fff !important; }
            .btn-white { background: #ffffff; color: #2c3e50 !important; }

            .action-btn:active {
                transform: translateY(4px);
                box-shadow: 0px 2px 0px #2c3e50;
            }
            .action-btn:hover {
                filter: brightness(1.05);
            }

            /* --- АДАПТИВНОСТЬ --- */
            @media (max-width: 600px) {
                .sketch-profile { padding: 40px 20px 30px; }
                .sketch-greeting h1 { font-size: 32px; }
                .sketch-actions { flex-direction: column; gap: 15px; }
                .action-btn { text-align: center; width: 100%; box-sizing: border-box; }
            }
        </style>

        <div class="sketch-profile">
            <div class="sketch-avatar-wrap">
                <?php echo get_avatar( $user_id, 120 ); ?>
            </div>

            <div class="sketch-greeting">
                <h1>Привет, <?php echo esc_html( $current_user->display_name ); ?>! 👋</h1>
                <p class="sketch-subtitle">Добро пожаловать в ваш личный кабинет</p>
            </div>

            <div class="sketch-section-title">🏆 Ваши очки</div>
            <div class="points-box">
                <span class="points-val"><?php echo $user_points; ?></span> XP
            </div>

            <div class="sketch-section-title">📊 Топ игроков</div>
            <div class="sketch-mini-lb">
                <table class="lb-mini-table">
                    <?php
                    $rank = 1;
                    foreach ( $top_users as $user ) :
                        $is_me = ($user->ID == $user_id);
                        $pts = (int) get_user_meta( $user->ID, $meta_key, true );
                        ?>
                        <tr class="lb-mini-row <?php echo $is_me ? 'is-me' : ''; ?>">
                            <td width="35">#<?php echo $rank; ?></td>
                            <td>
                                <?php echo esc_html( $user->display_name ); ?>
                                <?php if($is_me) echo ' <span style="font-size:0.8em; color:#7f8c8d;">(Вы)</span>'; ?>
                            </td>
                            <td class="lb-mini-points"><?php echo $pts; ?></td>
                        </tr>
                        <?php $rank++; endforeach; ?>
                </table>
            </div>

            <div class="sketch-actions">
                <a href="<?php echo esc_url( home_url( '/главная/game/' ) ); ?>" class="action-btn btn-blue">🎮 Продолжить игру</a>
                <a href="<?php echo wp_logout_url( home_url() ); ?>" class="action-btn btn-white">🚪 Выйти</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    add_shortcode( 'custom_user_profile', 'custom_user_profile_render' );
}

/**
 * Скрытие кнопки "Mark as Complete" и умный редирект при нажатии "Дальше"
 */
add_action('wp_head', function() {
    ?>
    <style>
        /* Усиленные селекторы (через body), чтобы перебить общие стили кнопок */
        body form.tutor-topbar-mark-btn,
        body .tutor-topbar-mark-btn,
        body .tutor-course-finish-btn-container,
        body .tutor-course-lesson-single-footer .tutor-btn-done {
            display: none !important;
        }
    </style>
    <?php
});

add_action('wp_footer', function() {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Сначала проверяем, не ждет ли нас редирект с прошлого шага
            const pendingRedirect = sessionStorage.getItem('tutor_auto_redirect_url');
            if (pendingRedirect) {
                sessionStorage.removeItem('tutor_auto_redirect_url'); // Очищаем память
                window.location.href = pendingRedirect; // Делаем мгновенный переход
                return; // Останавливаем скрипт, чтобы страница не успела мигнуть
            }

            // 2. Основная логика работы кнопок
            setTimeout(function() {
                const nextBtn = document.querySelector('.tutor-single-course-content-next a');
                const completeBtn = document.querySelector('.tutor-topbar-mark-btn');

                if (nextBtn && completeBtn) {
                    nextBtn.addEventListener('click', function(e) {
                        e.preventDefault(); // Останавливаем обычный клик

                        const nextUrl = nextBtn.href; // Запоминаем, куда ведет кнопка "Дальше"

                        // Записываем ссылку в память браузера.
                        // Если Tutor LMS перезагрузит страницу после отметки, сработает Шаг 1 (выше)
                        sessionStorage.setItem('tutor_auto_redirect_url', nextUrl);

                        // Нажимаем системную кнопку "Завершить"
                        completeBtn.click();

                        // Подстраховка: если Tutor LMS не станет перезагружать страницу,
                        // мы отправим пользователя на следующий урок сами через 1.5 секунды.
                        // Этого времени достаточно, чтобы сервер успел начислить баллы в GamiPress.
                        setTimeout(function() {
                            window.location.href = nextUrl;
                        }, 1500);
                    });
                }
            }, 800);
        });
    </script>
    <?php
});

if ( ! function_exists( 'custom_homepage_landing_render' ) ) {

    function custom_homepage_landing_render() {
        $mood_points = 50;
        $progress_points = 0;
        $streak = 0;

        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();

            if ( class_exists( 'GamiPress' ) ) {
                $progress_points = (int) gamipress_get_user_points( $user_id, 'progress-points' );
                $mood_points = (int) gamipress_get_user_points( $user_id, 'mood-points' );

                // Жестко ограничиваем от 0 до 100
                $mood_points = max( 0, min( 100, $mood_points ) );
            }

            $streak = (int) get_user_meta( $user_id, '_mn_login_streak', true );
            if ( $streak === 0 ) $streak = 1;
        }

        ob_start();
        ?>
        <link href="https://fonts.googleapis.com/css2?family=Balsamiq+Sans:wght@400;700&display=swap" rel="stylesheet">
        <style>
            /* --- ЭФФЕКТ ЛИСТА БУМАГИ --- */
            .sketch-landing-wrapper { position: relative; background-color: #fffdf5; border: 2px solid #333; border-radius: 15px 255px 15px 225px / 225px 15px 255px 15px; box-shadow: 8px 12px 25px rgba(0,0,0,0.08); padding: 60px 20px 80px; font-family: 'Balsamiq Sans', cursive, sans-serif; box-sizing: border-box; overflow: visible; max-width: 1100px; margin: 40px auto; z-index: 1; }
            .sketch-landing-wrapper::before { content: ''; position: absolute; top: -12px; left: 50%; transform: translateX(-50%); width: 24px; height: 24px; background-color: #e74c3c; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.3), inset 0 3px 6px rgba(255,255,255,0.3), inset 0 -3px 6px rgba(0,0,0,0.2); z-index: 20; border: 2px solid #c0392b; }

            /* --- КНОПКИ В ШАПКЕ --- */
            .sketch-btn-profile, .sketch-btn-leaderboard { position: absolute; top: 30px; background-color: #fff; color: #2c3e50 !important; font-size: 18px; font-weight: bold; text-decoration: none !important; padding: 10px 25px; border: 2px solid #2c3e50; border-radius: 6px; box-shadow: 2px 4px 0px #2c3e50; transition: transform 0.1s, box-shadow 0.1s; z-index: 10; }
            .sketch-btn-profile { left: 30px; } .sketch-btn-leaderboard { right: 30px; }
            .sketch-btn-profile:active, .sketch-btn-leaderboard:active { transform: translateY(4px); box-shadow: 0px 0px 0px #2c3e50; }
            .sketch-btn-profile:hover, .sketch-btn-leaderboard:hover { background-color: #f0f8ff; }

            /* --- ДЕКОРАЦИИ НА ФОНЕ --- */
            .sketch-bg-deco { position: absolute; z-index: 1; pointer-events: none; opacity: 0.8; }
            .deco-left { top: 110px; left: 5%; font-size: 60px; line-height: 1.2; transform: rotate(-5deg); }
            .deco-right { top: 110px; right: 5%; font-size: 55px; line-height: 1.4; text-align: right; }

            /* --- ЦЕНТРАЛЬНАЯ ЧАСТЬ (Кот и Бейджи) --- */
            .sketch-hero-section { position: relative; z-index: 2; text-align: center; margin-top: 20px; display: flex; flex-direction: column; align-items: center; }
            .cat-container { position: relative; display: inline-block; }
            .cat-image { width: 200px; height: auto; filter: drop-shadow(0px 5px 15px rgba(0,0,0,0.1)); }

            /* --- ПРОГРЕСС-БАР НАСТРОЕНИЯ КОТА --- */
            .mood-progress-wrapper {
                text-align: center; /* Центрируем содержимое без сложных flex-правил */
                margin-bottom: 15px;
                width: 100%;
            }

            /* Стили для цифр над шкалой */
            .mood-progress-text {
                font-size: 18px;
                font-weight: bold;
                color: #555;
                margin-bottom: 5px; /* Небольшой отступ до полоски */
            }

            .mood-progress-container {
                width: 200px;
                height: 16px;
                border: 2px solid #2c3e50;
                border-radius: 10px; /* Возвращаем скругление! */
                background-color: #ffffff;
                box-shadow: 2px 2px 0px #2c3e50;
                overflow: hidden;
                position: relative;
                margin: 0 auto; /* Центрируем саму полоску */
            }

            .mood-progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #e74c3c 0%, #f1c40f 50%, #2ecc71 100%);
                background-size: 200px 100%;
                transition: width 0.5s ease-in-out;
            }
            /* --- XP И СТРИК (СПРАВА ВНИЗУ) --- */
            .right-bottom-badges { position: absolute; top: 80px; right: -120px; display: flex; flex-direction: column; align-items: center; transform: rotate(-3deg); }
            .streak-badge, .progress-xp-badge { font-weight: bold; display: flex; align-items: baseline; gap: 5px; text-shadow: 2px 2px 0px rgba(255,255,255,0.9); white-space: nowrap; }
            .badge-label { font-size: 20px; color: #555; }
            .streak-badge { font-size: 30px; color: #e74c3c; }
            .progress-xp-badge { font-size: 24px; color: #2980b9; margin-top: 5px; }

            /* --- ГЛАВНАЯ КНОПКА --- */
            .sketch-btn-start { display: inline-block; background-color: #aed6dc; color: #2c3e50 !important; font-size: 22px; font-weight: bold; text-decoration: none !important; padding: 14px 50px; border: 3px solid #2c3e50; border-radius: 6px; margin: 40px 0 60px; box-shadow: 0px 6px 0px #2c3e50, inset 0px -4px 0px rgba(0,0,0,0.1); transition: transform 0.1s, box-shadow 0.1s; cursor: pointer; }
            .sketch-btn-start:active { transform: translateY(6px); box-shadow: 0px 0px 0px #2c3e50, inset 0px -2px 0px rgba(0,0,0,0.1); }
            .sketch-btn-start:hover { filter: brightness(1.05); }

            /* --- БЛОК МОДУЛЕЙ --- */
            .sketch-modules-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 15px; position: relative; z-index: 2; width: 100%; }
            .sketch-module { display: block; text-decoration: none !important; color: #111 !important; width: calc(20% - 15px); min-width: 160px; border: 2px solid #2c3e50; border-radius: 4px; padding: 25px 10px; text-align: center; display: flex; flex-direction: column; align-items: center; box-sizing: border-box; box-shadow: 4px 6px 12px rgba(0,0,0,0.1); transition: transform 0.2s, box-shadow 0.2s; background-image: linear-gradient(#2c3e50, #2c3e50), linear-gradient(#2c3e50, #2c3e50), linear-gradient(#2c3e50, #2c3e50), linear-gradient(#2c3e50, #2c3e50); background-repeat: no-repeat; background-size: 8px 2px, 2px 8px, 8px 2px, 2px 8px; background-position: 5px 5px, 5px 5px, calc(100% - 5px) calc(100% - 5px), calc(100% - 5px) calc(100% - 5px); }
            .sketch-module:hover { transform: translateY(-8px); box-shadow: 6px 12px 20px rgba(0,0,0,0.15); }
            .mod-1 { background-color: #dceada; } .mod-2 { background-color: #e0f2f1; } .mod-3 { background-color: #e8f4f8; } .mod-4 { background-color: #e5f5e0; } .mod-5 { background-color: #e3f2fd; }
            .module-title { font-weight: bold; font-size: 16px; margin-bottom: 8px; }
            .module-desc { font-size: 13px; color: #333; line-height: 1.3; margin-bottom: 25px; flex-grow: 1; }
            .module-icon { font-size: 45px; }

            /* Адаптивность */
            @media (max-width: 900px) { .sketch-module { width: calc(33.333% - 15px); } .deco-left, .deco-right { display: none; } }
            @media (max-width: 600px) { .sketch-module { width: calc(50% - 15px); } .sketch-btn-start { padding: 12px 30px; font-size: 20px; } .right-bottom-badges { right: -60px; top: 60px; } .streak-badge { font-size: 24px; } .progress-xp-badge { font-size: 20px; } .badge-label { font-size: 14px; } .sketch-btn-profile, .sketch-btn-leaderboard { position: static; display: inline-block; margin: 5px; } .top-buttons-container { text-align: center; margin-bottom: 20px; } }
        </style>

        <div class="sketch-landing-wrapper">
            <div class="top-buttons-container">
                <a href="https://moneyneko.local/profile-2/" class="sketch-btn-profile">Профиль</a>
                <a href="https://moneyneko.local/доска-лидеров/" class="sketch-btn-leaderboard">Доска лидеров</a>
            </div>

            <div class="sketch-bg-deco deco-left">📈 💳<br>📊</div>
            <div class="sketch-bg-deco deco-right">🍕 🧮<br>🪙 🐖</div>
            <div class="sketch-hero-section">
                <div class="cat-container">

                    <!-- ПОЛОСА НАСТРОЕНИЯ (Градиент и цифры) -->
                    <div class="mood-progress-wrapper" title="Настроение кота: <?php echo $mood_points; ?>/100">

                        <!-- ДОБАВЛЕНА ЭТА СТРОКА С ЦИФРАМИ -->
                        <div class="mood-progress-text"><?php echo $mood_points; ?> / 100</div>

                        <div class="mood-progress-container">
                            <div class="mood-progress-fill" style="width: <?php echo $mood_points; ?>%;"></div>
                        </div>
                    </div>

                    <?php
                    // ДИНАМИЧЕСКИЙ ВЫВОД КОТА
                    $cat_image_url = '/image/funcat.png';
                    if ( is_user_logged_in() && function_exists('moneyneko_get_dynamic_cat_image') ) {
                        // Передаем настроение кота, чтобы получить нужную эмоцию
                        $cat_image_url = moneyneko_get_dynamic_cat_image( $mood_points );
                    }
                    ?>
                    <img src="<?php echo esc_url( $cat_image_url ); ?>" alt="Манеки-нэко" class="cat-image">

                    <?php if ( is_user_logged_in() ) : ?>
                        <!-- КОНТЕЙНЕР ДЛЯ СТРИКА И XP -->
                        <div class="right-bottom-badges">
                            <div class="streak-badge">
                                🔥 <?php echo $streak; ?> <span class="badge-label">дней</span>
                            </div>
                            <div class="progress-xp-badge">
                                <?php echo $progress_points; ?> <span class="badge-label">XP</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <a href="https://moneyneko.local/courses/moneyneko-full-course/lessons/твой-первый-шаг-к-финансовой-свободе-2/" class="sketch-btn-start">Начать обучение</a>
            </div>

            <div class="sketch-modules-grid">
                <!-- Модули -->
                <a href="https://moneyneko.local/courses/moneyneko-full-course/lessons/тема-1-cash-flow-как-отслеживать-притоки-и-отто/" class="sketch-module mod-1">
                    <div class="module-title">Модуль 1.</div>
                    <div class="module-desc">Учет денежных<br>потоков</div>
                    <div class="module-icon">💰</div>
                </a>

                <a href="https://moneyneko.local/courses/moneyneko-full-course/lessons/тема-1-zero-based-budgeting-бюджет-с-нуля/" class="sketch-module mod-2">
                    <div class="module-title">Модуль 2.</div>
                    <div class="module-desc">Гибкое<br>планирование</div>
                    <div class="module-icon">📅</div>
                </a>

                <a href="https://moneyneko.local/courses/moneyneko-full-course/lessons/тема-1-ликвидность-способность-отвеча/" class="sketch-module mod-3">
                    <div class="module-title">Модуль 3.</div>
                    <div class="module-desc">Управление<br>ликвидностью и<br>покупкой<br>безопасности</div>
                    <div class="module-icon">🛡️</div>
                </a>

                <a href="https://moneyneko.local/courses/moneyneko-full-course/lessons/тема-1-совокупная-стоимость-владения-tco/" class="sketch-module mod-4">
                    <div class="module-title">Модуль 4.</div>
                    <div class="module-desc">Рациональное<br>принятие<br>решений</div>
                    <div class="module-icon">💡</div>
                </a>

                <a href="https://moneyneko.local/courses/moneyneko-full-course/lessons/тема-1-налоговый-вычет-за-обучение-и-ле/" class="sketch-module mod-5">
                    <div class="module-title">Модуль 5.</div>
                    <div class="module-desc">Инструменты<br>оптимизации<br>и автоматизации</div>
                    <div class="module-icon">⚙️</div>
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    add_shortcode( 'custom_homepage', 'custom_homepage_landing_render' );
}

if ( ! function_exists( 'custom_main_homepage_render' ) ) {

    function custom_main_homepage_render() {
        ob_start();
        ?>
        <link href="https://fonts.googleapis.com/css2?family=Balsamiq+Sans:wght@400;700&display=swap" rel="stylesheet">

        <style>
            /* --- ЭФФЕКТ ЛИСТА БУМАГИ (Контейнер Главной Страницы) --- */
            .sketch-main-wrapper {
                position: relative;
                background-color: #fffdf5;
                border: 2px solid #2c3e50;
                border-radius: 15px 255px 15px 225px / 225px 15px 255px 15px;
                box-shadow: 8px 12px 25px rgba(0,0,0,0.08);
                padding: 60px 40px;
                font-family: 'Balsamiq Sans', cursive, sans-serif;
                color: #2c3e50;
                box-sizing: border-box;
                overflow: hidden;
                max-width: 1100px;
                margin: 40px auto;
            }

            /* --- СЕКЦИЯ 1: Приветствие и кнопка --- */
            .sketch-hero-block {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 40px;
                margin-bottom: 60px;
            }

            .hero-image-wrap {
                flex: 1;
                position: relative;
            }

            .hero-image-wrap img {
                width: 100%;
                border-radius: 50% / 40%;
                border: 6px solid #fff;
                box-shadow: 3px 5px 15px rgba(0,0,0,0.15);
                transform: rotate(-2deg);
                object-fit: cover;
            }

            .hero-text-wrap {
                flex: 1;
                text-align: center;
            }

            .hero-title {
                font-size: 38px;
                font-weight: bold;
                line-height: 1.3;
                margin-bottom: 30px;
                color: #1a252f;
            }

            .sketch-btn-primary {
                display: inline-block;
                background-color: #3498db;
                color: #fff !important;
                font-size: 24px;
                font-weight: bold;
                text-decoration: none !important;
                padding: 15px 50px;
                border: 3px solid #2c3e50;
                border-radius: 255px 15px 225px 15px / 15px 225px 15px 255px;
                box-shadow: 3px 6px 0px #2c3e50;
                transition: transform 0.1s, box-shadow 0.1s;
                cursor: pointer;
            }

            .sketch-btn-primary:active {
                transform: translateY(4px);
                box-shadow: 0px 2px 0px #2c3e50;
            }
            .sketch-btn-primary:hover {
                filter: brightness(1.1);
            }

            /* --- СЕКЦИЯ 2: Описание и второй кот --- */
            .sketch-about-block {
                text-align: center;
                margin-bottom: 50px;
            }

            .about-text {
                font-size: 22px;
                line-height: 1.5;
                margin-bottom: 40px;
                max-width: 900px;
                margin-left: auto;
                margin-right: auto;
                background: rgba(255, 255, 255, 0.7);
                padding: 15px 30px;
                border-radius: 10px;
                border: 2px dashed #bdc3c7;
            }

            .about-image-wrap img {
                max-width: 700px;
                width: 100%;
                border-radius: 40% / 50%;
                border: 6px solid #fff;
                box-shadow: 3px 5px 15px rgba(0,0,0,0.15);
                transform: rotate(1deg);
                margin-bottom: 20px;
            }

            /* --- СЕКЦИЯ 3: ЛЕНТА МЯУ-НОВОСТЕЙ (ПОД КАРТИНКОЙ) --- */
            .sketch-news-section {
                margin-bottom: 50px;
                width: 100%;
            }

            .sketch-section-title {
                font-size: 28px;
                font-weight: bold;
                margin-bottom: 20px;
                text-align: center;
            }

            .sketch-news-container {
                display: flex;
                gap: 20px;
                width: 100%;
            }

            .sketch-news-card {
                flex: 1;
                background: #e8f4f8; /* Голубоватый стикер */
                padding: 20px;
                border: 2px solid #2c3e50;
                border-radius: 5px 15px 5px 15px;
                box-shadow: 4px 6px 0px #2c3e50;
                position: relative;
                transition: transform 0.2s;
                text-align: left;
            }

            .sketch-news-card:nth-child(even) {
                background: #fce4ec; /* Розоватый стикер */
                transform: rotate(1.5deg);
            }
            .sketch-news-card:nth-child(odd) {
                transform: rotate(-1deg);
            }
            .sketch-news-card:hover {
                transform: translateY(-5px) rotate(0);
            }

            /* Канцелярский гвоздик сверху каждого стикера новостей */
            .sketch-news-card::before {
                content: '';
                position: absolute;
                top: -8px;
                left: 50%;
                transform: translateX(-50%);
                width: 12px;
                height: 12px;
                background-color: #7f8c8d;
                border-radius: 50%;
                border: 2px solid #2c3e50;
            }

            .news-date { font-size: 14px; color: #7f8c8d; font-weight: bold; margin-bottom: 8px; }
            .news-title { font-size: 18px; font-weight: bold; margin-bottom: 8px; color: #2c3e50; }
            .news-text { font-size: 15px; line-height: 1.4; color: #34495e; }

            /* --- СЕКЦИЯ 4: ФАКТ ДНЯ (ПО ЦЕНТРУ СНИЗУ) --- */
            .sketch-fact-section {
                margin-bottom: 50px;
                text-align: center;
                width: 100%;
            }

            .sketch-fact-card {
                background: #ebf5df; /* Зеленоватый */
                padding: 25px 30px;
                border: 2px dashed #2ecc71;
                border-radius: 15px;
                max-width: 650px; /* Ограничиваем ширину, чтобы смотрелось аккуратно */
                margin: 0 auto;  /* Центрируем блок */
                transform: rotate(0.5deg);
                box-sizing: border-box;
            }

            .fact-icon { font-size: 40px; margin-bottom: 10px; }
            .fact-text { font-size: 17px; line-height: 1.5; font-weight: bold; color: #27ae60; }

            /* --- СЕКЦИЯ 5: Цитата кота --- */
            .sketch-quote-block {
                margin-top: 40px;
                padding: 30px;
                background-color: #fff9c4;
                border: 1px solid #e2c02c;
                border-radius: 2px 15px 3px 15px;
                box-shadow: 2px 4px 10px rgba(0,0,0,0.05);
                transform: rotate(-1deg);
                position: relative;
            }

            .sketch-quote-block::before {
                content: '';
                position: absolute;
                top: -10px;
                left: 50%;
                transform: translateX(-50%);
                width: 16px;
                height: 16px;
                background-color: #e74c3c;
                border-radius: 50%;
                box-shadow: 1px 2px 3px rgba(0,0,0,0.3);
            }

            .quote-text {
                font-size: 22px;
                font-style: italic;
                line-height: 1.4;
                margin-bottom: 15px;
                color: #2c3e50;
            }

            .quote-author {
                text-align: right;
                font-size: 18px;
                font-weight: bold;
                color: #7f8c8d;
            }

            /* Адаптивность для телефонов */
            @media (max-width: 800px) {
                .sketch-hero-block { flex-direction: column-reverse; text-align: center; }
                .sketch-news-container { flex-direction: column; }
                .hero-title { font-size: 32px; }
                .about-text, .quote-text { font-size: 18px; }
                .sketch-main-wrapper { padding: 30px 15px; }
            }
        </style>

        <div class="sketch-main-wrapper">

            <div class="sketch-hero-block">
                <div class="hero-image-wrap">
                    <img src="/image/catlookmoney.gif" alt="Деньги и кот">
                </div>

                <div class="hero-text-wrap">
                    <div class="hero-title">Добро пожаловать на сайт MoneyNeko!</div>
                    <a href="/game/" class="sketch-btn-primary">Начать игру</a>
                </div>
            </div>

            <div class="sketch-about-block">
                <div class="about-text">
                    Хотим представить вашему вниманию интерактивную игру, которая спешит помочь вам стать профи в сфере финансов
                </div>
                <div class="about-image-wrap">
                    <img src="/image/catinstreett.png" alt="Кот на улице">
                </div>
            </div>

            <div class="sketch-news-section">
                <div class="sketch-section-title">📰 Мяу-новости</div>
                <div class="sketch-news-container">
                    <div class="sketch-news-card">
                        <div class="news-date">Сегодня</div>
                        <div class="news-title">А вы найдете чеки в комнате?</div>
                        <div class="news-text">Мы добавили новый Тренажер. Помоги Ване найти потерянные чеки и отсортировать их.</div>
                    </div>
                    <div class="sketch-news-card">
                        <div class="news-date">Вчера</div>
                        <div class="news-title">Система стриков</div>
                        <div class="news-text">Теперь за ежедневный вход на сайт ты получаешь бонусные очки. Не пропусти ни дня!</div>
                    </div>
                </div>
            </div>

            <div class="sketch-fact-section">
                <div class="sketch-section-title">💡 Факт дня</div>
                <div class="sketch-fact-card">
                    <div class="fact-icon">💰</div>
                    <div class="fact-text">Если откладывать всего 10% от любой полученной суммы, через год у тебя сформируется надежная подушка безопасности!</div>
                </div>
            </div>

            <div class="sketch-quote-block">
                <div class="quote-text">
                    «Мрррр, следите за обновлениями на мррроём сайте и сохраняйте монетки в своих кошшшельках»
                </div>
                <div class="quote-author">
                    Кот MoneyNeko
                </div>
            </div>

        </div>
        <?php
        return ob_get_clean();
    }

    // Регистрируем шорткод для главной страницы
    add_shortcode( 'custom_main_page', 'custom_main_homepage_render' );
}

if ( ! function_exists( 'custom_moneyneko_header_render' ) ) {
    function custom_moneyneko_header_render() {
        ob_start();

        $is_logged_in = is_user_logged_in();
        $current_user = wp_get_current_user();
        ?>
        <link href="https://fonts.googleapis.com/css2?family=Balsamiq+Sans:wght@400;700&display=swap" rel="stylesheet">
        <style>
            .mn-header-wrapper {
                background-color: #2c3e50;
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 30px;
                font-family: 'Balsamiq Sans', cursive, sans-serif;
                color: #ffffff;

                /* Полноэкранный брейкаут для десктопа */
                width: 100vw;
                max-width: 100vw;
                margin-left: calc(-50vw + 50%);
                margin-right: calc(-50vw + 50%);
                box-sizing: border-box;
                box-shadow: 0px 4px 10px rgba(0,0,0,0.1);

                /* Верх полностью прямой (0), низ уходит вглубь формы со скетч-эффектом */
                border-radius: 0px 0px 15px 30px / 0px 0px 30px 15px !important;
            }
            .mn-header-left {
                display: flex;
                align-items: center;
                gap: 15px;
                text-decoration: none !important;
            }
            .mn-header-logo {
                width: 50px;
                height: auto;
                object-fit: contain;
            }
            .mn-header-title {
                font-size: 26px;
                font-weight: bold;
                color: #ffffff;
                text-decoration: none !important;
                margin: 0;
                letter-spacing: 0.5px;
            }
            .mn-header-right {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .mn-btn {
                padding: 8px 25px;
                border-radius: 255px 15px 225px 15px/15px 225px 15px 255px;
                font-size: 16px;
                font-weight: bold;
                text-decoration: none !important;
                transition: transform 0.1s, box-shadow 0.1s;
                cursor: pointer;
                display: inline-block;
                box-shadow: 2px 4px 0px rgba(0,0,0,0.3);
            }
            .mn-btn:active {
                transform: translateY(2px);
                box-shadow: 0px 2px 0px rgba(0,0,0,0.3);
            }
            .mn-btn-blue {
                background-color: #3498db;
                color: #ffffff !important;
                border: 2px solid #2c3e50;
            }
            .mn-btn-blue:hover {
                filter: brightness(1.1);
            }
            .mn-btn-outline {
                background-color: #fffdf5;
                color: #2c3e50 !important;
                border: 2px solid #2c3e50;
            }
            .mn-btn-outline:hover {
                background-color: #f0f8ff;
            }
            .mn-header-avatar {
                width: 45px;
                height: 45px;
                border-radius: 50% 40% 50% 40%;
                object-fit: cover;
                background-color: #fffdf5;
                border: 2px solid #ffffff;
            }

            /* ПОЛНЫЙ ФИКС МОБИЛЬНОГО СДВИГА И АДАПТИВНОСТЬ */
            @media (max-width: 768px) {
                .mn-header-wrapper {
                    /* Отключаем vw-брейкауты, ломающие мобильную верстку */
                    width: 100% !important;
                    max-width: 100% !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;

                    flex-direction: column !important;
                    gap: 15px !important;
                    padding: 20px 15px !important;

                    /* Пропорционально уменьшаем скетч-радиус под мобильные экраны */
                    border-radius: 0px 0px 10px 20px / 0px 0px 20px 10px !important;
                }
                .mn-header-right {
                    width: 100% !important;
                    justify-content: center !important;
                    flex-wrap: wrap !important;
                }
                .mn-btn {
                    padding: 8px 20px !important;
                    font-size: 15px !important;
                }
            }
        </style>

        <div class="mn-header-wrapper">

            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mn-header-left">
                <img src="/image/cat.png" alt="MoneyNeko Logo" class="mn-header-logo">
                <span class="mn-header-title">moneyneko</span>
            </a>

            <div class="mn-header-right">

                <?php if ( ! $is_logged_in ) : ?>
                    <a href="<?php echo esc_url( home_url( '/student-registration-page/' ) ); ?>" class="mn-btn mn-btn-blue">Регистрация</a>
                    <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="mn-btn mn-btn-blue">Войти</a>

                <?php else : ?>
                    <a href="<?php echo esc_url( home_url( '/profile-2/' ) ); ?>" class="mn-btn mn-btn-outline">Профиль</a>
                    <?php
                    echo get_avatar( $current_user->ID, 45, '', '', array( 'class' => 'mn-header-avatar' ) );
                    ?>
                <?php endif; ?>

            </div>

        </div>
        <?php
        return ob_get_clean();
    }
    add_shortcode( 'custom_header', 'custom_moneyneko_header_render' );
}

// ==========================================
// 1. ЖЕСТКИЙ ЛИМИТ НАСТРОЕНИЯ (МАКСИМУМ 100)
// ==========================================
add_action( 'gamipress_awarded_points', 'moneyneko_limit_mood_points_max', 10, 5 );
function moneyneko_limit_mood_points_max( $user_id, $points, $points_type, $reason, $log_id ) {
    if ( $points_type === 'mood-points' ) {
        $current_balance = gamipress_get_user_points( $user_id, 'mood-points' );
        // Если перевалили за 100, вычитаем излишек
        if ( $current_balance > 100 ) {
            $excess = $current_balance - 100;
            gamipress_deduct_points_from_user( $user_id, $excess, 'mood-points' );
        }
    }
}

// ==========================================
// 2.1 ВЫДАЧА БАЛЛОВ ПРИ РЕГИСТРАЦИИ
// ==========================================
add_action( 'user_register', 'moneyneko_award_points_on_registration' );
function moneyneko_award_points_on_registration( $user_id ) {
    if ( class_exists( 'GamiPress' ) ) {
        // Гарантированно выдаем стартовые 50 баллов настроения сразу при создании аккаунта
        gamipress_award_points_to_user( $user_id, 50, 'mood-points' );

        // Задаем начальные значения для стрика, чтобы при первом входе не было конфликтов
        update_user_meta( $user_id, '_mn_login_streak', 1 );
        update_user_meta( $user_id, '_mn_last_login_date', current_time( 'Y-m-d' ) );
    }
}

// ==========================================
// 2.2 СИСТЕМА СТРИКОВ, БОНУСОВ И ШТРАФОВ
// ==========================================
if ( ! function_exists( 'moneyneko_track_daily_streak' ) ) {
    function moneyneko_track_daily_streak( $user_login, $user ) {
        if ( ! class_exists( 'GamiPress' ) ) return;

        $user_id = $user->ID;
        $today = current_time( 'Y-m-d' );
        $last_login = get_user_meta( $user_id, '_mn_last_login_date', true );
        $streak     = (int) get_user_meta( $user_id, '_mn_login_streak', true );

        // Если это первый вход (подстраховка, если баллы при регистрации не сработали)
        if ( empty( $last_login ) ) {
            update_user_meta( $user_id, '_mn_login_streak', 1 );
            update_user_meta( $user_id, '_mn_last_login_date', $today );
            return;
        }

        // Если сегодня уже логинился — ничего не делаем
        if ( $last_login === $today ) return;

        // Считаем пропущенные дни
        $days_missed = 0;
        if ( ! empty( $last_login ) ) {
            $diff = strtotime( $today ) - strtotime( $last_login );
            $days_missed = floor( $diff / (60 * 60 * 24) ) - 1;
        }

        // --- ЛОГИКА ШТРАФОВ ---
        if ( $days_missed > 0 ) {
            $streak = 0; // Стрик обнуляется
            $current_mood = (int) gamipress_get_user_points( $user_id, 'mood-points' );

            for ( $i = 1; $i <= $days_missed; $i++ ) {
                $base_loss = 0;

                if ( $current_mood >= 90 ) $base_loss = 15;
                elseif ( $current_mood >= 75 ) $base_loss = 12;
                elseif ( $current_mood >= 40 ) $base_loss = 6;
                elseif ( $current_mood >= 10 ) $base_loss = 2;

                if ( $base_loss > 0 ) {
                    $multiplier = 0.5 + (0.5 * $i);
                    $daily_loss = round( $base_loss * $multiplier );
                    $current_mood -= $daily_loss;
                    if ( $current_mood < 0 ) $current_mood = 0;
                }
            }

            // Синхронизируем вычет с GamiPress (с защитой)
            if ( function_exists( 'gamipress_get_user_points' ) && function_exists( 'gamipress_deduct_points_to_user' ) ) {
                $actual_mood = (int) gamipress_get_user_points( $user_id, 'mood-points' );
                $points_to_deduct = $actual_mood - $current_mood;
                if ( $points_to_deduct > 0 ) {
                    gamipress_deduct_points_to_user( $user_id, $points_to_deduct, 'mood-points' );
                }
            }
        }
        // --- ЛОГИКА БОНУСОВ ЗА СТРИК ---
        else {
            $streak++;
            if ( $streak > 1 ) {
                // Каждый 7-й день даем 10 XP, в остальные дни стрика — 5 XP
                $bonus_progress = ( $streak % 7 == 0 ) ? 10 : 5;
                gamipress_award_points_to_user( $user_id, $bonus_progress, 'progress-points' );
            }
        }

        // Обновляем данные пользователя в БД
        update_user_meta( $user_id, '_mn_login_streak', $streak );
        update_user_meta( $user_id, '_mn_last_login_date', $today );
    }

    add_action( 'wp_login', 'moneyneko_track_daily_streak', 10, 2 );
}

// ==========================================
// 2.3 ЛОГИКА СМЕНЫ НАСТРОЕНИЯ КОТА (ФОТО)
// ==========================================
function moneyneko_get_dynamic_cat_image( $mood_points ) {
    $mood_points = (int) $mood_points;

    // 0-9: Отчаяние (😭)
    if ( $mood_points >= 0 && $mood_points <= 9 ) {
        return '/image/emotions/отчаяние.png';
    }
    // 10-39: Случайная плохая эмоция
    elseif ( $mood_points >= 10 && $mood_points <= 39 ) {
        $images = array(
            '/image/emotions/грусть.png',
            '/image/emotions/обида.png',
            '/image/emotions/кошачьи_глазки.png'
        );
        return $images[ array_rand( $images ) ];
    }
    // 40-74: Случайная нейтральная эмоция
    elseif ( $mood_points >= 40 && $mood_points <= 74 ) {
        $images = array(
            '/image/emotions/задумчивый.png',
            '/image/emotions/безразличный.png',
            '/image/emotions/сонливость.png'
        );
        return $images[ array_rand( $images ) ];
    }
    // 75-89: Случайная хорошая эмоция
    elseif ( $mood_points >= 75 && $mood_points <= 89 ) {
        $images = array(
            '/image/emotions/радость.png',
            '/image/emotions/восхищение.png',
            '/image/emotions/милашка.png'
        );
        return $images[ array_rand( $images ) ];
    }
    // 90-100: Крутость (😎)
    elseif ( $mood_points >= 90 && $mood_points <= 100 ) {
        $images = array(
            '/image/emotions/крутой.png'
        );
        return $images[array_rand($images)];
    }
    return '/image/funcat.png';
}
