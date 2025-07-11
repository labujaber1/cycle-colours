<?php
if (
    isset($_POST['schedule_edit_palette'], $_POST['schedule_new_palette_interval'])
) {
    $new_palette_interval = sanitize_text_field($_POST['schedule_new_palette_interval']);
    update_option('cycle_colours_palettes_interval', $new_palette_interval);
    cycle_colours_schedule_event_palettes();
    update_option('cycle_colours_toggle', 'schedules');
    $message .= __('Palette schedule interval updated.', 'cycle-colours');
}

// Process div schedule edit if checkbox selected
if (
    isset($_POST['schedule_edit_div'], $_POST['schedule_new_div_interval']) &&
    is_array($_POST['schedule_edit_div']) &&
    is_array($_POST['schedule_new_div_interval'])
) {
    foreach ($_POST['schedule_edit_div'] as $key => $checked) {
        $div_class = $_POST['div_class'][$key] ?? '';
        $div_style = $_POST['div_style'][$key] ?? '';
        $new_interval = sanitize_text_field($_POST['schedule_new_div_interval'][$key] ?? '');
        if ($div_class && $div_style && $new_interval !== '') {
            $class_style_array = [
                'div_class' => $div_class,
                'style' => $div_style,
            ];
            $result = cycle_colours_change_div_interval($class_style_array, $new_interval);
            if ($result) {
                $message .= __('Interval updated for ' . esc_html($div_class) . ' / ' . esc_html($div_style) . ' with interval: ' . $new_interval . '.', 'cycle-colours');
            } else {
                $error_message .= __('Failed to update interval for ' . esc_html($div_class) . ' / ' . esc_html($div_style) . '.', 'cycle-colours');
            }
        }
    }
    update_option('cycle_colours_toggle', 'schedules');
    cycle_colours_rerun_scheduled_events();
}
