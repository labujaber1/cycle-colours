<?php

if (isset($_POST['delete_class_btn']) && !empty($_POST['delete_class_select'])) {
    $div_class = sanitize_text_field($_POST['delete_class_select']);
    $ans_class = cycle_colours_delete_div_class($div_class);
    if ($ans_class) {
        $message .= __('Div class ' . esc_html($div_class) . ' and all styles has been deleted.' . PHP_EOL);
    } else {
        $error_message .= __('Failed to delete div class ' . esc_html($div_class) . ' due to an error.' . PHP_EOL);
    }
    cycle_colours_rerun_scheduled_events();
}

// Process style deletion
if (isset($_POST['delete_class_style_btn']) || isset($_POST['stop_schedule_event_btn']) && !empty($_POST['delete_class_style_select'])) {
    $parts = explode('|', $_POST['delete_class_style_select'], 2);
    if (count($parts) === 2) {
        list($div_class, $style) = $parts;
        $class_style_array = [
            'div_class' => $div_class,
            'style' => $style,
        ];

        if (isset($_POST['delete_class_style_btn'])) {
            $ans_style = cycle_colours_delete_div_class_style($class_style_array);
            if ($ans_style) {
                $message .= __('The style ' . esc_html($style) . ' for the class ' . esc_html($div_class) . ' has been deleted.
                    If this is the last style for the class, the class will also be deleted.' . PHP_EOL);
            } else {
                $error_message .= __('Failed to delete div class ' . esc_html($div_class) . ' and style ' . esc_html($style) . ' due to an error.' . PHP_EOL);
            }
        }
        if (isset($_POST['stop_schedule_event_btn'])) {
            $ans_style = cycle_colours_change_div_interval($class_style_array, 0);
            if ($ans_style) {
                $message .= __('The interval for the style ' . esc_html($style) . ' for the class ' . esc_html($div_class) . ' has been stopped.' . PHP_EOL);
            } else {
                $error_message .= __('Failed to stop interval for div class ' . esc_html($div_class) . ' and style ' . esc_html($style) . ' due to an error. Please try again.' . PHP_EOL);
            }
            cycle_colours_rerun_scheduled_events(); // Rerun the scheduled events to update the divs
        }
    } else {
        $error_message .= __('Invalid class and style selection.' . PHP_EOL);
    }
}
