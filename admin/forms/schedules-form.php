<?php
// Display edit schedule settings
echo '<div id="schedules-settings" style="display:' . ($toggle === 'schedules' ? 'block' : 'none') . ';">';

echo '<h2>Edit Schedules</h2>
    <p>To keep things simple the schedules are controlled by the intervals.
    Changing from daily to weekly will remove the existing schedule event and create a new one.</p>';

echo '<form method="post" action="" style="display:inline;">';
wp_nonce_field('cycle_colours_edit_schedules', 'cycle_colours_edit_schedules_nonce'); // Nonce field for security
echo '<h3>Palettes</h3>';

include_once  CYCLE_COLOURS_PLUGIN_PATH . 'admin/templates/palettes-schedule-table.php';

echo '<div class="cycle-colours-buttons-wrap">';
echo '<input type="submit" name="schedule_new_palette_interval_btn" value="Save" class="button button-secondary " style="background-color: green; color: white;margin: 1rem;">';
echo '</div>'; // End of buttons
echo '</form>';

echo '<br>';

echo '<form method="post" action="" style="display:inline;">
    <h3>Divs</h3>';
wp_nonce_field('cycle_colours_edit_schedules', 'cycle_colours_edit_schedules_nonce'); // Nonce field for security

include_once CYCLE_COLOURS_PLUGIN_PATH . 'admin/templates/divs-schedule-table.php';

echo '<div class="cycle-colours-buttons-wrap">';
echo '<input type="submit" name="schedule_new_div_interval_btn" value="Save" class="button button-secondary " style="background-color: green; color: white;margin: 1rem;">';
echo '</div>'; // End of buttons
echo '</div>'; // End schedules settings
echo '</form>';
