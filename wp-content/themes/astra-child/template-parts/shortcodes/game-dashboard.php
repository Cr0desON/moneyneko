<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php
// Собираем данные интерактивной карты через адаптер
$user_id          = get_current_user_id();
$map_data         = function_exists( 'mn_get_map_nodes' ) ? mn_get_map_nodes( $user_id ) : array( 'modules' => array(), 'current_node_id' => 0 );
$current_node     = $map_data['current_node_id'];
$last_cat_node    = (int) get_user_meta( $user_id, '_mn_cat_node', true );


// Если пользователь впервые на карте — ставим кота сразу на текущий узел
if ( ! $last_cat_node && $current_node && $user_id ) {
    $last_cat_node = $current_node;
    update_user_meta( $user_id, '_mn_cat_node', $current_node );
}
?>

<div class="sketch-landing-wrapper mn-game-map-page">
    <div class="top-buttons-container">
        <a href="<?php echo esc_url( home_url( '/profile/' ) ); ?>" class="sketch-btn-profile">Профиль</a>
        <a href="<?php echo esc_url( home_url( '/доска-лидеров/' ) ); ?>" class="sketch-btn-leaderboard" id="mn-tour-leaderboard">Доска лидеров</a>
    </div>

    <div class="sketch-bg-deco deco-left">📈 💳<br>📊</div>
    <div class="sketch-bg-deco deco-right">🍕 🧮<br>🪙 🐖</div>
    
    <div class="sketch-hero-section">
        <div class="cat-container">
            <!-- ПОЛОСА НАСТРОЕНИЯ -->
            <div class="mood-progress-wrapper" id="mn-tour-mood" title="Настроение кота: <?php echo $mood_points; ?>/100">
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
            <img src="<?php echo esc_url( $cat_image_url ); ?>" alt="Манеки-нэко" class="cat-image" id="mn-tour-cat">

            <?php if ( is_user_logged_in() ) : ?>
                <div class="right-bottom-badges">
                    <div class="streak-badge" id="mn-tour-streak">
                        🔥 <?php echo $streak; ?> <span class="badge-label">дней</span>
                    </div>
                    <div class="progress-xp-badge" id="mn-tour-points">
                        <?php echo $progress_points; ?> <span class="badge-label">XP</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

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

    <button type="button" class="mn-map-current-button" id="mn-map-current-button"
            aria-label="Прокрутить к текущему уроку" <?php echo $current_node ? '' : 'disabled'; ?>>
        🎯 К текущему уроку
    </button>
</div>

<?php if ( is_user_logged_in() ) : ?>
    <!-- Кнопка «?» — повторно показывает туториал (js/tutorial.js) -->
    <button type="button" id="mn-tutorial-help" class="mn-tutorial-help" title="Показать обучение" aria-label="Показать обучение">?</button>
<?php endif; ?>
