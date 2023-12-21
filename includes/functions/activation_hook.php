<?php

/**
 *  Create the custom table if it doesn't exist
 *
 * @return void
 */
function sbwc_order_confirmation_upsells_riode_create_tracking_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'sbwc_conf_upsells_tracking';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        product_id INT(11) NOT NULL,
        click_count INT(11) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $result = dbDelta($sql);

    // log
    $log = new WC_Logger();
    $log->add('sbwc_order_confirmation_upsells_riode', 'Custom table created: ' . print_r($result, true));
}
register_activation_hook(__FILE__, 'sbwc_order_confirmation_upsells_riode_create_tracking_table');
