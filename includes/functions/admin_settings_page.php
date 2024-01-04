<?php

// ================================================
// add admin page where user can define upsell ids
// ================================================
add_action('admin_menu', 'sbwc_order_confirmation_upsells_riode_add_admin_menu');

function sbwc_order_confirmation_upsells_riode_add_admin_menu()
{
    add_menu_page(
        __('Order Confirmation Upsells', 'woocommerce'),
        __('Order Confirmation Upsells', 'woocommerce'),
        'manage_options',
        'sbwc-order-confirmation-upsells-riode',
        'sbwc_order_confirmation_upsells_riode_options_page',
        'dashicons-admin-generic',
        20
    );

    // add sales tracking sub menu page
    add_submenu_page(
        'sbwc-order-confirmation-upsells-riode',
        __('Sales Tracking', 'woocommerce'),
        __('Sales Tracking', 'woocommerce'),
        'manage_options',
        'sbwc-order-confirmation-upsells-riode-sales-tracking',
        'sbwc_order_confirmation_upsells_riode_sales_tracking_page'
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

        $languages = pll_languages_list();

        foreach ($languages as $language) {
            $option_name = 'sbwc_ocus_product_ids_' . $language;
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

        <h1><?php _e('Order Confirmation Upsells', 'woocommerce'); ?></h1>

        <!-- if polylang is installed -->
        <?php if (function_exists('pll_languages_list')) { ?>
            <p class="description"><?php _e('Enter the product IDs for upsells, separated by commas. For languages you do not want to display upsells for, leave the relevant input empty.', 'sbwc-order-confirmation-upsells-riode'); ?></p>
            <!-- if polylang not installed -->
        <?php } else { ?>
            <p class="description"><?php _e('Enter the product IDs for upsells, separated by commas.', 'sbwc-order-confirmation-upsells-riode'); ?></p>
        <?php } ?>

        <form method="post" action="options.php">

            <?php settings_fields('sbwc_order_confirmation_upsells_riode_settings'); ?>
            <?php do_settings_sections('sbwc_order_confirmation_upsells_riode_settings'); ?>

            <table class="form-table">
                <?php
                // Check if Polylang is active
                if (function_exists('pll_languages_list')) {

                    $languages = pll_languages_list();

                    foreach ($languages as $language) {

                        $option_name = 'sbwc_ocus_product_ids_' . $language;
                        $option_value = get_option($option_name);
                ?>
                        <tr valign="top">
                            <th scope="row"><?php echo sprintf(__('Upsell Product IDs (%s):', 'sbwc-order-confirmation-upsells-riode'), strtoupper($language)); ?></th>
                            <td>
                                <input type="text" name="<?php echo $option_name; ?>" value="<?php echo esc_attr($option_value); ?>" />

                            </td>
                        </tr>
                    <?php
                    }

                    // If Polylang is not active
                } else {
                    $option_name = 'sbwc_ocus_product_ids';
                    $option_value = get_option($option_name);
                    ?>
                    <tr valign="top">
                        <th scope="row"><?php _e('Upsell Product IDs:', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                        <td>
                            <input type="text" name="<?php echo $option_name; ?>" value="<?php echo esc_attr($option_value); ?>" />
                        </td>
                    </tr>
                <?php
                }
                ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>

    <style>
        /* 400px width for text inputs */
        input[type="text"] {
            width: 400px;
        }

        /* bold and italic description */
        .description {
            font-style: italic;
            font-weight: bold;
            font-size: 16px !important;
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

        }

        /* color #646970 for input labels */
        .form-table th {
            color: #646970;
        }
    </style>
<?php
}

?>