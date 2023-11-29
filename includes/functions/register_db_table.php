<?php

/**
 * Register custom db table to store tracking data
 */

global $wpdb;
$table_name      = $wpdb->prefix . 'sbwc_conf_upsells_tracking';
$charset_collate = $wpdb->get_charset_collate();

// Check if table exists
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

// Check if code has already been executed
$code_executed = get_option('sbwc_upsells_table_created');

if (!$table_exists && !$code_executed) {
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
         id INT(11) NOT NULL AUTO_INCREMENT,
         product_id INT(11) NOT NULL,
         click_count INT(11) NOT NULL,
         impressions INT(11) NOT NULL,
         active BOOLEAN NOT NULL,
         sales_qty INT(11) NOT NULL,
         conversion_rate FLOAT NOT NULL,
         revenue FLOAT NOT NULL,
         PRIMARY KEY (id)
     ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $result = dbDelta($sql, true);

    // Log table creation result
    $log = new WC_Logger();
    $log->log('notice', '[SBWC Order Confirmation Page Upsells] Custom DB table creation result: ' . print_r($result, true));

    // Set the flag to indicate that the code has been executed
    update_option('sbwc_upsells_table_created', true);
}
