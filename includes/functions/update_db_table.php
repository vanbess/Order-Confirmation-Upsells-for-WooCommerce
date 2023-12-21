<?php

/**
 * Save the tracking data to the custom table
 *
 * @param int/string $product_id
 * @param int/string $click_count
 * @param int/string $impressions
 * @param bool $active
 * @param int/string $sales_qty
 * @param float $conversion_rate
 * @param float $revenue
 * @return void
 */
function sbwc_order_confirmation_upsells_riode_save_tracking_data($product_id, $click_count, $impressions, $active, $sales_qty, $conversion_rate, $revenue)
{

    global $wpdb;
    $table_name = $wpdb->prefix . 'sbwc_conf_upsells_tracking';

    // check if product_id exists
    $product_id_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT product_id FROM $table_name WHERE product_id = %d",
        $product_id
    ));

    $log = new WC_Logger();

    // log submitted values
    $log->add('sbwc_order_confirmation_upsells_riode', 'Submitted values: ' . ' | Product ID: ' . $product_id . ' | Clicks: ' . $click_count . ' | Impressions: ' . $impressions . ' | Active: ' . $active . ' | Sales Qty: ' . $sales_qty . ' | Conversion Rate: ' . $conversion_rate . ' | Revenue: ' . $revenue);

    // log for debugging
    $log->add('sbwc_order_confirmation_upsells_riode', 'Order confirmation page product exists in tracking table - Product ID: ' . $product_id . ' | Clicks: ' . $click_count . ' | Impressions: ' . $impressions . ' | Active: ' . $active . ' | Sales Qty: ' . $sales_qty . ' | Conversion Rate: ' . $conversion_rate . ' | Revenue: ' . $revenue);

    // update if product_id exists
    if ($product_id_exists) {

        try {
            $wpdb->update(
                $table_name,
                array(
                    'click_count'     => $click_count,
                    'impressions'     => $impressions,
                    'active'          => $active ? 1 : 0,
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

            // Log successful evaluation
            $log->add('sbwc_order_confirmation_upsells_riode', 'Tracking data updated successfully.');
        } catch (Exception $e) {
            // Log error
            $log->add('sbwc_order_confirmation_upsells_riode', 'Error updating tracking data: ' . $e->getMessage());
        }
    } else {

        // insert if product_id does not exist
        try {
            $wpdb->insert(
                $table_name,
                array(
                    'id'              => $product_id,
                    'product_id'      => $product_id,
                    'click_count'     => $click_count,
                    'impressions'     => $impressions,
                    'active'          => $active ? 1 : 0,
                    'sales_qty'       => $sales_qty,
                    'conversion_rate' => $conversion_rate,
                    'revenue'         => $revenue
                ),
                array(
                    '%d',
                    '%d'
                )
            );

            // Log successful insertion
            $log->add('sbwc_order_confirmation_upsells_riode', 'Tracking data inserted successfully.');
        } catch (Exception $e) {
            // Log error
            $log->add('sbwc_order_confirmation_upsells_riode', 'Error inserting tracking data: ' . $e->getMessage());
        }
    }
}

/**
 * Update the callback function to use the custom table
 *
 * @return void
 */
function sbwc_order_confirmation_upsells_riode_track_data_callback()
{

    global $wpdb;
    $table_name = $wpdb->prefix . 'sbwc_conf_upsells_tracking';

    $log = new WC_Logger();

    $log->add('sbwc_order_confirmation_upsells_riode', '==============================================================================================================');
    $log->add('sbwc_order_confirmation_upsells_riode', 'Starting to update tracking data...');

    // get sales transient
    $sales_trans = get_transient('sbwc_ocus_sales_data') ?: array();

    if (empty($sales_trans)) {
        $log->add('sbwc_order_confirmation_upsells_riode', 'No sales data available. Standing over until next run.');
        $log->add('sbwc_order_confirmation_upsells_riode', '==============================================================================================================');
        return;
    }

    $simplified_array = array();

    // simplify $sales_trans array
    foreach ($sales_trans as $order_id => $product_data) :

        foreach ($product_data as $index => $product_data) :

            foreach ($product_data as $product_id => $sales_qty) :

                $simplified_array[$order_id][$product_id] = $sales_qty;
            endforeach;

        endforeach;

    endforeach;

    $sales_trans = $simplified_array;

    // get clicks
    $clicks_trans = get_transient('sbwc_ocus_clicks');

    // get impressions
    $impressions_trans = get_transient('sbwc_ocus_impressions');

    // debug
    // $log->add('sbwc_order_confirmation_upsells_riode', 'Sales trans: ' . print_r($simplified_array, true));
    // $log->add('sbwc_order_confirmation_upsells_riode', 'Clicks trans: ' . print_r($clicks_trans, true));
    // $log->add('sbwc_order_confirmation_upsells_riode', 'Impressions trans: ' . print_r($impressions_trans, true));
    // return;

    // Check if Polylang is active
    if (function_exists('pll_languages_list')) {

        $languages = pll_languages_list();

        foreach ($languages as $language) {
            $upsells[] = get_option('sbwc_ocus_product_ids_' . $language);
        }

        // If Polylang is not active
    } else {
        $upsells = get_option('sbwc_ocus_product_ids');
    }

    // if upsells empty or not defined, bail
    if (empty($upsells)) {
        $log->add('sbwc_order_confirmation_upsells_riode', 'No upsells defined. Stopping execution.');
        return;
    }

    // debug upsells
    // $log->add('sbwc_order_confirmation_upsells_riode', 'Upsell data: ' . print_r($upsells, true));

    // return;

    // log
    $log->add('sbwc_order_confirmation_upsells_riode', 'Starting to loop through sales data...');
    $log->add('sbwc_order_confirmation_upsells_riode', 'Sales transaction length: ' . count($sales_trans));

    // ========================
    // loop through sales data
    // ========================
    foreach ($sales_trans as $order_id => $sales_data) :

        // log sales data for debugging
        // $log->add('sbwc_order_confirmation_upsells_riode', 'Sales data: ' . print_r($sales_data, true));
        // continue;

        // log
        $log->add('sbwc_order_confirmation_upsells_riode', 'Order ID: ' . $order_id);

        // get order
        $order = wc_get_order($order_id);

        // get order
        $order_items = $order->get_items();

        // get order currency
        $order_currency = $order->get_currency();

        // debug
        // $log->add('sbwc_order_confirmation_upsells_riode', 'Sales data: ' . print_r($sales_data, true));

        // loop through product data
        foreach ($sales_data as $product_id => $sales_qty) :

            // debug
            $log->add('sbwc_order_confirmation_upsells_riode', 'Post type: ' . get_post_type($product_id));
            $log->add('sbwc_order_confirmation_upsells_riode', 'Sales Qty: ' . $sales_qty);
            $log->add('sbwc_order_confirmation_upsells_riode', 'Product ID: ' . $product_id);
            // continue;

            // ========================================================
            // if order has status of completed, processing or shipped
            // ========================================================
            if (in_array($order->get_status(), array('completed', 'processing', 'shipped', 'on-hold'))) :

                // check if product exists in table
                $product_id_exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT product_id FROM $table_name WHERE product_id = %d",
                    $product_id
                ));

                // ============================================================
                // if product id exists in table, get existing data and update
                // ============================================================
                if ($product_id_exists) {

                    // get product order price from $order_items
                    $order_item = $order_items[$product_id];

                    // log
                    $log->add('sbwc_order_confirmation_upsells_riode', 'Product ID exists in table: ' . $product_id);
                    $log->add('sbwc_order_confirmation_upsells_riode', 'Starting to get existing data from table...');

                    // get existing sales qty from custom table
                    $sales_qty_table = $wpdb->get_var($wpdb->prepare(
                        "SELECT sales_qty FROM $table_name WHERE product_id = %d",
                        $product_id
                    )) ?: 0;

                    // get existing revenue from custom table
                    $revenue_table = $wpdb->get_var($wpdb->prepare(
                        "SELECT revenue FROM $table_name WHERE product_id = %d",
                        $product_id
                    )) ?: 0;

                    // get existing impressions from custom table
                    $impressions_table = $wpdb->get_var($wpdb->prepare(
                        "SELECT impressions FROM $table_name WHERE product_id = %d",
                        $product_id
                    )) ?: 0;

                    // get existing click count from custom table
                    $click_count_table = $wpdb->get_var($wpdb->prepare(
                        "SELECT click_count FROM $table_name WHERE product_id = %d",
                        $product_id
                    )) ?: 0;

                    // get click count
                    $click_count = $clicks_trans[$product_id] ? (int)$clicks_trans[$product_id] + (int)$click_count_table : 1 + (int)$click_count_table;

                    // get impressions count
                    $impressions = $impressions_trans[$product_id] ? (int)$impressions_trans[$product_id] + (int)$impressions_table : 1 + (int)$impressions_table;

                    $revenue = 0;

                    // get product order price from $order_items
                    foreach ($order_items as $order_item) :
                        if ($order_item['product_id'] == $product_id) :

                            // get order item total
                            $revenue = $order_item['total'];
                            break 1;

                        endif;
                    endforeach;

                    // update sales qty
                    $sales_qty = (int)$sales_qty + (int)$sales_qty_table;

                    // log
                    $log->add('sbwc_order_confirmation_upsells_riode', 'Existing data: ' . ' | Sales Qty: ' . $sales_qty . ' | Revenue: ' . $revenue . ' | Impressions: ' . $impressions . ' | Clicks: ' . $click_count);

                    // log
                    $log->add('sbwc_order_confirmation_upsells_riode', 'Revenue: ' . $revenue);
                    $log->add('sbwc_order_confirmation_upsells_riode', 'Starting to check if order currency is USD...');

                    // if order currency not USD, convert revenue to USD
                    if ($order_currency !== 'USD') {

                        // log
                        $log->add('sbwc_order_confirmation_upsells_riode', 'Order currency is not USD: ' . $order_currency);
                        $log->add('sbwc_order_confirmation_upsells_riode', 'Starting to get exchange rate...');

                        // get exchange rate
                        $exchange_rate = get_option('alg_currency_switcher_exchange_rate_USD_' . $order_currency);

                        // convert revenue to USD
                        $revenue = $revenue / $exchange_rate;

                        // log
                        $log->add('sbwc_order_confirmation_upsells_riode', 'Exchange rate: ' . $exchange_rate);
                        $log->add('sbwc_order_confirmation_upsells_riode', 'Revenue converted to USD: ' . $revenue);
                    }

                    // update revenue
                    $revenue = $revenue + $revenue_table;

                    // log
                    $log->add('sbwc_order_confirmation_upsells_riode', 'Starting to check if product is active...');

                    // check whether product is active
                    foreach ($upsells as $upsell_arr) :
                        if (in_array($product_id, $upsell_arr)) :
                            $active = 'true';
                            break 1;
                        else :
                            $active = 'false';
                        endif;
                    endforeach;

                    // log
                    $log->add('sbwc_order_confirmation_upsells_riode', 'Product is active: ' . $active);

                    // calculate conversion rate %
                    $conversion_rate = ($sales_qty / $impressions) * 100;

                    // log
                    $log->add('sbwc_order_confirmation_upsells_riode', 'Conversion rate: ' . $conversion_rate);

                    // log
                    $log->add('sbwc_order_confirmation_upsells_riode', 'Starting to save tracking data...');

                    // ============================================================================
                    // if product does not exist in table, do initial setup for adding its data to
                    // ============================================================================
                } else {

                    // Log that product does not exist in table
                    $log->add('sbwc_order_confirmation_upsells_riode', 'Product ID does not exist in table: ' . $product_id . '. Setting up initial data...');

                    // setup variables
                    $click_count     = $clicks_trans[$product_id] ? $clicks_trans[$product_id] : 1;
                    $impressions     = $impressions_trans[$product_id] ? $impressions_trans[$product_id] : 1;

                    // debug
                    $log->add('sbwc_order_confirmation_upsells_riode', 'Clicks: ' . $click_count);
                    $log->add('sbwc_order_confirmation_upsells_riode', 'Impressions: ' . $impressions);

                    // check whether product is active
                    foreach ($upsells as $upsell_arr) :
                        if (in_array($product_id, $upsell_arr)) :
                            $active = 'true';
                            break 1;
                        else :
                            $active = 'false';
                        endif;
                    endforeach;

                    // debug
                    $log->add('sbwc_order_confirmation_upsells_riode', 'Active: ' . $active);

                    $conversion_rate = ($sales_qty / $impressions) * 100;

                    // debug
                    $log->add('sbwc_order_confirmation_upsells_riode', 'Conversion rate: ' . $conversion_rate);

                    $revenue = 0;

                    // get product order price from $order_items
                    foreach ($order_items as $order_item) :
                        if ($order_item['product_id'] == $product_id) :

                            // get order item total
                            $revenue = $order_item['total'];
                            break 1;

                        endif;
                    endforeach;

                    $log->add('sbwc_order_confirmation_upsells_riode', 'Revenue: ' . $revenue);

                    // if order currency not USD, convert revenue to USD
                    if ($order_currency !== 'USD') {

                        // log
                        $log->add('sbwc_order_confirmation_upsells_riode', 'Order currency is not USD: ' . $order_currency);
                        $log->add('sbwc_order_confirmation_upsells_riode', 'Starting to get exchange rate...');

                        // get exchange rate
                        $exchange_rate = get_option('alg_currency_switcher_exchange_rate_USD_' . $order_currency);

                        // convert revenue to USD
                        $revenue = $revenue / $exchange_rate;

                        // log
                        $log->add('sbwc_order_confirmation_upsells_riode', 'Exchange rate: ' . $exchange_rate);
                        $log->add('sbwc_order_confirmation_upsells_riode', 'Revenue converted to USD: ' . $revenue);
                    }
                }

            endif;

            // ================================
            // run save tracking data function
            // ================================
            sbwc_order_confirmation_upsells_riode_save_tracking_data($product_id, $click_count, $impressions, $active, $sales_qty, $conversion_rate, $revenue);

        endforeach;

    endforeach;

    // Delete the associated transients
    $sales_deleted       = delete_transient('sbwc_ocus_sales_data');
    $clicks_deleted      = delete_transient('sbwc_ocus_clicks');
    $impressions_deleted = delete_transient('sbwc_ocus_impressions');

    // Log that transients were deleted
    $log->add('sbwc_order_confirmation_upsells_riode', 'Sales transient deleted: ' . $sales_deleted);
    $log->add('sbwc_order_confirmation_upsells_riode', 'Clicks transient deleted: ' . $clicks_deleted);
    $log->add('sbwc_order_confirmation_upsells_riode', 'Impressions transient deleted: ' . $impressions_deleted);
    $log->add('sbwc_order_confirmation_upsells_riode', '==============================================================================================================');
}
