<?php
// ===========================
// render sales tracking page
// ===========================
function sbwc_order_confirmation_upsells_riode_sales_tracking_page()
{
    $upsell_ids = get_option('sbwc_order_confirmation_upsells_riode_product_ids');
    $upsell_ids = explode(',', $upsell_ids);

    if (empty($upsell_ids)) {
        echo '<div class="wrap">';
        echo '<h1>' . __('Sales Tracking', 'woocommerce') . '</h1>';
        echo '<p>' . __('No upsell product IDs defined.', 'sbwc-order-confirmation-upsells-riode') . '</p>';
        echo '</div>';
        return;
    }

?>
    <div class="wrap">
        <h1><?php _e('Sales Tracking', 'woocommerce'); ?></h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col"><?php _e('Product ID', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                    <th scope="col"><?php _e('Impressions', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                    <th scope="col"><?php _e('Clicks', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                    <th scope="col"><?php _e('Qty Sold', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                    <th scope="col"><?php _e('Total Sales Value', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                    <th scope="col"><?php _e('Last Sold Date', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($upsell_ids as $upsell_id) : ?>
                    <tr>
                        <td><?php echo $upsell_id; ?></td>
                        <td><?php // Add code to display impressions 
                            ?></td>
                        <td><?php // Add code to display clicks 
                            ?></td>
                        <td><?php // Add code to display quantity sold 
                            ?></td>
                        <td><?php // Add code to display total sales value 
                            ?></td>
                        <td><?php // Add code to display last sold date 
                            ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
}
?>