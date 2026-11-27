<?php
/**
 * Универсальный шаблон одиночной записи тренажёра (mn_trainer)
 * Интеграция с оболочкой Tutor LMS
 */
if (!defined('ABSPATH')) exit;

if (function_exists('tutor_lesson_sidebar')) {
    get_header('course');
} else {
    get_header();
}

$course_content_id = get_the_ID();
$course_id = 0;
if (function_exists('tutor_utils')) {
    $course_id = 'mn_trainer' === get_post_type($course_content_id)
        ? tutor_utils()->get_course_id_by('lesson', $course_content_id)
        : tutor_utils()->get_course_id_by_subcontent($course_content_id);
}

$is_enrolled = function_exists('tutor_utils') ? tutor_utils()->is_enrolled($course_id) : true;

$game_url = home_url('/главная/game/');
$is_completed = function_exists('tutor_utils')
    ? (bool) tutor_utils()->is_completed_lesson($course_content_id, get_current_user_id())
    : false;
?>

<div class="tutor-course-single-wrap mn-trainer-page <?php echo $is_completed ? 'mn-trainer-completed' : 'mn-trainer-incomplete'; ?>">
    <a class="mn-trainer-exit-link tutor-iconic-btn" href="<?php echo esc_url($game_url); ?>" aria-label="Вернуться на карту" title="На карту">
        <span class="tutor-icon-times" aria-hidden="true"></span>
    </a>
    <div class="tutor-course-content-right" style="width:100%; padding:32px 40px;">

        <?php
        // Выводим контент записи. Если внутри вставлен шорткод [mn_trainer_m1], он автоматически отработает
        echo do_shortcode(get_the_content());
        ?>

        <?php if ($is_enrolled && !$is_completed && function_exists('tutor')) : ?>
            <form method="post" class="mn-complete-content-form" style="display:none;">
                <?php wp_nonce_field(tutor()->nonce_action, tutor()->nonce, false); ?>
                <input type="hidden" name="lesson_id" value="<?php echo esc_attr($course_content_id); ?>">
                <input type="hidden" name="tutor_action" value="tutor_complete_lesson">
                <button type="submit" class="tutor-topbar-mark-btn" name="complete_lesson_btn" value="complete_lesson">Завершить</button>
            </form>
        <?php endif; ?>

        <div class="tutor-course-topic-single-footer tutor-px-32 tutor-py-12 tutor-mt-auto" style="margin-top:32px; display:flex; justify-content:flex-end; align-items:center;">

            <?php if ($is_completed) : ?>
                <div class="tutor-single-course-content-next">
                    <a class="tutor-btn tutor-btn-primary tutor-btn-sm"
                       id="mn-tutor-next-btn"
                       href="<?php echo esc_url($game_url); ?>"
                       aria-label="Далее">
                        <span class="tutor-mr-8"><?php esc_html_e('Далее', 'tutor'); ?></span>
                        <span class="tutor-icon-next" aria-hidden="true"></span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php
if (function_exists('tutor_lesson_sidebar')) {
    get_footer('course');
} else {
    get_footer();
}
?>
