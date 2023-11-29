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

/**
 * Update the callback function to use the custom table
 *
 * @return void
 */
function sbwc_order_confirmation_upsells_riode_track_data_callback()
{

    global $wpdb;
    $table_name = $wpdb->prefix . 'sbwc_conf_upsells_tracking';

    // get sales transient
    $sales = get_transient('sbwc_ocus_sales_data');

    // get upsell ids
    $upsell_ids = get_option('sbwc_ocus_product_ids');
    $upsell_ids = explode(',', $upsell_ids);

    // holds sales data

    foreach ($sales as $order_id => $sales_data) :

        // get order
        $order = wc_get_order($order_id);

        // get order currency
        $order_currency = $order->get_currency();

        // if order has status of completed, processing or shipped
        if (in_array($order->get_status(), array('completed', 'processing', 'shipped'))) :

            // get order items
            $order_items = $order->get_items();

            // loop through order items
            foreach ($order_items as $order_item) :

                // check if order item is product
                if ($order_item->get_type() !== 'line_item') {
                    continue;
                }

                // get product id
                $product_id = $order_item->get_id();

                // get existing sales qty from custom table
                $sales_qty = $wpdb->get_var($wpdb->prepare(
                    "SELECT sales_qty FROM $table_name WHERE product_id = %d",
                    $product_id
                )) ?: 0;

                // get existing revenue from custom table
                $revenue = $wpdb->get_var($wpdb->prepare(
                    "SELECT revenue FROM $table_name WHERE product_id = %d",
                    $product_id
                )) ?: 0;

                // get existing impressions from custom table
                $impressions = $wpdb->get_var($wpdb->prepare(
                    "SELECT impressions FROM $table_name WHERE product_id = %d",
                    $product_id
                )) ?: 0;

                // get existing click count from custom table
                $click_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT click_count FROM $table_name WHERE product_id = %d",
                    $product_id
                )) ?: 0;

                // get clicks
                $clicks_trans = get_transient('sbwc_ocus_clicks');
                $click_count  = $clicks_trans[$product_id] + $click_count;

                // get impressions
                $impressions_trans = get_transient('sbwc_ocus_impressions');
                $impressions       = $impressions_trans[$product_id] + $impressions;

                // get quantity
                $qty = $order_item->get_quantity();

                // add quantity to sales qty
                $sales_qty += $qty;

                // add order total to revenue
                $revenue += $order->get_item_total($order_item, true, true);

                // if order currency not USD, convert revenue to USD
                if ($order_currency !== 'USD') {

                    // get exchange rate
                    $exchange_rate = get_option('alg_currency_switcher_exchange_rate_USD_'. $order_currency);

                    // convert revenue to USD
                    $revenue = $revenue / $exchange_rate;
                }

                // check whether product is active
                $active = in_array($product_id, $upsell_ids) ? true : false;

                // calculate conversion rate %
                $conversion_rate = $sales_qty / $impressions * 100;

                // run save tracking data function
                sbwc_order_confirmation_upsells_riode_save_tracking_data($product_id, $click_count, $impressions, $active, $sales_qty, $conversion_rate, $revenue);

            endforeach;

        endif;

    endforeach;

    // Delete the associated transients
    delete_transient('sbwc_ocus_clicks');
    delete_transient('sbwc_ocus_impressions');
    delete_transient('sbwc_ocus_sales_data');
}
