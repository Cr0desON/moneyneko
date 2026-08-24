<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

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