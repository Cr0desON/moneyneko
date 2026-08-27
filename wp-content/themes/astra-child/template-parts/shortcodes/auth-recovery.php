<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="password-recovery-form">
    <h2>Восстановление пароля</h2>

    <?php if ($recovery_message): ?>
        <div class="custom-auth-recovery-success">
            <?php echo esc_html($recovery_message); ?>
        </div>
        <p><a href="<?php echo esc_url( home_url( '/login' ) ); ?>">← Вернуться ко входу</a></p>
    <?php else: ?>
        <form method="post">
            <p>
                <label>
                    Введите никнейм или email:<br>
                    <input type="text" name="user_login" class="custom-auth-input-recovery" required>
                </label>
            </p>
            <p>
                <input type="submit" name="recover_password" value="Получить ссылку" class="button button-primary">
            </p>
        </form>

        <p class="custom-auth-link-mt15">
            <a href="<?php echo esc_url( home_url( '/login' ) ); ?>">← Вернуться ко входу</a>
        </p>
    <?php endif; ?>
</div>