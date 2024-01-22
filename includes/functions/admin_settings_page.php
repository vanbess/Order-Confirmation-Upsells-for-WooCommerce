<?php

// tracking
require_once __DIR__ . '/admin_tracking_page.php';

// ================================================
// add admin page where user can define upsell ids
// ================================================
add_action('admin_menu', 'sbwc_order_confirmation_upsells_riode_add_admin_menu');

function sbwc_order_confirmation_upsells_riode_add_admin_menu()
{

    add_submenu_page(
        'woocommerce',
        __('Order Confirmation Upsells', 'woocommerce'),
        __('Order Confirmation Upsells', 'woocommerce'),
        'manage_options',
        'sbwc-order-confirmation-upsells-riode',
        'sbwc_order_confirmation_upsells_riode_options_page'
    );
}

// ================================================
// register settings for upsell product IDs
// ================================================
function sbwc_order_confirmation_upsells_riode_register_settings()
{
    // Register a new setting for upsell product IDs
    register_setting('sbwc_order_confirmation_upsells_riode_settings', 'sbwc_ocus_product_ids');

    // Register a new setting for upsell product IDs for each language
    if (function_exists('pll_languages_list')) {

        $langs = pll_languages_list();

        foreach ($langs as $lang) {
            $option_name = 'sbwc_ocus_product_ids_' . $lang;
            register_setting('sbwc_order_confirmation_upsells_riode_settings', $option_name);
        }
    }
}

add_action('admin_init', 'sbwc_order_confirmation_upsells_riode_register_settings');

// ================================================
// admin page to define upsell product IDs
// ================================================
function sbwc_order_confirmation_upsells_riode_options_page()
{
?>

    <div class="wrap">

        <h1 id="ord_conf_admin_title_main"><?php _e('Order Confirmation Upsells', 'woocommerce'); ?></h1>

        <!-- nav -->

        <h2 class="nav-tab-wrapper">

            <a href="#form-tab" class="nav-tab nav-tab-active"><?php _e('Product IDs', 'woocommerce'); ?></a>

            <a href="#tracking-tab" class="nav-tab"><?php _e('Sales Tracking', 'woocommerce'); ?></a>
        </h2>

        <!-- product ids -->

        <div id="form-tab" class="tab-content">

            <!-- if polylang is installed -->
            <?php if (function_exists('pll_languages_list')) { ?>

                <p class="description">
                    <?php _e('Enter the product IDs for upsells, separated by commas. For languages you do not want to display upsells for, leave the relevant input empty.', 'woocommerce'); ?>
                </p>

                <hr>

                <!-- if polylang not installed -->
            <?php } else { ?>

                <p class="description"><?php _e('Enter the product IDs for upsells, separated by commas.', 'woocommerce'); ?></p>
                <hr>
            <?php } ?>

            <form method="post" action="options.php">

                <?php settings_fields('sbwc_order_confirmation_upsells_riode_settings'); ?>

                <?php do_settings_sections('sbwc_order_confirmation_upsells_riode_settings'); ?>

                <table class="form-table">
                    <?php
                    // Check if Polylang is active
                    if (function_exists('pll_languages_list')) :

                        $langs = pll_languages_list();

                        foreach ($langs as $lang) :

                            $option_name = 'sbwc_ocus_product_ids_' . $lang;
                            $option_values = get_option($option_name);

                            // debug
                            // echo '<pre>';
                            // print_r($option_value ? $option_value : 'empty');
                            // echo '</pre>';
                    ?>

                            <tr valign="top">

                                <th class="th_input_title" scope="row">
                                    <?php echo sprintf(__('Upsell Product IDs (%s):', 'woocommerce'), strtoupper($lang)); ?>
                                </th>

                                <td>
                                    <select name="<?php echo $option_name; ?>[]" multiple="multiple" class="order-conf-upsell-ids" data-placeholder="<?php _e('Search for a product', 'woocommerce'); ?>" data-lang="<?php echo $lang; ?>">
                                        <?php foreach ($option_values as $prod_id) { ?>
                                            <option selected="selected"><?php echo get_the_title($prod_id) . ' [' . wc_strtoupper($lang) . ']'; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                        <?php
                        endforeach;
                    else :
                        $option_name = 'sbwc_ocus_product_ids';
                        $option_value = get_option($option_name);
                        ?>

                        <tr valign="top">

                            <th class="th_input_title" scope="row"><?php _e('Upsell Product IDs:', 'woocommerce'); ?></th>

                            <td>

                                <input type="text" name="<?php echo $option_name; ?>" value="<?php echo esc_attr($option_value); ?>" />
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>

        <!-- tracking info -->

        <div id="tracking-tab" class="tab-content">
            <?php
            sbwc_order_confirmation_upsells_riode_sales_tracking_page();
            ?>
        </div>
    </div>

    <!-- include select2 -->

    <link href="<?php echo plugins_url('woocommerce/assets/css/select2.css'); ?>" rel="stylesheet" />

    <script src="<?php echo plugins_url('woocommerce/assets/js/select2/select2.min.js'); ?>"></script>

    <!-- select2, tabs etc -->
    <script>
        jQuery(document).ready(function($) {
            // trigger tab switch
            $('.nav-tab').click(function(e) {

                e.preventDefault();

                // remove active class from all tabs
                $('.nav-tab').removeClass('nav-tab-active');

                // add active class to clicked tab
                $(this).addClass('nav-tab-active');

                // hide all tab content
                $('.tab-content').hide();

                // show tab content for clicked tab
                var tab = $(this).attr('href');
                $(tab).show();
            });
            // init select2 for product search
            $('.order-conf-upsell-ids').select2({

                placeholder: $(this).data('placeholder'),
                minimumInputLength: 3,
                width: '300px',
                escapeMarkup: function(markup) {
                    return markup;
                },
                ajax: {
                    url: ajaxurl,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            term: params.term,
                            action: 'order_conf_fetch_products',
                            security: '<?php echo wp_create_nonce("order_conf_fetch_products"); ?>',
                            limit: 20,
                            lang: $(this).data('lang')
                        };
                    },
                    processResults: function(data) {
                        var terms = [];
                        if (data) {
                            $.each(data, function(id, text) {
                                terms.push({
                                    id: id,
                                    text: text
                                });
                            });
                        }
                        return {
                            results: terms
                        };
                    },
                    cache: true
                },
                // escapeMarkup: function(markup) {
                //     return markup;
                // },
                // minimumInputLength: 3,
                // templateResult: formatProduct,
                // templateSelection: formatProductSelection
            });

            // tracking enabled on change, update setting 
            $('#tracking-enabled').change(function() {

                // get value
                var tracking_enabled = $(this).val();

                // update setting
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'order_conf_update_tracking_enabled',
                        tracking_enabled: tracking_enabled,
                        security: '<?php echo wp_create_nonce("order_conf_update_tracking_enabled"); ?>'
                    },
                    success: function(data) {
                        // console.log(data);

                        alert('<?php _e('Tracking enabled setting updated', 'woocommerce'); ?>');
                    }
                });
            });

            // clear tracking data on click
            $('#clear-tracking').click(function(e) {

                e.preventDefault();

                // confirm
                var confirm_clear = confirm('<?php _e('Are you sure you want to clear all tracking data?', 'woocommerce'); ?>');

                // if confirmed
                if (confirm_clear) {

                    // clear tracking data
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'order_conf_clear_tracking',
                            security: '<?php echo wp_create_nonce("order_conf_clear_tracking"); ?>'
                        },
                        success: function(data) {
                            // console.log(data);
                            // reload page

                            alert('<?php _e('Tracking data cleared', 'woocommerce'); ?>');
                            location.reload();
                        }
                    });
                }
            });

        });
    </script>

    <!-- css -->
    <style>
        /* hide all tab content */
        .tab-content {
            display: none;
            padding-top: 20px 30px 10px;
            background: white;
            padding: 30px;
            width: 95.6%;
            border-bottom-right-radius: 5px;
            border-bottom-left-radius: 5px;
        }

        /* display first tab content */
        .tab-content:first-of-type {
            display: block;
        }

        /* 400px width for text inputs */
        input[type="text"] {
            width: 400px;
        }

        /* bold and italic description */
        .description {
            font-style: italic;
            font-weight: 500;
            font-size: 14px !important;
            color: var(--wc-red) !important;
        }

        /* white background heading with padding of 10px 15px and bottom margin of 30px */
        .wrap h1 {
            background-color: #fff;
            padding: 10px 15px;
            margin-bottom: 20px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
            font-weight: 600;
            text-transform: uppercase;
            color: #646970;
            width: 97.5%;
        }

        /* color #646970 for input labels */
        .form-table th {
            color: #646970;
        }

        span.select2.select2-container.select2-container--default {
            min-width: 300px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            padding: 0 20px;
        }

        input.select2-search__field {
            margin-top: 0 !important;
        }
    </style>
<?php
}

// ================================================
// fetch products for select2
// ================================================
function sbwc_order_conf_fetch_products()
{

    // Check the nonce
    check_ajax_referer('order_conf_fetch_products', 'security');

    // Get the search term
    $term = $_GET['term'];

    // Get the language
    $lang = $_GET['lang'];

    // Get the products
    $products = wc_get_products(array(
        'status' => 'publish',
        'limit'  => 20,
        's'      => $term,
        'lang'   => $lang
    ));

    // Array of results
    $results = array();

    // Loop through products
    foreach ($products as $product) {

        // Get the product name
        $product_name = $product->get_name();

        // Get the product ID
        $product_id = $product->get_id();

        // Add to results array
        $results[$product_id] = $product_name . ' [' . wc_strtoupper($lang) . ']';
    }

    // Return JSON
    wp_send_json($results);
}

add_action('wp_ajax_order_conf_fetch_products', 'sbwc_order_conf_fetch_products');

// ================================
// update tracking enabled setting
// ================================
function sbwc_order_conf_update_tracking_enabled()
{

    // Check the nonce
    check_ajax_referer('order_conf_update_tracking_enabled', 'security');

    // Get the tracking enabled value
    $tracking_enabled = $_POST['tracking_enabled'];

    // Update the setting
    update_option('sbwc_ocus_tracking_enabled', $tracking_enabled);

    // Return JSON
    wp_send_json('success');
}

add_action('wp_ajax_order_conf_update_tracking_enabled', 'sbwc_order_conf_update_tracking_enabled');

// ====================
// clear tracking data
// ====================
function sbwc_order_conf_clear_tracking()
{

    // Check the nonce
    check_ajax_referer('order_conf_clear_tracking', 'security');

    // query tracking table
    global $wpdb;
    $table_name = $wpdb->prefix . 'sbwc_conf_upsells_tracking';

    // delete all rows
    $wpdb->query("TRUNCATE TABLE $table_name");

    // Return JSON
    wp_send_json('success');
}

?>