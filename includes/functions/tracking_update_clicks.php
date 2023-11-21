<?php
// =================================
//  save clicks for upsell products
// =================================
add_action('wp_footer', function () {

    // check if is order confirmation page
    if (!is_order_received_page()) {
        return;
    } ?>

    <script>
        jQuery(document).ready(function($) {

            // get upsell product ids
            var upsell_ids = '<?php echo get_option('sbwc_order_confirmation_upsells_riode_product_ids'); ?>';
            upsell_ids = upsell_ids.split(',');

            // loop through upsell product ids
            upsell_ids.forEach(function(upsell_id) {

                // get upsell product element
                var upsell_product = $('.woocommerce-order-again').find('[data-product_id="' + upsell_id + '"]');

                // check if upsell product exists
                if (upsell_product.length) {

                    // add click event listener
                    upsell_product.on('click', function() {

                        // ajax request to save clicks
                        $.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'POST',
                            data: {
                                action: 'sbwc_order_confirmation_upsells_riode_save_clicks',
                                product_id: upsell_id,
                                nonce: '<?php echo wp_create_nonce('sbwc_order_confirmation_upsells_riode_save_clicks'); ?>'
                            }
                        });
                    });
                }
            });

        });
    </script>


<?php });

// =================================
//  save clicks for upsell products
// =================================
add_action('wp_ajax_sbwc_order_confirmation_upsells_riode_save_clicks', 'sbwc_order_confirmation_upsells_riode_save_clicks');
add_action('wp_ajax_nopriv_sbwc_order_confirmation_upsells_riode_save_clicks', 'sbwc_order_confirmation_upsells_riode_save_clicks');

function sbwc_order_confirmation_upsells_riode_save_clicks()
{

    // check nonce and die silently if invalid
    if (!wp_verify_nonce($_POST['nonce'], 'sbwc_order_confirmation_upsells_riode_save_clicks')) {
        die;
    }

    // get product id
    $product_id = $_POST['product_id'];

    $clicks = get_transient('sbwc_order_confirmation_upsells_riode_clicks');

    if (!$clicks) {
        $clicks = array();
    }

    if (!isset($clicks[$product_id])) {
        $clicks[$product_id] = 0;
    }

    $clicks[$product_id]++;

    set_transient('sbwc_order_confirmation_upsells_riode_clicks', $clicks, DAY_IN_SECONDS);
}
?>