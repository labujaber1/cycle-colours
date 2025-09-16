<?php


echo '<table class="wp-list-table widefat fixed striped id="palettes-schedule-table">
    <tr>
    <th>Select to change</th>
    <th>Scheduled Task</th>
    <th>Class/Style</th>
    <th>Interval</th>
    <th>New</th>
    <th>Scheduled Time</th>
    </tr>
    <tr>';
$palette_interval = get_option('cycle_colours_palettes_interval', 0);
$palette_timestamp = wp_next_scheduled('cycle_colours_palettes_task');

if (isset($palette_timestamp)) {


    echo '<td><input type="checkbox" name="schedule_edit_palette" value="cycle_colours_palettes_task"></td>
    <td>' . esc_html(empty($palette_timestamp) ? '' : 'cycle_colours_palettes_task') . '</td>
    <td>' . esc_html(empty($palette_timestamp) ? '' : 'Palettes') . '</td>
    <td>' . esc_html(empty($palette_timestamp) ? '' : $palette_interval) . '</td>
    <td>
        <select name="schedule_new_palette_interval" style="font-size:0.75rem;">';
    foreach (cycle_colours_display_interval_options() as $option_value => $label) {
        $selected = ($palette_interval === $option_value) ? 'selected' : '';
        echo '<option value="' . esc_attr($option_value) . '" ' . esc_attr($selected) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>
    </td>
    <td>' . esc_html(empty($palette_timestamp) ? '' : gmdate('H:i:s d-m-Y ', $palette_timestamp)) . '</td>';
}
echo '</tr>
    </table>';
