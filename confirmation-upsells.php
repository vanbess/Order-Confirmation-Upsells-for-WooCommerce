<?php

/**
 * Plugin Name: SBWC Order Confirmation Page Upsells Riode
 * Plugin URI: https://silverbackev.co.za/
 * Description: This plugin adds upsells to the order confirmation page no websites which uses the Riode theme.
 * Version: 1.0.2
 * Author: WC Bessinger
 * Author URI: https://silvebackdev.co.za/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sbwc-order-confirmation-upsells-riode
 * Domain Path: /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', function () {

    // ===============================
    // Check if WooCommerce is active
    // ===============================
    if (!is_plugin_active('woocommerce/woocommerce.php')) {

        // show error message to user and bail
        add_action('admin_notices', 'sbwc_order_confirmation_upsells_riode_admin_notice__error');

        function sbwc_order_confirmation_upsells_riode_admin_notice__error()
        {
?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e('WooCommerce is not active, please install and activate WooCommerce to use SBWC Order Confirmation Upsells Riode plugin.', 'sbwc-order-confirmation-upsells-riode'); ?></p>
            </div>
<?php
        }

        return;
    }

    // ==================================
    // setup uri and file path constants
    // ==================================
    define('SBWC_ORDER_CONFIRMATION_UPSELLS_RIODE_URI', plugin_dir_url(__FILE__));
    define('SBWC_ORDER_CONFIRMATION_UPSELLS_RIODE_PATH', plugin_dir_path(__FILE__));

    // register custom db table
    require_once(SBWC_ORDER_CONFIRMATION_UPSELLS_RIODE_PATH . 'includes/functions/register_db_table.php');

    // admin settings page
    require_once(SBWC_ORDER_CONFIRMATION_UPSELLS_RIODE_PATH . 'includes/functions/admin_settings_page.php');

    // render upsells
    require_once(SBWC_ORDER_CONFIRMATION_UPSELLS_RIODE_PATH . 'includes/functions/render_upsells.php');

    // register pll strings
    if(function_exists('pll_register_string')) {
        pll_register_string('sbwc-order-confirmation-upsells-riode', 'SPECIAL OFFERS FOR YOU EXPIRING IN ');
    }

    
});