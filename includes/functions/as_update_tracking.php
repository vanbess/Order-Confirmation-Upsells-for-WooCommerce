<?php

/**
 * Add the action to schedule the tracking task
 */
add_action('init', function () {
    if (!wp_next_scheduled('sbwc_order_confirmation_upsells_riode_track_data')) {
        wp_schedule_event(time(), 'every_30_minutes', 'sbwc_order_confirmation_upsells_riode_track_data');
    }
});

/**
 * Define the callback function to track data
 */
add_action('sbwc_order_confirmation_upsells_riode_track_data', function () {
    sbwc_order_confirmation_upsells_riode_track_data_callback();
});

/**
 * Add the custom cron schedule for every 30 minutes
 */
add_filter('cron_schedules', function ($schedules) {
    $schedules['every_30_minutes'] = array(
        'interval' => 1800, // 30 minutes in seconds
        'display' => __('Every 30 Minutes')
    );
    return $schedules;
});
