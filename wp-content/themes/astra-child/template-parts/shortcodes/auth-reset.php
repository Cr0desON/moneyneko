<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<?php if ( $is_invalid_key ): ?>
    <div class="custom-auth-reset-wrapper-center">
        <h2><?php echo esc_html($invalid_title); ?></h2>
        <p class="custom-auth-error-text">Ссылка для сброса пароля недействительна или истекла.</p>
        <p><a href="<?php echo home_url('/password-recovery'); ?>">Запросить новую ссылку</a></p>
    </div>
<?php else: ?>
    <div class="custom-auth-reset-wrapper">
        <h2>Установка нового пароля</h2>
        <p class="custom-auth-desc">Введите новый пароль для аккаунта <strong><?php echo esc_html($user->user_login); ?></strong></p>

        <?php if ($error_message): ?>
            <div class="custom-auth-error">
                <?php echo esc_html($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <p>
                <label>Новый пароль<br>
                    <input type="password" name="new_password" class="custom-auth-input-reset" required minlength="6">
                </label>
            </p>
            <p>
                <label>Подтвердите пароль<br>
                    <input type="password" name="confirm_password" class="custom-auth-input-reset" required minlength="6">
                </label>
            </p>
            <p class="custom-auth-submit-mt25">
                <input type="hidden" name="reset_key" value="<?php echo esc_attr($reset_key); ?>">
                <input type="hidden" name="reset_login" value="<?php echo esc_attr($reset_login); ?>">
                <input type="submit" name="custom_password_reset_submit" value="Установить пароль" class="custom-auth-btn-blue">
            </p>
        </form>
        <p class="custom-auth-link-mt15">
            <a href="<?php echo home_url('/login'); ?>">← Вернуться ко входу</a>
        </p>
    </div>
<?php endif; ?>