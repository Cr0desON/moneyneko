<?php
/**
 * Функции и определения дочерней темы Astra Child MoneyNeko
 */

// Защита от прямого обращения к файлу
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Подключение базовых скриптов и стилей
require_once get_stylesheet_directory() . '/inc/enqueue-scripts.php';

// 2. Управление правами и перенаправления
require_once get_stylesheet_directory() . '/inc/security-redirects.php';

// 3. Формы авторизации и восстановление пароля
require_once get_stylesheet_directory() . '/inc/shortcodes-auth.php';

// 4. Визуальные интерфейсы (Профиль, Лидерборд, Шапка)
require_once get_stylesheet_directory() . '/inc/shortcodes-ui.php';

// 5. Кастомизация интерфейса Tutor LMS
require_once get_stylesheet_directory() . '/inc/tutor-lms-custom.php';

// 6. Интеграция и хуки для GamiPress (Очки, Стрики, Кот)
require_once get_stylesheet_directory() . '/inc/gamipress-hooks.php';