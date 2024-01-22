<?php
/**
 * AS Schedule Recurring Action to update tracking
 */
add_action('init', function () {

    if (as_has_scheduled_action('sbwc_order_confirmation_upsells_riode_update_tracking')) {
        return;
    }

    as_schedule_recurring_action(time(), 900, 'sbwc_order_confirmation_upsells_riode_update_tracking', array(), 'sbwc_order_confirmation_upsells_riode');
});

/**
 * AS Update Tracking hooked to action scheduler
 */
add_action('sbwc_order_confirmation_upsells_riode_update_tracking', function () {

    // debug: log with WC logger to see if action scheduler is working
    // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Action Scheduler is working');

    // get impressions from cache
    $impressions = get_transient('sbwc_ocus_impressions');

    // get sales data from cache
    $sales_data = get_transient('sbwc_ocus_sales_data');

    // get clicks from cache
    $clicks = get_transient('sbwc_ocus_clicks');

    // debug: log with WC logger to see if transient data is being retrieved
    // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Impressions: ' . print_r($impressions, true));
    // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Sales: ' . print_r($sales_data, true));
    // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Clicks: ' . print_r($clicks, true));

    // update tracking table sbwc_conf_upsells_tracking with product_id, click_count, impressions, active, sales_qty, conversion_rate and revenue
    global $wpdb;

    // get all products from tracking table
    $products = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sbwc_conf_upsells_tracking");

    // check if products are in tracking table
    $prods_not_in_table = order_conf_check_if_prod_in_table($impressions, $clicks, $wpdb);

    // debug: log with WC logger to see if products are being retrieved
    // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Products: ' . print_r($products, true));

    // check if product id is in tracking table
    foreach ($products as $product) {

        // search for product id in sales array and, if found, return order id (main array key)
        $order_id = array_search($product->product_id, array_column($sales_data, 'product_id'));

        // get order from order id
        $order = wc_get_order($order_id);

        // get order currency
        $order_currency = $order->get_currency();

        // debug: log with WC logger to see if product id is being retrieved
        // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Product ID: ' . print_r($product->product_id, true));

        // update impressions
        order_conf_update_impressions($wpdb, $product, $impressions, $prods_not_in_table);

        // update sales qty
        order_conf_update_sales_qty($wpdb, $product, $sales_data);

        // update clicks
        order_conf_update_clicks($wpdb, $product, $clicks, $prods_not_in_table);

        // calculate conversion rate
        order_conf_calc_conversion($wpdb, $product);

        // calculate revenue
        order_conf_calc_revenue($wpdb, $product, $order_currency);
    }
});

/**
 * Check for products in tracking table, insert if not found and return array of product ids 
 * not found in tracking table to be used for checks in other functions
 *
 * @param array $impressions - impressions array
 * @param object $wpdb - WordPress database object
 * @param array $clicks - clicks array
 *
 * @return array $prods_not_in_table - array of product ids not in tracking table
 */
function order_conf_check_if_prod_in_table($impressions, $clicks, $wpdb){

    // contains product ids not in tracking table
    $prods_not_in_table = [];

    // check if product id is in tracking table; if not, insert new row with product id, click_count, and impressions
    foreach ($impressions as $key => $value) {

        // debug: log with WC logger to see if product id is in tracking table
        // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Product ID: ' . print_r($key, true));

        // check if product id is in tracking table
        $product_id = $wpdb->get_var("SELECT product_id FROM {$wpdb->prefix}sbwc_conf_upsells_tracking WHERE product_id = $key");

        // debug: log with WC logger to see if product id is being retrieved
        // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Product ID: ' . print_r($product_id, true));

        // if product id is not in tracking table, insert new row with product id, click_count, and impressions
        if (!$product_id) {

            // debug: log with WC logger to see if product id is not in tracking table
            // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Product ID is not in tracking table');

            // insert new row with product id, click_count, and impressions
            $wpdb->insert(
                "{$wpdb->prefix}sbwc_conf_upsells_tracking",
                array(
                    'product_id'      => $key,
                    'click_count'     => $clicks[$key],
                    'impressions'     => $value,
                    'active'          => 1,
                    'sales_qty'       => 0,
                    'conversion_rate' => 0,
                    'revenue'         => 0,
                )
            );

            // add product id to array
            $prods_not_in_table[] = $key;
        }
    }

    // return array of product ids not in tracking table
    return $prods_not_in_table;
}

/**
 * Update impressions in tracking table
 *
 * @param  object $wpdb - WordPress database object
 * @param  object $product - product tracking table data object
 * @param  array $impressions - impressions array
 * @param  array $prods_not_in_table - array of product ids not currently in tracking table
 *
 * @return void
 */
function order_conf_update_impressions($wpdb, $product, $impressions, $prods_not_in_table){

    // if product id in $prods_not_in_table, skip updating impressions
    if (in_array($product->product_id, $prods_not_in_table)) {
        return;
    }

    // check if product id is in impressions array
    if (array_key_exists($product->product_id, $impressions)) {

        // debug: log with WC logger to see if product id is in impressions array
        // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Product ID is in impressions array');

        // get impressions for product id
        $impressions_count = $impressions[$product->product_id];

        // get existing impressions from tracking table
        $existing_impressions = $product->impressions;

        // debug: log with WC logger to see if impressions count is being retrieved
        // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Impressions Count: ' . print_r($impressions_count, true));

        // update impressions in tracking table
        $wpdb->update(
            "{$wpdb->prefix}sbwc_conf_upsells_tracking",
            array(
                'impressions' => $impressions_count + $existing_impressions
            ),
            array(
                'product_id' => $product->product_id
            )
        );
    }

}

/**
 * Update sales qty in tracking table
 *
 * @param  object $wpdb - WordPress database object
 * @param  object $product - product tracking table data object
 * @param  array $sales_data - sales data array
 *
 * @return void
 */
function order_conf_update_sales_qty($wpdb, $product, $sales_data){

    // check if product id is in sales array
    if (array_key_exists($product->product_id, $sales_data)) {

        // debug: log with WC logger to see if product id is in sales array
        // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Product ID is in sales array');

        // get sales for product id
        $sales_qty = $sales_data[$product->product_id];

        // get existing sales qty from tracking table
        $existing_sales_qty = $product->sales_qty;

        // debug: log with WC logger to see if sales qty is being retrieved
        // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Sales Qty: ' . print_r($sales_qty, true));

        // update sales qty in tracking table
        $wpdb->update(
            "{$wpdb->prefix}sbwc_conf_upsells_tracking",
            array(
                'sales_qty' => $sales_qty + $existing_sales_qty
            ),
            array(
                'product_id' => $product->product_id
            )
        );
    }
}

/**
 * Update clicks in tracking table
 *
 * @param  object $wpdb - WordPress database object
 * @param  object $product - product tracking table data object
 * @param  array $clicks - clicks array
 * @param  array $prods_not_in_table - array of product ids not currently in tracking table
 *
 * @return void
 */
function order_conf_update_clicks($wpdb, $product, $clicks, $prods_not_in_table)
{

    // if product id in $prods_not_in_table, skip updating impressions
    if (in_array($product->product_id, $prods_not_in_table)) {
        return;
    }

    // check if product id is in clicks array
    if (array_key_exists($product->product_id, $clicks)) {

        // debug: log with WC logger to see if product id is in clicks array
        // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Product ID is in clicks array');

        // get clicks for product id
        $click_count = $clicks[$product->product_id];

        // get existing clicks count from tracking table
        $existing_click_count = $product->click_count;

        // debug: log with WC logger to see if clicks count is being retrieved
        // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Clicks Count: ' . print_r($click_count, true));

        // update clicks count in tracking table
        $wpdb->update(
            "{$wpdb->prefix}sbwc_conf_upsells_tracking",
            array(
                'click_count' => $click_count + $existing_click_count
            ),
            array(
                'product_id' => $product->product_id
            )
        );
    }
}

/**
 * Calculate conversion rate
 *
 * @param  object $wpdb - WordPress database object
 * @param  object $product - product tracking table data object
 *
 * @return void
 */
function order_conf_calc_conversion($wpdb, $product)
{
    // calculate conversion rate
    if ($product->impressions > 0) {

        // debug: log with WC logger to see if impressions is greater than 0
        // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Impressions is greater than 0');

        // calculate conversion rate
        $conversion_rate = ($product->sales_qty / $product->impressions) * 100;

        // debug: log with WC logger to see if conversion rate is being calculated
        // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Conversion Rate: ' . print_r($conversion_rate, true));

        // update conversion rate in tracking table
        $wpdb->update(
            "{$wpdb->prefix}sbwc_conf_upsells_tracking",
            array(
                'conversion_rate' => $conversion_rate
            ),
            array(
                'product_id' => $product->product_id
            )
        );
    }
}

/**
 * Calculate revenue
 *
 * @param  object $wpdb - WordPress database object
 * @param  object $product - product tracking table data object
 * @param  string $order_currency - order currency
 *
 * @return void
 */
function order_conf_calc_revenue($wpdb, $product, $order_currency)
{

    // calculate revenue
    if ($product->sales_qty > 0) {

        // debug: log with WC logger to see if sales qty is greater than 0
        // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Sales Qty is greater than 0');
        $wc_product = wc_get_product($product->product_id);

        // calculate revenue
        $revenue = $product->sales_qty * $wc_product->get_price();

        // debug: log with WC logger to see if revenue is being calculated
        // wc_get_logger()->debug('SBWC Order Confirmation Upsells Riode: Revenue: ' . print_r($revenue, true));

        // update revenue in tracking table
        $wpdb->update(
            "{$wpdb->prefix}sbwc_conf_upsells_tracking",
            array(
                'revenue' => $revenue
            ),
            array(
                'product_id' => $product->product_id
            )
        );
    }
}
