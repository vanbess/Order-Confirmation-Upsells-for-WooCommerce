<?php
// ===========================
// render sales tracking page
// ===========================
function sbwc_order_confirmation_upsells_riode_sales_tracking_page()
{
    $upsell_ids = get_option('sbwc_ocus_product_ids');
    $upsell_ids = explode(',', $upsell_ids);

    // query tracking table
    global $wpdb;
    $table_name = $wpdb->prefix . 'sbwc_conf_upsells_tracking';
    $results = $wpdb->get_results("SELECT * FROM $table_name"); ?>

    <div class="wrap">

        <h1 id="sbwc_ocus_tracking_header">
            <?php _e('Sales Tracking', 'woocommerce'); ?>

            <span class="header-inputs">
                <label for="tracking-toggle"><?php _e('Tracking enabled?', 'woocommerce'); ?></label>
                <select name="sbwc_ocus_tracking_enabled" id="sbwc_ocus_tracking_enabled">
                    <option value="yes"><?php _e('Yes', 'woocommerce'); ?></option>
                    <option value="no"><?php _e('No', 'woocommerce'); ?></option>
                </select>
                <span class="help"><?php _e('Set this to No if you\'re experiencing server performance issues.', 'woocommerce'); ?></span>
            </span>
            <span class="header-inputs">
                <label for="clear-tracking"><?php _e('Click to clear tracking: ', 'woocommerce'); ?></label>
                <input type="submit" class="button button-primary" value="<?php _e('Clear Tracking', 'woocommerce'); ?>">
            </span>

        </h1>

        <?php
        // check if table is empty
        if (empty($results)) : ?>

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
                    <tr>
                        <!-- no data -->
                        <td colspan="7">
                            <p id="no-tracking-data"><?php _e('No sales tracking data currently available. If you\'ve enabled tracking, please check back in a while.', 'sbwc-order-confirmation-upsells-riode'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>

        <?php else : ?>

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

        <?php endif; ?>
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

        p#no-tracking-data {
            color: var(--wc-red);
            font-size: 16px;
            text-align: center;
            font-weight: 500;
            line-height: 3;
            margin-bottom: 0;
        }

        .header-inputs label {
            font-size: 14px;
            margin-right: 10px;
        }

        h1#sbwc_ocus_tracking_header {
            display: flex;
            align-items: self-end;
            justify-content: space-between;
        }

        span.help {
            font-size: initial;
            text-transform: none;
            color: var(--wc-red);
        }
    </style>
<?php
}
?>