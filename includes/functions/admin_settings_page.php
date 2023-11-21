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
    register_setting('sbwc_order_confirmation_upsells_riode_settings', 'sbwc_order_confirmation_upsells_riode_product_ids');
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
        <form method="post" action="options.php">
            <?php settings_fields('sbwc_order_confirmation_upsells_riode_settings'); ?>
            <?php do_settings_sections('sbwc_order_confirmation_upsells_riode_settings'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Upsell Product IDs', 'sbwc-order-confirmation-upsells-riode'); ?></th>
                    <td>
                        <input type="text" name="sbwc_order_confirmation_upsells_riode_product_ids" value="<?php echo esc_attr(get_option('sbwc_order_confirmation_upsells_riode_product_ids')); ?>" />
                        <p class="description"><?php _e('Enter the product IDs for upsells, separated by commas.', 'sbwc-order-confirmation-upsells-riode'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

?>