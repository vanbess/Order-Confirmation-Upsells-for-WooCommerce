<?php
// ===========================
// render sales tracking page
// ===========================
function sbwc_order_confirmation_upsells_riode_sales_tracking_page()
{

    // query tracking table
    global $wpdb;
    $table_name = $wpdb->prefix . 'sbwc_conf_upsells_tracking';
    $results    = $wpdb->get_results("SELECT * FROM $table_name");

    // echo '<pre>';
    // print_r($results);
    // echo '</pre>';

    // check if tracking is enabled
    $tracking_enabled = get_option('sbwc_ocus_tracking_enabled');

?>

    <div class="wrap">

        <!-- header inputs -->
        <div class="header-inputs">

            <label for="tracking-toggle"><b><i><?php _e('Tracking enabled?', 'woocommerce'); ?></i></b></label>

            <select name="sbwc_ocus_tracking_enabled" id="sbwc_ocus_tracking_enabled" onchange="setTracking(event)">
                <option value="yes" <?php echo ($tracking_enabled == 'yes') ? 'selected' : ''; ?>><?php _e('Yes', 'woocommerce'); ?></option>
                <option value="no" <?php echo ($tracking_enabled == 'no') ? 'selected' : ''; ?>><?php _e('No', 'woocommerce'); ?></option>
            </select>

            <span class="help"><?php _e('Set this to No if you\'re experiencing server performance issues.', 'woocommerce'); ?></span>

        </div>

        <div class="header-inputs">

            <label for="clear-tracking"><b><i><?php _e('Click to clear tracking: ', 'woocommerce'); ?></i></b></label>

            <button onclick="clearTracking(event)" class="button button-primary"><?php _e('Clear Tracking', 'woocommerce'); ?></button>

            <script>
                // clear tracking 
                function clearTracking(event) {

                    event.preventDefault();

                    confirm('<?php _e('Are you sure you want to clear all tracking data?', 'woocommerce') ?>');

                    var data = {
                        'action': 'sbwc_conf_clear_tracking'
                    };

                    jQuery.post(ajaxurl, data, function(response) {
                        // console.log(response);
                        alert('<?php _e('Tracking data cleared.', 'woocommerce') ?>');
                        location.reload();
                    });

                }

                // set tracking
                function setTracking(event) {

                    event.preventDefault();

                    var tracking_enabled = $('#sbwc_ocus_tracking_enabled').val();

                    var data = {
                        'action': 'sbwc_conf_set_tracking',
                        'tracking_enabled': tracking_enabled
                    };

                    jQuery.post(ajaxurl, data, function(response) {
                        // console.log(response);
                        alert('<?php _e('Tracking setting updated.', 'woocommerce') ?>');
                        location.reload();
                    });

                }
            </script>

        </div>

        <?php
        // check if table is empty
        if (empty($results)) : ?>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col"><?php _e('Product', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                        <th scope="col"><?php _e('Impressions', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                        <th scope="col"><?php _e('Clicks', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                        <th scope="col"><?php _e('Qty Sold', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                        <th scope="col"><?php _e('Total Sales Value', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                        <th scope="col"><?php _e('Conversion Rate', 'sbwc-order-confirmation-upsells-riode'); ?></th>
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

            <table class="wp-list-table widefat fixed striped order-conf-upsell-tracking">
                <thead>
                    <tr>
                        <th scope="col"><?php _e('Product', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                        <th scope="col"><?php _e('Impressions', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                        <th scope="col"><?php _e('Clicks', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                        <th scope="col"><?php _e('Qty Sold', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                        <th scope="col"><?php _e('Total Sales Value', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                        <th scope="col"><?php _e('Conversion Rate', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result) : ?>
                        <tr>

                            <!-- product id -->
                            <td>
                                <a href="<?php echo get_the_permalink($result->product_id); ?>" target="_blank">
                                    <?php echo get_the_title($result->product_id); ?>
                                    <?php echo function_exists('pll_get_post_language') ? ' [' . strtoupper(pll_get_post_language($result->product_id)) . ']' : ''; ?>
                                </a>
                            </td>

                            <!-- impressions -->
                            <td>
                                <?php
                                echo $result->impressions;
                                ?>
                            </td>

                            <!-- clicks -->
                            <td>
                                <?php
                                echo $result->click_count;
                                ?>
                            </td>

                            <!-- quantity sold -->
                            <td>
                                <?php
                                echo $result->sales_qty;
                                ?>
                            </td>

                            <!-- total sales value -->
                            <td>
                                <?php
                                echo $result->revenue > 0 ? round($result->revenue, 2) . ' USD' :  'N/A';
                                ?>
                            </td>

                            <!-- conversion rate -->
                            <td>
                                <?php
                                echo $result->conversion_rate > 0 ? round($result->conversion_rate, 2) * 100 . '%' : 'N/A';
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

        table.wp-list-table.widefat.fixed.striped.order-conf-upsell-tracking {
            font-weight: 600;
        }
    </style>
<?php
}

// clear tracking action
add_action('wp_ajax_sbwc_conf_clear_tracking', 'sbwc_conf_clear_tracking');

function sbwc_conf_clear_tracking()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'sbwc_conf_upsells_tracking';
    $cleared = $wpdb->query("TRUNCATE TABLE $table_name");

    if ($cleared) :
        wp_send_json(array('success' => true));
    else :
        wp_send_json(array('success' => false));
    endif;
}

// set tracking action
add_action('wp_ajax_sbwc_conf_set_tracking', 'sbwc_conf_set_tracking');

function sbwc_conf_set_tracking()
{
    $tracking_enabled = $_POST['tracking_enabled'];
    update_option('sbwc_ocus_tracking_enabled', $tracking_enabled);
    wp_send_json(array('success' => true));
}
?>