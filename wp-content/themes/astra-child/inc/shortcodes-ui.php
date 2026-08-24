<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

// --- ДОСКА ЛИДЕРОВ ---
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
        $current_id = get_current_user_id(); // Получаем ID здесь, чтобы передать в HTML

        ob_start();
        // Подключаем наш чистый HTML-шаблон
        include get_stylesheet_directory() . '/template-parts/shortcodes/leaderboard.php';
        return ob_get_clean();
    }
    add_shortcode( 'custom_leaderboard', 'custom_leaderboard_render' );
}

// --- ПРОФИЛЬ ПОЛЬЗОВАТЕЛЯ ---
if ( ! function_exists( 'custom_user_profile_render' ) ) {
    function custom_user_profile_render() {
        ob_start();

        if ( ! is_user_logged_in() ) {
            // Если гость — подгружаем HTML для гостя
            include get_stylesheet_directory() . '/template-parts/shortcodes/profile-guest.php';
            return ob_get_clean();
        }

        // Логика для авторизованного: вычисления
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;
        $meta_key = '_gamipress_progress-points_points';
        $user_points = (int) get_user_meta( $user_id, $meta_key, true );

        $top_users_query = new WP_User_Query( array(
            'number'   => 3,
            'meta_key' => $meta_key,
            'orderby'  => 'meta_value_num',
            'order'    => 'DESC',
        ) );
        $top_users = $top_users_query->get_results();

        // Подгружаем HTML для авторизованного юзера
        include get_stylesheet_directory() . '/template-parts/shortcodes/profile-user.php';
        return ob_get_clean();
    }
    add_shortcode( 'custom_user_profile', 'custom_user_profile_render' );
}

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