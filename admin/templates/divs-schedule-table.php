<?php

echo '<table class="wp-list-table widefat fixed striped id="divs-schedule-table"><tr>
    <th>Select to change</th>
    <th>Scheduled Task</th>
    <th>Class/Style</th>
    <th class= "manager-column">Interval</th>
    <th>New</th>
    <th>Scheduled Time</th>
    </tr>';
$unsorted_div_array = get_option('cycle_colours_div_array', []);
$interval_order = array_keys(cycle_colours_display_interval_options());
// display in a styled wp list table, ordered by the interval
foreach ($interval_order as $dinterval) {
    foreach ($unsorted_div_array as $class => $styles) {
        foreach ($styles as $style => $data) {
            $div_interval = $data['interval'] ?? '0';
            if ((string)$div_interval !== (string)$dinterval) continue;
            $hook = 'cycle_colours_div_task_' . $div_interval;
            $div_timestamp = wp_next_scheduled($hook);
            $key = $class . '|' . $style;
            echo '<tr>
                    <td><input type="checkbox" name="schedule_edit_div[' . esc_attr($key) . ']" value="1"></td>
                    <td>' . esc_html($hook) . '</td>
                    <td>' . esc_html($class . '/' . $style) . '</td>
                    <input type="hidden" name="div_class[' . esc_attr($key) . ']" value="' . esc_attr($class) . '">
                    <input type="hidden" name="div_style[' . esc_attr($key) . ']" value="' . esc_attr($style) . '">
                    <td>' . esc_html($div_interval) . '</td>
                    <td><select name="schedule_new_div_interval[' . esc_attr($key) . ']" style="font-size:0.75rem;">';
            foreach (cycle_colours_display_interval_options() as $option_value => $label) {
                $selected = ($div_interval === $option_value) ? 'selected' : '';
                echo '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
            }
            echo '</select></td>
                <td>' . esc_html(empty($div_timestamp) ? 'Not Scheduled.' : date('H:i:s d-m-Y ', $div_timestamp)) . '</td>
                </tr>';
        }
    }
}
echo '</table>';
