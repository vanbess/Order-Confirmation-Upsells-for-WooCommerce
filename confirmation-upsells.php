<?php

/**
 * Plugin Name: SBWC Order Confirmation Page Upsells Riode
 * Plugin URI: https://silverbackev.co.za/
 * Description: This plugin adds upsells to the order confirmation page no websites which uses the Riode theme.
 * Version: 1.0.0
 * Author: Your Name
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

// activation hook
require_once(SBWC_ORDER_CONFIRMATION_UPSELLS_RIODE_PATH . 'includes/functions/activation_hook.php');

// admin settings page
require_once(SBWC_ORDER_CONFIRMATION_UPSELLS_RIODE_PATH . 'includes/functions/admin_settings_page.php');

// admin tracking page
require_once(SBWC_ORDER_CONFIRMATION_UPSELLS_RIODE_PATH . 'includes/functions/admin_tracking_page.php');

// tracking update impressions
require_once(SBWC_ORDER_CONFIRMATION_UPSELLS_RIODE_PATH . 'includes/functions/tracking_update_impressions.php');

// tracking update clicks
require_once(SBWC_ORDER_CONFIRMATION_UPSELLS_RIODE_PATH . 'includes/functions/tracking_update_clicks.php');

// update tracking via action scheduler
require_once(SBWC_ORDER_CONFIRMATION_UPSELLS_RIODE_PATH . 'includes/functions/as_update_tracking.php');

// render upsells
require_once(SBWC_ORDER_CONFIRMATION_UPSELLS_RIODE_PATH . 'includes/functions/render_upsells.php');