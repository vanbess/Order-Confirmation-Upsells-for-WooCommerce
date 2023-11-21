<?php

/**
 * Register custom table and update table data function
 */
$table_name = $wpdb->prefix . 'conf_upsells_tracking';

// Create the custom table if it doesn't exist
function sbwc_order_confirmation_upsells_riode_create_tracking_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'conf_upsells_tracking';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        product_id INT(11) NOT NULL,
        click_count INT(11) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'sbwc_order_confirmation_upsells_riode_create_tracking_table' );

// Save the tracking data to the custom table
function sbwc_order_confirmation_upsells_riode_save_tracking_data($product_id, $click_count, $impressions, $active, $sales_qty, $conversion_rate, $revenue) {

    global $wpdb;
    $table_name = $wpdb->prefix . 'conf_upsells_tracking';

    // check if product_id exists
    $product_id_exists = $wpdb->get_var( $wpdb->prepare(
        "SELECT product_id FROM $table_name WHERE product_id = %d",
        $product_id
    ) );

    // update if product_id exists
    if ($product_id_exists) {
        $wpdb->update(
            $table_name,
            array(
                'click_count'     => $click_count,
                'impressions'     => $impressions,
                'active'          => $active,
                'sales_qty'       => $sales_qty,
                'conversion_rate' => $conversion_rate,
                'revenue'         => $revenue
            ),
            array(
                'product_id' => $product_id
            ),
            array(
                '%d',
                '%d'
            ),
            array(
                '%d'
            )
        );
        return;
    }

    // insert if product_id does not exist
    $wpdb->insert(
        $table_name,
        array(
            'product_id'      => $product_id,
            'click_count'     => $click_count,
            'impressions'     => $impressions,
            'active'          => $active,
            'sales_qty'       => $sales_qty,
            'conversion_rate' => $conversion_rate,
            'revenue'         => $revenue
        ),
        array(
            '%d',
            '%d'
        )
    );
}

// Update the callback function to use the custom table
function sbwc_order_confirmation_upsells_riode_track_data_callback($product_id) {

    // get clicks
    $clicks_trans = get_transient('sbwc_order_confirmation_upsells_riode_clicks');
    $clicks       = $clicks_trans[$product_id];

    // get impressions
    $impressions_trans = get_transient('sbwc_order_confirmation_upsells_riode_impressions');
    $impressions       = $impressions_trans[$product_id];

    // check if product is active (i.e. in defined upsell list)
    $upsell_ids = get_option('sbwc_order_confirmation_upsells_riode_product_ids');
    $upsell_ids = explode(',', $upsell_ids);
    $active     = in_array($product_id, $upsell_ids) ? 1 : 0;

    // get sales qty from transient
    $sales_qty_trans = get_transient('sbwc_order_confirmation_upsells_riode_sales_qty');
    $sales_qty       = $sales_qty_trans[$product_id];

    // get conversion rate from transient
    $conversion_rate_trans = get_transient('sbwc_order_confirmation_upsells_riode_conversion_rate');
    $conversion_rate       = $conversion_rate_trans[$product_id];

    // get revenue from transient
    $revenue_trans = get_transient('sbwc_order_confirmation_upsells_riode_revenue');
    $revenue       = $revenue_trans[$product_id];

    // Save the tracking data to the custom table
    if ($clicks) {
        foreach ($clicks as $product_id => $click_count) {
            sbwc_order_confirmation_upsells_riode_save_tracking_data($product_id, $click_count, $impressions, $active, $sales_qty, $conversion_rate, $revenue);
        }
    }

    // Delete the associated transients
    delete_transient('sbwc_order_confirmation_upsells_riode_clicks');
    delete_transient('sbwc_order_confirmation_upsells_riode_impressions');
    delete_transient('sbwc_order_confirmation_upsells_riode_sales_qty');
    delete_transient('sbwc_order_confirmation_upsells_riode_conversion_rate');
    delete_transient('sbwc_order_confirmation_upsells_riode_revenue');

}


?>