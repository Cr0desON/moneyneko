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
$course_id = function_exists('tutor_utils') ? tutor_utils()->get_course_id_by_subcontent($course_content_id) : 0;
$content_id = function_exists('tutor_utils') ? tutor_utils()->get_post_id($course_content_id) : $course_content_id;

$contents = function_exists('tutor_utils') ? tutor_utils()->get_course_prev_next_contents_by_id($content_id) : (object)['previous_id' => 0, 'next_id' => 0];

$previous_id = $contents->previous_id ?? 0;
$next_id     = $contents->next_id ?? 0;
$is_enrolled = function_exists('tutor_utils') ? tutor_utils()->is_enrolled($course_id) : true;

$prev_link = $previous_id && $is_enrolled ? get_the_permalink($previous_id) : '#';
$next_link = $next_id && $is_enrolled ? get_the_permalink($next_id) : '#';
?>

<div class="tutor-course-single-wrap">
    <div class="tutor-course-content-right" style="width:100%; padding:32px 40px;">

        <?php
        // Выводим контент записи. Если внутри вставлен шорткод [mn_trainer_m1], он автоматически отработает
        echo do_shortcode(get_the_content());
        ?>

        <div class="tutor-course-topic-single-footer tutor-px-32 tutor-py-12 tutor-mt-auto" style="margin-top:32px; display:flex; justify-content:space-between; align-items:center;">
            <div class="tutor-single-course-content-prev">
                <a class="tutor-btn tutor-btn-secondary tutor-btn-sm"
                   href="<?php echo esc_url($prev_link); ?>"
                   <?php echo !$previous_id ? 'disabled="disabled"' : ''; ?>>
                    <span class="tutor-icon-previous" aria-hidden="true"></span>
                    <span class="tutor-ml-8"><?php esc_html_e('Назад', 'tutor'); ?></span>
                </a>
            </div>

            <div class="tutor-single-course-content-next">
                <a class="tutor-btn tutor-btn-primary tutor-btn-sm"
                   id="mn-tutor-next-btn"
                   href="#"
                   data-next="<?php echo esc_url($next_link); ?>"
                   <?php echo !$next_id ? 'disabled="disabled"' : ''; ?>
                   style="<?php echo !$next_id ? 'opacity:0.5; cursor:not-allowed;' : ''; ?>">
                    <span class="tutor-mr-8"><?php esc_html_e('Дальше', 'tutor'); ?></span>
                    <span class="tutor-icon-next" aria-hidden="true"></span>
                </a>
            </div>
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