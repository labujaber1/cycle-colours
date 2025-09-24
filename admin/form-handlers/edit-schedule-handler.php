<?php
// Process palette schedule edit
if (isset($_POST['schedule_edit_palette'], $_POST['schedule_new_palette_interval'], $_POST['cycle_colours_edit_schedules_nonce']) && wp_verify_nonce(sanitize_key($_POST['cycle_colours_edit_schedules_nonce']), 'cycle_colours_edit_schedules')) {
    $new_palette_interval = sanitize_text_field(wp_unslash($_POST['schedule_new_palette_interval']));
    update_option('cycle_colours_palettes_interval', $new_palette_interval);
    cycle_colours_schedule_event_palettes();
    update_option('cycle_colours_toggle', 'schedules');
    $message .= __('Palette schedule interval updated.', 'cycle-colours') . PHP_EOL;
}

// Process div schedule edit if checkbox selected
// Note: checking that both $_POST['schedule_edit_div'] and $_POST['schedule_new_div_interval'] are arrays to prevent PHP warnings/notices
// as they should be arrays from the form and sanitizing data separately as used
if (isset($_POST['schedule_edit_div'], $_POST['schedule_new_div_interval'], $_POST['cycle_colours_edit_schedules_nonce']) && is_array($_POST['schedule_edit_div']) && is_array($_POST['schedule_new_div_interval'] && wp_verify_nonce(sanitize_key($_POST['cycle_colours_edit_schedules_nonce']), 'cycle_colours_edit_schedules'))) {
    foreach (wp_unslash($_POST['schedule_edit_div']) as $key => $checked) {
        $div_class = isset($_POST['div_class'][$key]) ? sanitize_text_field(wp_unslash($_POST['div_class'][$key])) : '';
        $div_style = isset($_POST['div_style'][$key]) ? sanitize_text_field(wp_unslash($_POST['div_style'][$key])) : '';
        $new_interval = isset($_POST['schedule_new_div_interval'][$key]) ? sanitize_text_field(wp_unslash($_POST['schedule_new_div_interval'][$key])) : '';
        if ($div_class && $div_style && $new_interval !== '') {
            $class_style_array = [
                'div_class' => $div_class,
                'style' => $div_style,
            ];
            $result = cycle_colours_change_div_interval($class_style_array, $new_interval);
            if ($result) {
                /* translators: %1$s: div class, %2$s: style, %3$s: interval */
                $message .= sprintf(__('Interval updated for %1$s / %2$s with interval: %3$s.', 'cycle-colours'), esc_html($div_class), esc_html($div_style), esc_html($new_interval)) . PHP_EOL;
            } else {
                /* translators: %1$s: div class, %2$s: style */
                $error_message .= sprintf(__('Failed to update interval for %1$s / %2$s.', 'cycle-colours'), esc_html($div_class), esc_html($div_style)) . PHP_EOL;
            }
        }
    }
    update_option('cycle_colours_toggle', 'schedules');
    cycle_colours_rerun_scheduled_events();
}
