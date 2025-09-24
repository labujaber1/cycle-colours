<?php
// Process class deletion
if (isset($_POST['delete_class_btn'], $_POST['cycle_colours_div_remove_class_task_nonce']) && !empty($_POST['delete_class_select']) && wp_verify_nonce(sanitize_key($_POST['cycle_colours_div_remove_class_task_nonce']), 'cycle_colours_div_remove_class_task')) {
    $div_class = sanitize_text_field(wp_unslash($_POST['delete_class_select']));
    $ans_class = cycle_colours_delete_div_class($div_class);
    if ($ans_class) {
        /* translators: %s: div class */
        $message .= sprintf(__('Div class %s and all styles has been deleted.', 'cycle-colours'), esc_html($div_class)) . PHP_EOL;
    } else {
        /* translators: %s: div class */
        $error_message .= sprintf(__('Failed to delete div class %s due to an error.', 'cycle-colours'), esc_html($div_class)) . PHP_EOL;
    }
    cycle_colours_rerun_scheduled_events();
}

// Process style deletion
if (isset($_POST['delete_class_style_btn'], $_POST['cycle_colours_delete_class_style_nonce']) || isset($_POST['stop_schedule_event_btn'], $_POST['cycle_colours_delete_class_style_nonce']) && !empty($_POST['delete_class_style_select'])  && wp_verify_nonce(sanitize_key($_POST['cycle_colours_delete_class_style_nonce']), 'cycle_colours_delete_class_style')) {
    $parts = explode('|', sanitize_text_field(wp_unslash($_POST['delete_class_style_select'])), 2);
    if (count($parts) === 2) {
        list($div_class, $style) = $parts;
        $class_style_array = [
            'div_class' => $div_class,
            'style' => $style,
        ];

        if (isset($_POST['delete_class_style_btn'], $_POST['cycle_colours_delete_class_style_nonce']) && wp_verify_nonce(sanitize_key($_POST['cycle_colours_delete_class_style_nonce']), 'cycle_colours_delete_class_style')) {
            $ans_style = cycle_colours_delete_div_class_style($class_style_array);
            if ($ans_style) {
                /* translators: %1$s: style, %2$s: div class */
                $message .= sprintf(__('The style %1$s for the class %2$s has been deleted.
                    If this is the last style for the class, the class will also be deleted.', 'cycle-colours'), esc_html($style), esc_html($div_class)) . PHP_EOL;
            } else {
                /* translators: %1$s: style, %2$s: div class */
                $error_message .= sprintf(__('Failed to delete div class %2$s and style %1$s due to an error.', 'cycle-colours'), esc_html($style), esc_html($div_class)) . PHP_EOL;
            }
        }
        if (isset($_POST['stop_schedule_event_btn'], $_POST['cycle_colours_delete_class_style_nonce']) && wp_verify_nonce(sanitize_key($_POST['cycle_colours_delete_class_style_nonce']), 'cycle_colours_delete_class_style')) {
            $ans_style = cycle_colours_change_div_interval($class_style_array, 0);
            if ($ans_style) {
                /* translators: %1$s: style, %2$s: div class */
                $message .= sprintf(__('The interval for the style %1$s for the class %2$s has been stopped.', 'cycle-colours'), esc_html($style), esc_html($div_class)) . PHP_EOL;
            } else {
                /* translators: %1$s: style, %2$s: div class */
                $error_message .= sprintf(__('Failed to stop interval for div class %2$s and style %1$s due to an error. Please try again.', 'cycle-colours'), esc_html($style), esc_html($div_class)) . PHP_EOL;
            }
        }
        cycle_colours_rerun_scheduled_events(); // Rerun the scheduled events to update the divs
    } else {
        $error_message .= __('Invalid class and style selection.', 'cycle-colours') . PHP_EOL;
    }
}
