<?php
// Display edit schedule settings
echo '<div id="schedules-settings" style="display:' . ($toggle === 'schedules' ? 'block' : 'none') . ';">';

echo '<h1>Edit Schedules</h1>
    <p>To keep things simple to use the schedules are controlled through the intervals..
    Changing from daily to weekly will remove the existing schedule event and create a new one.</p>';

echo '<form method="post" action="" style="display:inline;">';
wp_nonce_field('cycle_colours_palettes_task', 'cycle_colours_task_nonce'); // Nonce field for security
$palette_interval = get_option('cycle_colours_palettes_interval', 0);
$palette_timestamp = wp_next_scheduled('cycle_colours_palettes_task');
echo '<h2>Palettes</h2>';

include_once  CYCLE_COLOURS_PLUGIN_PATH . 'admin/templates/palettes-schedule-table.php';

echo '<div class="cycle-colours-buttons-wrap">';
echo '<input type="submit" name="schedule_new_palette_interval_btn" value="Save" class="button button-secondary " style="background-color: green; color: white;margin: 1rem;">';
echo '</div>'; // End of buttons
echo '</form>';

echo '<br>';

echo '<form method="post" action="" style="display:inline;">
    <h2>Divs</h2>';

include_once CYCLE_COLOURS_PLUGIN_PATH . 'admin/templates/divs-schedule-table.php';

echo '<div class="cycle-colours-buttons-wrap">';
echo '<input type="submit" name="schedule_new_div_interval_btn" value="Save" class="button button-secondary " style="background-color: green; color: white;margin: 1rem;">';
echo '</div>'; // End of buttons
echo '</div>'; // End schedules settings
echo '</form>';
