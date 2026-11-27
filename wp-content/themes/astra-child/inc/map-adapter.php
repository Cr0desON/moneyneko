<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * 1. Получаем ID основного курса MoneyNeko
 */
function mn_get_main_course_id() {
    $courses = get_posts( array(
        'post_type'      => 'courses',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'orderby'        => 'ID',
        'order'          => 'ASC',
        'fields'         => 'ids',
    ) );
    return ! empty( $courses ) ? (int) $courses[0] : 0;
}

/**
 * 2. Определяем тип узла: lesson (клубок), trainer (миска), quiz (сейф)
 */
function mn_detect_node_type( $post ) {
    // Приоритет 1: Явное мета-поле (если задано)
    $explicit = get_post_meta( $post->ID, '_mn_node_type', true );
    if ( in_array( $explicit, array( 'lesson', 'trainer', 'quiz' ), true ) ) {
        return $explicit;
    }
    // Приоритет 2: Тип записи
    if ( $post->post_type === 'tutor_quiz' ) {
        return 'quiz';
    }
    if ( $post->post_type === 'mn_trainer' ) {
        return 'trainer';
    }
    // Приоритет 3: Проверка на шорткод тренажёра внутри обычного урока
    if ( has_shortcode( $post->post_content, 'mn_trainer' ) || strpos( $post->post_content, '[mn_trainer' ) !== false ) {
        return 'trainer';
    }
    return 'lesson';
}

/**
 * 3. Проверяем, пройден ли узел пользователем
 */
function mn_is_node_completed( $post, $user_id ) {
    if ( ! $user_id || ! function_exists( 'tutor_utils' ) ) {
        return false;
    }
    // Для квизов (сейфов) проверяем именно УСПЕШНУЮ сдачу, а не просто попытку
    if ( $post->post_type === 'tutor_quiz' ) {
        if ( class_exists( '\Tutor\Models\QuizModel' ) && method_exists( '\Tutor\Models\QuizModel', 'is_quiz_passed' ) ) {
            return \Tutor\Models\QuizModel::is_quiz_passed( $post->ID, $user_id );
        }
        return false;
    }
    // Для уроков и тренажёров
    return (bool) tutor_utils()->is_completed_lesson( $post->ID, $user_id );
}

/**
 * 4. Главный Адаптер: собирает дерево модулей и узлов для карты
 */
function mn_get_map_nodes( $user_id = 0, $course_id = 0 ) {
    if ( ! $user_id ) { $user_id = get_current_user_id(); }
    if ( ! $course_id ) { $course_id = mn_get_main_course_id(); }

    if ( ! $course_id || ! function_exists( 'tutor_utils' ) ) {
        return array( 'modules' => array(), 'current_node_id' => 0 );
    }

    $topics_query = tutor_utils()->get_topics( $course_id );
    $topics       = ( $topics_query && ! empty( $topics_query->posts ) ) ? $topics_query->posts : array();

    $modules         = array();
    $found_current   = false;
    $current_node_id = 0;
    $global_index    = 0;

    foreach ( $topics as $m_idx => $topic ) {
        $contents_query = tutor_utils()->get_course_contents_by_topic( $topic->ID, -1 );
        $posts          = ( $contents_query && ! empty( $contents_query->posts ) ) ? $contents_query->posts : array();

        $nodes = array();
        foreach ( $posts as $post ) {
            $type      = mn_detect_node_type( $post );
            $completed = mn_is_node_completed( $post, $user_id );

            if ( $completed ) {
                $status = 'completed';
            } elseif ( ! $found_current ) {
                $status          = 'current';
                $found_current   = true;
                $current_node_id = (int) $post->ID;
            } else {
                $status = 'locked';
            }

            $nodes[] = array(
                'id'           => (int) $post->ID,
                'title'        => $post->post_title,
                'type'         => $type,   // lesson | trainer | quiz
                'status'       => $status, // completed | current | locked
                'url'          => get_permalink( $post->ID ),
                'global_index' => $global_index++,
            );
        }

        // Привязка фоновой темы (пока 3 базовых зоны: dorm, flat, office)
        $zone = ( $m_idx < 2 ) ? 'dorm' : ( ( $m_idx < 4 ) ? 'flat' : 'office' );

        $modules[] = array(
            'id'    => (int) $topic->ID,
            'index' => $m_idx + 1,
            'title' => $topic->post_title,
            'zone'  => $zone,
            'nodes' => $nodes,
        );
    }

    return array(
        'modules'         => $modules,
        'current_node_id' => $current_node_id,
    );
}

/**
 * 5. Серверная блокировка закрытых узлов (защита от прямых ссылок)
 */
add_action( 'template_redirect', 'mn_block_locked_nodes_access' );
function mn_block_locked_nodes_access() {
    if ( ! is_singular( array( 'lesson', 'tutor_quiz', 'mn_trainer' ) ) ) {
        return;
    }
    if ( current_user_can( 'manage_options' ) ) {
        return; // Админов пускаем везде для проверки контента
    }

    $queried_id = get_queried_object_id();
    $map_data   = mn_get_map_nodes( get_current_user_id() );

    foreach ( $map_data['modules'] as $mod ) {
        foreach ( $mod['nodes'] as $node ) {
            if ( $node['id'] === $queried_id && $node['status'] === 'locked' ) {
                // Если узел закрыт — редиректим обратно на страницу игры (карты)
                wp_safe_redirect( home_url( '/game/' ) );
                exit;
            }
        }
    }
}

/**
 * 6. AJAX: Сохранение новой позиции кота после проигрывания анимации прыжка
 */
add_action( 'wp_ajax_mn_update_cat_node', 'mn_ajax_update_cat_node' );
function mn_ajax_update_cat_node() {
    check_ajax_referer( 'mn_map_nonce', 'nonce' );
    $user_id = get_current_user_id();
    $node_id = isset( $_POST['node_id'] ) ? absint( $_POST['node_id'] ) : 0;

    if ( $user_id && $node_id ) {
        update_user_meta( $user_id, '_mn_cat_node', $node_id );
        wp_send_json_success();
    }
    wp_send_json_error();
}