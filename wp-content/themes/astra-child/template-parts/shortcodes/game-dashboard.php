<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php
// Собираем данные интерактивной карты через адаптер
$user_id          = get_current_user_id();
$map_data         = function_exists( 'mn_get_map_nodes' ) ? mn_get_map_nodes( $user_id ) : array( 'modules' => array(), 'current_node_id' => 0 );
$current_node     = $map_data['current_node_id'];
$last_cat_node    = (int) get_user_meta( $user_id, '_mn_cat_node', true );
$streak_last_two  = (int) $streak % 100;
$streak_last      = (int) $streak % 10;
$streak_label     = 'дней';
if ( $streak_last_two < 11 || $streak_last_two > 14 ) {
    if ( 1 === $streak_last ) {
        $streak_label = 'день';
    } elseif ( $streak_last >= 2 && $streak_last <= 4 ) {
        $streak_label = 'дня';
    }
}
$current_module   = null;

foreach ( $map_data['modules'] as $module ) {
    foreach ( $module['nodes'] as $node ) {
        if ( (int) $node['id'] === (int) $current_node ) {
            $current_module = $module;
            break 2;
        }
    }
}

$map_heading = 'Карта обучения';
if ( $current_module ) {
    $module_title = trim( (string) $current_module['title'] );
    $module_title = preg_replace( '/^Модуль\s*\d+\s*[:–—-]\s*/u', '', $module_title );
    $map_heading  = sprintf( 'Модуль %d: %s', (int) $current_module['index'], $module_title );
}

$cat_image_url = '/image/funcat.png';
if ( is_user_logged_in() && function_exists( 'moneyneko_get_dynamic_cat_image' ) ) {
    $cat_image_url = moneyneko_get_dynamic_cat_image( $mood_points );
}


// Если пользователь впервые на карте — ставим кота сразу на текущий узел
if ( ! $last_cat_node && $current_node && $user_id ) {
    $last_cat_node = $current_node;
    update_user_meta( $user_id, '_mn_cat_node', $current_node );
}
?>

<div class="sketch-landing-wrapper mn-game-map-page">
    <aside class="mn-game-sidebar" aria-label="Игровая навигация">
        <div class="mn-game-brand">
            <div class="mn-game-cat-avatar">
                <img src="<?php echo esc_url( $cat_image_url ); ?>" alt="Грош, маскот MoneyNeko" class="cat-image" id="mn-tour-cat">
            </div>
            <span class="mn-game-brand-name">MoneyNeko</span>
        </div>

        <div class="mn-game-sidebar-stats">
            <?php if ( is_user_logged_in() ) : ?>
                <div class="mn-sidebar-stat streak-badge" id="mn-tour-streak">
                    <span class="mn-sidebar-stat-icon" aria-hidden="true">🔥</span>
                    <span><?php echo esc_html( $streak ); ?> <?php echo esc_html( $streak_label ); ?></span>
                </div>
                <div class="mn-sidebar-stat progress-xp-badge" id="mn-tour-points">
                    <span class="mn-sidebar-stat-icon mn-xp-icon" aria-hidden="true">XP</span>
                    <span><?php echo esc_html( $progress_points ); ?> XP</span>
                </div>
            <?php endif; ?>

            <div class="mood-progress-wrapper" id="mn-tour-mood" title="Настроение Гроша: <?php echo esc_attr( $mood_points ); ?>/100">
                <div class="mood-progress-heading">
                    <span>Настроение</span>
                    <span class="mood-progress-text"><?php echo esc_html( $mood_points ); ?>/100</span>
                </div>
                <div class="mood-progress-container" role="progressbar" aria-label="Настроение Гроша" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( $mood_points ); ?>">
                    <div class="mood-progress-fill" style="width: <?php echo esc_attr( $mood_points ); ?>%;"></div>
                </div>
            </div>
        </div>

        <nav class="mn-game-sidebar-nav" aria-label="Разделы">
            <a href="<?php echo esc_url( home_url( '/главная/game/' ) ); ?>" class="mn-sidebar-link is-active" aria-current="page">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 7 6-3 6 3 6-3v13l-6 3-6-3-6 3V7Z"/><path d="M9 4v13m6-10v13"/></svg>
                <span>Карта</span>
            </a>
            <a href="<?php echo esc_url( home_url( '/profile/' ) ); ?>" class="mn-sidebar-link sketch-btn-profile" id="mn-tour-profile">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                <span>Профиль</span>
            </a>
            <a href="<?php echo esc_url( home_url( '/доска-лидеров/' ) ); ?>" class="mn-sidebar-link sketch-btn-leaderboard" id="mn-tour-leaderboard">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 21h8m-4-4v4M7 4h10v5a5 5 0 0 1-10 0V4Z"/><path d="M7 6H4v2a4 4 0 0 0 4 4m9-6h3v2a4 4 0 0 1-4 4"/></svg>
                <span>Лидерборд</span>
            </a>
        </nav>
    </aside>

    <main class="mn-game-main">
        <header class="mn-game-main-header">
            <div class="mn-current-module-title"><?php echo esc_html( $map_heading ); ?></div>
            <button type="button" class="mn-map-current-button" id="mn-map-current-button"
                    aria-label="Прокрутить к текущему уроку" <?php echo $current_node ? '' : 'disabled'; ?>>
                🎯 К текущему уроку
            </button>
        </header>

    <!-- ИНТЕРАКТИВНАЯ КАРТА (ЗМЕЙКА ВМЕСТО СЕТКИ МОДУЛЕЙ) -->
    <div class="mn-map-container" 
         id="mn-interactive-map"
         data-current-node="<?php echo esc_attr( $current_node ); ?>"
         data-last-cat-node="<?php echo esc_attr( $last_cat_node ); ?>"
         data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
         data-nonce="<?php echo esc_attr( wp_create_nonce( 'mn_map_nonce' ) ); ?>">

        <!-- SVG-слой для отрисовки тропинки между кружками -->
        <svg class="mn-map-svg" id="mn-map-svg"></svg>

        <?php $map_doodle_icons = array( '🪙', '📈', '🧾', '🐷', '💳', '📊', '🧮', '🏦', '💰', '📉', '📒', '🏧' ); ?>
        <div class="mn-map-modules">
            <?php foreach ( $map_data['modules'] as $m_idx => $module ) : ?>
                <div class="mn-map-module zone-<?php echo esc_attr( $module['zone'] ); ?>" 
                     <?php echo ( $m_idx === 0 ) ? 'id="mn-tour-first-module"' : ''; ?>>

                    <div class="mn-module-banner">
                        <span class="mn-module-num">Модуль <?php echo esc_html( $module['index'] ); ?>:</span>
                        <span class="mn-module-name"><?php echo esc_html( $module['title'] ); ?></span>
                    </div>

                    <div class="mn-nodes-path">
                        <?php foreach ( $module['nodes'] as $node ) : 
                            if ( $node['status'] === 'completed' ) {
                                $icon = '✅';
                            } elseif ( $node['status'] === 'locked' ) {
                                $icon = ( $node['type'] === 'quiz' ) ? '🗄️' : '🔒';
                            } else {
                                $icons_map = array( 'lesson' => '🧶', 'trainer' => '🥣', 'quiz' => '🔓' );
                                $icon = isset( $icons_map[ $node['type'] ] ) ? $icons_map[ $node['type'] ] : '🧶';
                            }

                            $is_locked = ( $node['status'] === 'locked' );
                            $tag       = $is_locked ? 'div' : 'a';
                            $href_attr = $is_locked ? '' : 'href="' . esc_url( $node['url'] ) . '"';
                        ?>
                            <div class="mn-node-row" style="--i: <?php echo esc_attr( $node['global_index'] ); ?>;">
                                <<?php echo $tag; ?> <?php echo $href_attr; ?> 
                                    class="mn-node type-<?php echo esc_attr( $node['type'] ); ?> status-<?php echo esc_attr( $node['status'] ); ?>"
                                    data-node-id="<?php echo esc_attr( $node['id'] ); ?>"
                                    data-status="<?php echo esc_attr( $node['status'] ); ?>"
                                    title="<?php echo esc_attr( $node['title'] ); ?>">
                                    
                                    <span class="mn-node-icon"><?php echo $icon; ?></span>

                                    <?php if ( $node['id'] === $last_cat_node ) : ?>
                                        <div class="mn-map-cat-token" id="mn-map-cat-token">🐱</div>
                                    <?php endif; ?>

                                </<?php echo $tag; ?>>
                                <div class="mn-node-caption"><?php echo esc_html( $node['title'] ); ?></div>
                                <?php if ( ( (int) $node['global_index'] + 1 ) % 3 === 0 ) : ?>
                                    <?php
                                    $doodle_index = intdiv( (int) $node['global_index'] + 1, 3 ) - 1;
                                    $doodle_side  = ( $doodle_index % 2 === 0 ) ? 'left' : 'right';
                                    ?>
                                    <span class="mn-node-side-doodle side-<?php echo esc_attr( $doodle_side ); ?>" aria-hidden="true"><?php echo esc_html( $map_doodle_icons[ $doodle_index % count( $map_doodle_icons ) ] ); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    </main>
</div>

<?php if ( is_user_logged_in() ) : ?>
    <!-- Кнопка «?» — повторно показывает туториал (js/tutorial.js) -->
    <button type="button" id="mn-tutorial-help" class="mn-tutorial-help" title="Показать обучение" aria-label="Показать обучение">?</button>
<?php endif; ?>
