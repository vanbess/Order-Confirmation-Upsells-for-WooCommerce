<?php

// Add the action to schedule the tracking task
add_action('init', 'sbwc_order_confirmation_upsells_riode_schedule_tracking');

function sbwc_order_confirmation_upsells_riode_schedule_tracking()
{
    if (!wp_next_scheduled('sbwc_order_confirmation_upsells_riode_track_data')) {
        wp_schedule_event(time(), 'every_30_minutes', 'sbwc_order_confirmation_upsells_riode_track_data');
    }
}

// Define the callback function to track data
add_action('sbwc_order_confirmation_upsells_riode_track_data', 'sbwc_order_confirmation_upsells_riode_track_data_as_callback');

function sbwc_order_confirmation_upsells_riode_track_data_as_callback()
{
    // Get the transient data
    $clicks = get_transient('sbwc_order_confirmation_upsells_riode_clicks');

    // Save the tracking data to the database
    if ($clicks) {
        foreach ($clicks as $product_id => $click_count) {
            // Save the tracking data to the database using your preferred method
            // Example: $wpdb->insert('tracking_table', array('product_id' => $product_id, 'click_count' => $click_count));
        }
    }

    // Delete the associated transients
    delete_transient('sbwc_order_confirmation_upsells_riode_clicks');
}

// Add the custom cron schedule for every 30 minutes
add_filter('cron_schedules', 'sbwc_order_confirmation_upsells_riode_custom_cron_schedule');

function sbwc_order_confirmation_upsells_riode_custom_cron_schedule($schedules)
{
    $schedules['every_30_minutes'] = array(
        'interval' => 1800, // 30 minutes in seconds
        'display' => __('Every 30 Minutes')
    );
    return $schedules;
}
