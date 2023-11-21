<?php
// ======================================
//  save impressions for upsell products
// ======================================
function sbwc_order_confirmation_upsells_riode_save_impressions($product_id)
{

    $impressions = get_transient('sbwc_order_confirmation_upsells_riode_impressions');

    if (!$impressions) {
        $impressions = array();
    }

    if (!isset($impressions[$product_id])) {
        $impressions[$product_id] = 0;
    }

    $impressions[$product_id]++;

    set_transient('sbwc_order_confirmation_upsells_riode_impressions', $impressions, DAY_IN_SECONDS);
}

add_action('woocommerce_thankyou', 'sbwc_order_confirmation_upsells_riode_save_impressions');
