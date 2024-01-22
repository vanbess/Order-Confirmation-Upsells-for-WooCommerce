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
    $results    = $wpdb->get_results("SELECT * FROM $table_name");

    // check if tracking is enabled
    $tracking_enabled = get_option('sbwc_ocus_tracking_enabled');

?>

    <div class="wrap">

        <!-- header inputs -->
        <div class="header-inputs">

            <label for="tracking-toggle"><b><i><?php _e('Tracking enabled?', 'woocommerce'); ?></i></b></label>

            <select name="sbwc_ocus_tracking_enabled" id="sbwc_ocus_tracking_enabled">
                <option value="yes" <?php echo ($tracking_enabled == 'yes') ? 'selected' : ''; ?>><?php _e('Yes', 'woocommerce'); ?></option>
                <option value="no" <?php echo ($tracking_enabled == 'no') ? 'selected' : ''; ?>><?php _e('No', 'woocommerce'); ?></option>
            </select>

            <span class="help"><?php _e('Set this to No if you\'re experiencing server performance issues.', 'woocommerce'); ?></span>

        </div>

        <div class="header-inputs">

            <label for="clear-tracking"><b><i><?php _e('Click to clear tracking: ', 'woocommerce'); ?></i></b></label>

            <input type="submit" class="button button-primary" value="<?php _e('Clear Tracking', 'woocommerce'); ?>">

        </div>

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
            margin-right: 5px;
        }

        h1#sbwc_ocus_tracking_header {
            display: flex;
            align-items: self-end;
            justify-content: space-between;
        }

        span.help {
            color: var(--wc-red);
        }

        h1#sbwc_ocus_tracking_header {
            width: 98%;
        }

        div#hidden-admin-notices-link-wrap {
            display: none !important;
        }

        span.header-inputs {
            display: block;
        }

        .header-inputs {
            padding-bottom: 20px;
        }

        hr {
            height: 0px;
            color: #ddd;
            background-color: #ddd;
            margin-top: 20px;
        }
    </style>
<?php
}
?>