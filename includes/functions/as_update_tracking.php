<?php

/**
 * Add the action to schedule the tracking task
 */
add_action('init', function () {
    if (!as_has_scheduled_action('sbwc_order_confirmation_upsells_riode_track_data')) {
        as_schedule_recurring_action(time(), 1800, 'sbwc_order_confirmation_upsells_riode_track_data');
    }
});

/**
 * Define the callback function to track data
 */
add_action('sbwc_order_confirmation_upsells_riode_track_data', function () {
    sbwc_order_confirmation_upsells_riode_track_data_callback();
});
