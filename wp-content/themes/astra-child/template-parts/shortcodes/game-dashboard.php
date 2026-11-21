<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php
// Собираем данные интерактивной карты через адаптер
$user_id          = get_current_user_id();
$map_data         = function_exists( 'mn_get_map_nodes' ) ? mn_get_map_nodes( $user_id ) : array( 'modules' => array(), 'current_node_id' => 0 );
$current_node     = $map_data['current_node_id'];
$last_cat_node    = (int) get_user_meta( $user_id, '_mn_cat_node', true );
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

// Если пользователь впервые на карте — ставим кота сразу на текущий узел
if ( ! $last_cat_node && $current_node && $user_id ) {
    $last_cat_node = $current_node;
    update_user_meta( $user_id, '_mn_cat_node', $current_node );
}
?>

<main class="mn-game-main">
        <header class="mn-game-main-header">
            <div class="mn-current-module-title"><?php echo esc_html( $map_heading ); ?></div>
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
                <?php
                $module_heading = trim( (string) $module['title'] );
                $module_heading = preg_replace( '/^Модуль\s*\d+\s*[:–—-]\s*/u', '', $module_heading );
                $module_heading = sprintf( 'Модуль %d: %s', (int) $module['index'], $module_heading );
                ?>
                <div class="mn-map-module zone-<?php echo esc_attr( $module['zone'] ); ?>"
                     data-module-title="<?php echo esc_attr( $module_heading ); ?>"
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
        <span class="mn-map-current-button-label">К текущему уроку</span>
    </button>

</main>
