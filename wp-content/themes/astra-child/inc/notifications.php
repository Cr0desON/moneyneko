<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * 1. Базовая функция отправки Push-уведомлений через API OneSignal
 */
function moneyneko_send_onesignal_push( $user_id, $heading, $message ) {
    // Автоматически берем ключи из настроек плагина в админке
    $onesignal_settings = get_option('OnesignalWPSettings');
    $app_id = isset($onesignal_settings['app_id']) ? $onesignal_settings['app_id'] : '';
    $rest_api_key = isset($onesignal_settings['app_rest_api_key']) ? $onesignal_settings['app_rest_api_key'] : '';

    if ( empty($app_id) || empty($rest_api_key) ) return false;

    $fields = array(
        'app_id' => $app_id,
        'include_external_user_ids' => array( strval($user_id) ), 
        'contents' => array( "ru" => $message ),
        'headings' => array( "ru" => $heading )
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Authorization: Basic ' . $rest_api_key));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

/**
 * 2. Событийные пуши: Падение настроения (Срабатывает мгновенно)
 */
add_action( 'gamipress_deduct_points_to_user', 'moneyneko_mood_drop_push', 10, 3 );
add_action( 'gamipress_revoke_points_to_user', 'moneyneko_mood_drop_push', 10, 3 );
function moneyneko_mood_drop_push( $user_id, $points, $points_type ) {
    if ( $points_type !== 'mood-points' ) return;

    $current_mood = gamipress_get_user_points( $user_id, 'mood-points' );
    $message = '';

    // Временные заглушки
    if ( $current_mood >= 90 ) {
        $message = "Ой, мы потеряли балл настроения! Но мы всё ещё на вершине, не расслабляемся!";
    } elseif ( $current_mood >= 75 ) {
        $message = "Настроение котика немного просело. Давай вернем его на максимум!";
    } elseif ( $current_mood >= 40 ) {
        $message = "Внимание, запас настроения тает! Зайди и пополни его.";
    } elseif ( $current_mood >= 10 ) {
        $message = "Критическое падение настроения! Котику срочно нужна твоя активность!";
    } else {
        $message = "Настроение упало на самое дно... Мы теряем контроль над ситуацией!";
    }

    if ( ! empty( $message ) ) {
        moneyneko_send_onesignal_push( $user_id, 'MoneyNeko', $message );
    }
}

/**
 * 3. Защита стрика: Напоминания без повторов в 17:00, 20:00 и 23:00
 */
add_action('init', function() {
    if (!wp_next_scheduled('moneyneko_hourly_streak_check')) {
        wp_schedule_event(time(), 'hourly', 'moneyneko_hourly_streak_check');
    }
});

add_action('moneyneko_hourly_streak_check', 'moneyneko_process_streak_reminders');
function moneyneko_process_streak_reminders() {
    // Получаем текущий час с учетом часового пояса сайта
    $current_hour = (int) current_time('H'); 
    
    // Скрипт идет дальше ТОЛЬКО если сейчас 17, 20 или 23 часа
    if ( ! in_array( $current_hour, array( 17, 20, 23 ) ) ) return;

    $users = get_users(); 
    $today = current_time('Y-m-d');

    foreach ( $users as $user ) {
        $user_id = $user->ID;
        
        // ФИЛЬТР: Пропускаем пользователя, если он уже заходил сегодня
        $last_login = get_user_meta( $user_id, '_mn_last_login_date', true );
        if ( $last_login === $today ) {
            continue;
        }

        $current_mood = gamipress_get_user_points( $user_id, 'mood-points' );
        $messages = array();

        // Распределяем тексты аналитиков по уровням настроения
        if ( $current_mood >= 90 ) {
            $messages = array(
                'msg_90_1' => 'Расширение территории: абсолютный баланс! Среди всех студентов лишь мы познали истинную силу накоплений 😎. Зайди продолжить стрик!',
                'msg_90_2' => 'Жадность — это хорошо, особенно если это жадность к знаниям 😎. Твой финансовый уровень пробивает потолок!',
                'msg_90_3' => 'Ты король мира 😎! Твой стрик непотопляем, а финансовая грамотность пробивает стратосферу. Продолжай в том же духе!'
            );
        } elseif ( $current_mood >= 75 ) {
            $messages = array(
                'msg_75_1' => 'Мяу! Твой бюджет сияет ярче золота! Давай пройдем короткую лекцию на 5 минут и закрепим успех?',
                'msg_75_2' => 'Продай мне эту ручку... или просто грамотно сэкономь на ней 😄! Применяй эти знания на практике, и у тебя всё получится',
                'msg_75_3' => 'Да пребудет с тобой сила сложного процента! Наш капитал знаний растет, давай закрепим его в тренажере.'
            );
        } elseif ( $current_mood >= 40 ) {
            $messages = array(
                'msg_40_1' => 'В этом бизнесе нужно быть первым, умным или... просто вовремя заходить в приложение. Жду тебя на новом модуле!',
                'msg_40_2' => 'Мы покупаем вещи, которые нам не нужны, на деньги, которых у нас нет 😑... Пора зайти в приложение и вернуть контроль над расходами.',
                'msg_40_3' => 'Кажется, я засыпаю от отсутствия активности 😴... Пройди тренажер, чтобы нас немного взбодрить.'
            );
        } elseif ( $current_mood >= 10 ) {
            $messages = array(
                'msg_10_1' => 'Стрик может сгореть, а я уже начинаю копить обиду ☹️. Пройди хотя бы одну лекцию!',
                'msg_10_2' => 'Законно ли так надолго бросать своего кота? Абсолютно незаконно ☹️! Я теряю баллы, скорее вернись и спаси ситуацию!',
                'msg_10_3' => 'Моя прелесть... Мои баллы настроения тают на глазах 🥺! Не оставляй меня одного, пройди хотя бы одну лекцию.'
            );
        } else {
            $messages = array(
                'msg_0_1' => 'Беспросветный мрак поглощает мой бюджет 😭... Я проваливаюсь в бездну пустых кошельков, вернись и спаси меня!',
                'msg_0_2' => 'Это финансовый крах 😭! Телефоны оборваны, акции нашего настроения рухнули до нуля. Только пройденная лекция спасет от дефолта!',
                'msg_0_3' => 'Не отпускай наш стрик 😭! Мы идем на финансовое дно, срочно пройди тест, чтобы спасти меня!'
            );
        }

        // Логика исключения повторов
        $meta_key = 'moneyneko_pushes_today_' . $today;
        $used_today = get_user_meta( $user_id, $meta_key, true );
        if ( ! is_array( $used_today ) ) $used_today = array();

        // Убираем те сообщения, ключи которых уже есть в $used_today
        $available_messages = array_diff_key( $messages, array_flip( $used_today ) );

        if ( ! empty( $available_messages ) ) {
            // Выбираем случайный текст из оставшихся
            $random_key = array_rand( $available_messages );
            $message_to_send = $available_messages[$random_key];

            moneyneko_send_onesignal_push( $user_id, 'MoneyNeko', $message_to_send );

            // Запоминаем, что это сообщение отправлено
            $used_today[] = $random_key;
            update_user_meta( $user_id, $meta_key, $used_today );
        }
    }
}

/**
 * 4. Связываем ID пользователя WordPress с его подпиской в OneSignal
 */
add_action('wp_head', 'moneyneko_onesignal_link_user');
function moneyneko_onesignal_link_user() {
    // Выводим скрипт только если пользователь авторизован
    if ( is_user_logged_in() ) {
        $user_id = get_current_user_id();
        ?>
        <script>
            window.OneSignalDeferred = window.OneSignalDeferred || [];
            window.OneSignalDeferred.push(function(OneSignal) {
                // Привязываем WP ID к OneSignal (работает и для новых, и для старых версий API)
                if (typeof OneSignal.login === 'function') {
                    OneSignal.login("<?php echo esc_js($user_id); ?>");
                } else if (typeof OneSignal.setExternalUserId === 'function') {
                    OneSignal.setExternalUserId("<?php echo esc_js($user_id); ?>");
                }
            });
        </script>
        <?php
    }
}