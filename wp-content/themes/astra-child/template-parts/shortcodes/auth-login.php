<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="wp-login-form">
    <h2>Добро пожаловать!</h2>

    <?php if ($reset_success): ?>
        <div class="custom-auth-success">
            <?php echo esc_html($reset_success); ?>
        </div>
    <?php endif; ?>

    <?php if ($login_error): ?>
        <div class="custom-auth-error">
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

    <p class="custom-auth-link-mt15">
        <a href="<?php echo esc_url(home_url('/password-recovery')); ?>">Забыли пароль?</a>
    </p>

    <p class="custom-auth-link-mt20">
        Нет аккаунта? <a href="<?php echo esc_url(home_url('/student-registration-page')); ?>">Зарегистрируйся сейчас</a>
    </p>

    <p class="custom-auth-link-mt20">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">← На главную</a>
    </p>
</div>