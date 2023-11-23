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
                    <th scope="col"><?php _e('Active?', 'sbwc-order-confirmation-upsells-riode'); ?></th>
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

                        <!-- product id -->
                        <td>
                            <?php echo $upsell_id; ?>
                        </td>

                        <!-- active? -->
                        <td>
                            <?php
                            ?>
                        </td>

                        <!-- impressions -->
                        <td>
                            <?php
                            ?>
                        </td>

                        <!-- clicks -->
                        <td>
                            <?php
                            ?>
                        </td>

                        <!-- quantity sold -->
                        <td>
                            <?php
                            ?>
                        </td>

                        <!-- total sales value -->
                        <td>
                            <?php
                            ?>
                        </td>

                        <!-- last sold date -->
                        <td>
                            <?php
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <style>
        /* white background heading with padding of 10px 15px and bottom margin of 30px */
        .wrap h1 {
            background-color: #fff;
            padding: 10px 15px;
            margin-bottom: 20px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
            font-weight: 600;
            text-transform: uppercase;
            color: #646970;
        }

        /* bold table head elements */
        .wp-list-table thead th {
            font-weight: bold;
        }
    </style>
<?php
}
?>