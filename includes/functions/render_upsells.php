<?php

add_action('woocommerce_thankyou', function () {

    // check if Polylang exists and get current lang and upsell product ids
    if (function_exists('pll_current_language')) {
        $current_lang = pll_current_language();
        $upsell_product_ids = get_option('sbwc_ocus_product_ids_' . $current_lang);
    } else {
        $upsell_product_ids = get_option('sbwc_ocus_product_ids');
    }

    // bail if no upsell ids
    if (!$upsell_product_ids || $upsell_product_ids == '') return;

    // explode upsell product ids
    $upsell_product_ids = explode(',', $upsell_product_ids);

    // ===================
    // update impressions
    // ===================
    $impressions = get_transient('sbwc_ocus_impressions');

    // debug
    // delete_transient('sbwc_ocus_impressions');
    // return

    if (!$impressions) {
        $impressions = array();
    }

    foreach ($upsell_product_ids as $upsell_product_id) {
        $impressions[$upsell_product_id] = isset($impressions[$upsell_product_id]) ? $impressions[$upsell_product_id] + 1 : 1;
    }

    set_transient('sbwc_ocus_impressions', $impressions, DAY_IN_SECONDS);

    // debug impressions
    // echo '<pre>';
    // print_r(get_transient('sbwc_ocus_impressions'));
    // echo '</pre>';

    // debug clicks
    // $clicks_trans = get_transient('sbwc_ocus_clicks');
    // echo '<pre>';
    // print_r($clicks_trans);
    // echo '</pre>';

    $order_id = wc_get_order_id_by_order_key($_GET['key']);

    // Get the order from order key
    $order = wc_get_order($order_id);

    // Get the order products
    $order_products = $order->get_items();

    // debug
    // foreach ($order_products as $order_product) :
    //     echo '<pre>';
    //     print_r($order_product->get_data());
    //     echo '</pre>';
    // endforeach;

    // =============
    // update sales
    // =============

    // debug
    // delete_transient('sbwc_ocus_sales_data');
    // return;

    // Add order products to options table option 'sbwc_ocus_sales_data' in array format: [$order_id][] = [$product_id => $qty]
    $ocus_sales_data = get_transient('sbwc_ocus_sales_data');

    $order_id = $order->get_id();

    // if order id does not exist in $ocus_sales_data, add it
    if (!array_key_exists($order_id, $ocus_sales_data)) :

        foreach ($order_products as $order_product) {

            $product_id                   = $order_product['product_id'];
            $qty                          = $order_product['quantity'];

            $ocus_sales_data[$order_id][] = [$product_id => $qty];
        }

        set_transient('sbwc_ocus_sales_data', $ocus_sales_data);

    endif;

    // debug
    // print_r($ocus_sales_data);

    // =================================
    // bail if upsells already rendered
    // =================================

    // bail if $_SESSION['us_checkout_form'] is set
    // session_start();
    // if (isset($_SESSION['us_checkout_form'])) return;

?>

    <div id="us_cont_outer">

        <!-- countdown clock container -->
        <div id="countdown-clock" has-countdown=""></div>

        <script id="us_countdown_timer">
            // Set the date and time for the countdown to 10 minutes from now
            var countdownDate = new Date().getTime() + (10 * 60 * 1000);

            // Update the countdown every 1 second
            var countdownTimer = setInterval(function() {

                // Get the current date and time
                var now = new Date().getTime();

                // Calculate the remaining time
                var distance = countdownDate - now;

                // Calculate minutes, and seconds
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Display the countdown in the element with id "countdown-clock"
                document.getElementById("countdown-clock").innerHTML = '<span class="us_expires_in"><?php _e('SPECIAL OFFERS FOR YOU EXPIRING IN ', 'woocommerce') ?></span><span class="us_time_minutes">' + minutes + 'm</span><span class="us_time_seconds">' + seconds + "s </span>";

                // If the countdown is finished, display a message
                if (distance < 0) {
                    clearInterval(countdownTimer);
                    document.getElementById("countdown-clock").innerHTML = "EXPIRED";
                }
            }, 1000);

            // if countdown expires, remove upsells
            setTimeout(() => {
                $('#us_cont_outer').remove();
            }, 600000);
        </script>

        <!-- upsell products container -->
        <div class="us_cont">

            <?php
            // loop to render products with checkbox (flexbox, 4 col layout on desktop, 3 col layout on tablet, 2 col layout on mobile)
            foreach ($upsell_product_ids as $product_id) :

                // Get product details
                $product = wc_get_product($product_id);

                // continue if product returns false
                if (!$product) continue;

            ?>

                <!-- if is not simple or variable product, continue -->
                <?php if ($product->get_type() !== 'simple' && $product->get_type() !== 'variable') continue; ?>

                <div class="us_prod_cont">

                    <!-- inner cont -->
                    <div class="us_prod_inner_cont" product-type="<?php echo $product->get_type(); ?>">

                        <!-- image and rating container -->
                        <div class="us_prod_img_cont">
                            <a href="<?php echo get_permalink($product_id); ?>">
                                <?php echo $product->get_image(); ?>

                                <!-- if rating, display rating html, else display empty rating html -->
                                <?php if ($product->get_rating_count() > 0) : ?>
                                    <?php echo wc_get_rating_html($product->get_average_rating()); ?>
                                <?php else : ?>
                                    <?php echo wc_get_rating_html(0, 0); ?>
                                <?php endif; ?>
                            </a>
                        </div>

                        <!-- title, price and qty input (with plus and minus buttons) container -->
                        <div class="us_prod_title_price_qty_cont">
                            <a href="<?php echo get_permalink($product_id); ?>">
                                <h3><?php echo strlen($product->get_name() > 50) ? substr($product->get_name(), 0, 50) . '...' : $product->get_name(); ?></h3>
                            </a>
                            <p><?php echo $product->get_price_html(); ?></p>
                            <div class="us_prod_qty_cont">
                                <button class="us_dec_qty">-</button>
                                <input type="number" name="quantity" value="1" min="1" max="100" step="1">
                                <button class="us_inc_qty">+</button>
                            </div>
                        </div>

                        <!-- checkbox container -->
                        <div class="us_prod_checkbox_cont">
                            <input type="checkbox" name="us_checkbox" id="us_checkbox_<?php echo $product_id ?>" class="us_checkbox" qty="1" value="<?php echo $product_id; ?>" var-id="">
                        </div>

                    </div>

                    <!-- hidden button click triggered to show quickview so that client can add to cart (and I don't have to build a popup from scratch...) -->
                    <button style="display: none;" class="btn-product btn-quickview" data-product="<?php echo $product_id; ?>" title="Quick View">Quick View</button>

                </div>

            <?php

            endforeach;

            // js
            sbwc_ocus_render_js();

            // css
            sbwc_ocus_render_css(); ?>

            <div id="us_checkout_form">

            </div>

        </div>
    </div>

<?php }, 1);

/**
 * Function: render JS
 */
function sbwc_ocus_render_js()
{
?>
    <!-- js -->
    <script id="us_js_misc_qty">
        $ = jQuery.noConflict();

        jQuery(window).on('load', function() {

            // holds product id: cart item key pairs    
            var cart_item_keys = {};

            // ------------------------
            // qty plus minus on click
            // ------------------------

            // plus
            $('.us_inc_qty').click(function() {

                // get input value
                var input_val = $(this).parent().find('input').val();

                // increment input value
                input_val++;

                // set input value
                $(this).parent().find('input').val(input_val);

                // set checkbox qty attribute
                $(this).parent().parent().parent().find('input').attr('qty', input_val);

            });

            // minus
            $('.us_dec_qty').click(function() {

                // get input value
                var input_val = $(this).parent().find('input').val();

                // decrement input value
                input_val--;

                // set input value
                $(this).parent().find('input').val(input_val);

                // set checkbox qty attribute
                $(this).parent().parent().parent().find('input').attr('qty', input_val);

            });

            // --------------------------------------------------------------------------------
            // when item is added to cart, get its key and push to cart_item_keys array
            // --------------------------------------------------------------------------------
            $(document).on('added_to_cart', function(event, fragments, cart_hash, $button) {

                // create cart nonce dummy element
                let cart_nonce = $('<?php echo wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce') ?>');

                // retrieve nonce value
                let nonce = cart_nonce.val();

                //  get all checked checkbox values
                $('.us_checkbox:checked').each(function() {

                    // get product id
                    let product_id = $(this).val();

                    // get cart item key
                    let cart_item_key = $('.mini-list a[data-product_id="' + product_id + '"]').attr('data-cart_item_key');

                    // set checkbox data attribute to cart item key
                    $(this).attr('data-cart_item_key', cart_item_key);

                });

            });

            // --------------------------------------------------------------------------------
            // checkbox on click; if checked, trigger click on hidden button to show quickview
            // --------------------------------------------------------------------------------
            $('.us_checkbox').click(function() {

                // if checked
                if ($(this).is(':checked')) {

                    // Display loading spinner
                    var spinner = $('<div class="us_spinner"></div>');
                    $(this).parents('.us_prod_cont').append(spinner);

                    // get qty
                    var qty = $(this).attr('qty');

                    // Dim content
                    $(this).parents('.us_prod_inner_cont').addClass('us_dimmed');

                    // trigger click on hidden button
                    $(this).parents('.us_prod_cont').find('.btn-quickview').trigger('click');

                    // check which ajax event was triggered
                    $(document).ajaxComplete(function(event, xhr, settings) {

                        setTimeout(() => {

                            // remove spinner
                            $('.us_spinner').remove();

                            // remove dimmed class
                            $('.us_prod_inner_cont').removeClass('us_dimmed');

                            // set qty inside mfp-content form element (input with name 'quantity')
                            $('.mfp-content').find('input[name="quantity"]').val(qty);

                            // if .mfp-close exists
                            if ($(document).find('.mfp-close').length) {

                                $(document).find('.mfp-close').one('click', function() {

                                    // console.log('clicked');

                                    // retrieve product id
                                    let product_id = $(this).parents('.mfp-content').find('input[name="product_id"]').val();

                                    // set target
                                    let target = $(this).parents('.us_prod_cont').find('#us_checkbox_' + product_id);

                                    // uncheck checkbox with product id
                                    $('#us_checkbox_' + product_id).prop('checked', false);

                                });
                            }

                        }, 2000);

                    });
                }

                // if unchecked, remove item from cart
                if (!$(this).is(':checked')) {

                    // console.log('unchecked');

                    // get cart item key
                    let cart_item_key = $(this).attr('data-cart_item_key');

                    // get product id from checkbox value
                    let product_id = $(this).val();

                    // retrieve remove from cart url from cart_item_keys array
                    let remove_from_cart_url = cart_item_keys[product_id];

                    // create dummy element with remove from cart url
                    let remove_from_cart_url_dummy = $('<a href="' + remove_from_cart_url + '">Remove</a>');

                    // trigger click on dummy element
                    remove_from_cart_url_dummy.trigger('click');

                    // send ajax request to get updated checkout form
                    data = {
                        '_ajax_nonce': '<?php echo wp_create_nonce('us get checkout form') ?>',
                        'action': 'sbwc_ocus_get_co_form',
                        'cart_item_key': cart_item_key,
                    }

                    $.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {

                        // console.log(response)

                        if (response == 'Cart is currently empty.') {
                            $('#us_checkout_form').empty().hide();
                        } else {
                            $('#us_checkout_form').empty().append(response).show();
                        }

                        // trigger fragment refresh
                        $(document).trigger('wc_fragment_refresh');

                        // trigger mini cart fragment refresh
                        $(document).trigger('wc_fragments_refreshed');

                    })

                }
            });

            // ---------------------------------------------------
            // if added to cart successfully, hide .mfp-product
            // ---------------------------------------------------
            $(document).on('added_to_cart', function(event, fragments, cart_hash, $button) {

                // close popup
                $.magnificPopup.close();

                data = {
                    '_ajax_nonce': '<?php echo wp_create_nonce('us get checkout form') ?>',
                    'action': 'sbwc_ocus_get_co_form',
                    'order_id': $(document).find('#sbwc_ocus_checkout_form').attr('data-current-order') ? $(document).find('#sbwc_ocus_checkout_form').attr('data-current-order') : 'false',
                    'previous_order_key': '<?php echo $_GET['key']; ?>',
                }

                console.log(data);

                $.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {

                    // console.log(response);

                    if (response == 'Cart is currently empty.') {
                        $('#us_checkout_form').empty().hide();
                    } else {
                        $('#us_checkout_form').empty().append(response).show();
                    }

                    // trigger fragment refresh
                    $(document).trigger('wc_fragment_refresh');

                    // trigger mini cart fragment refresh
                    $(document).trigger('wc_fragments_refreshed');


                })

            });

            // ---------------------------------------------------
            // if referrer is wc checkout, uncheck all checkboxes
            // ---------------------------------------------------
            if (document.referrer == '<?php echo wc_get_checkout_url(); ?>') {

                // uncheck all checkboxes
                $('.us_checkbox').prop('checked', false);

                // remove go to cart button
                $('.us_go_to_cart_btn').remove();

            }

            // -------------------------
            // place order button click
            // -------------------------
            $(document).on('click', '#place_order', function() {
                var spinner = $('<div class="us_spinner_checkout"></div>');
                $('#us_checkout_form').append(spinner);
                $('#us_checkout_form').addClass('us_dimmed');
            });

            // -------------------------
            // register upsell clicks
            // -------------------------
            $('.us_prod_cont').on('mousedown', function() {

                // get checkbox value
                let checkbox_val = $(this).find('.us_checkbox').val();

                $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    '_ajax_nonce': '<?php echo wp_create_nonce('us register clicks') ?>',
                    'action': 'sbwc_ocus_register_clicks',
                    'product_id': checkbox_val,
                }, function(response) {
                    // debug
                    // console.log(response);
                });

            });

        });
    </script>

<?php }

/**
 * Function: render CSS
 */
function sbwc_ocus_render_css()
{ ?>
    <style>
        button#vans-riode-buy-now-btn-variable {
            display: none;
        }

        /* dimmed class */
        .us_dimmed {
            opacity: 0.5;
        }

        /* spinner */
        .us_spinner {
            position: absolute;
            top: 40%;
            left: 45%;
            transform: translate(-50%, -50%);
            border: 3px solid #f3f3f3;
            border-radius: 50%;
            border-top: 3px solid #3498db;
            width: 40px;
            height: 40px;
            -webkit-animation: spin 0.5s linear infinite;
            animation: spin 0.5s linear infinite;
            z-index: 1000;
        }

        /* spinner checkout form */
        .us_spinner_checkout {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 3px solid #f3f3f3;
            border-radius: 50%;
            border-top: 3px solid #3498db;
            width: 40px;
            height: 40px;
            -webkit-animation: spin 0.5s linear infinite;
            animation: spin 0.5s linear infinite;
            z-index: 1000;
        }

        /* flexbox for .us_cont */
        .us_cont {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 60px;
            margin-top: 30px;
        }

        /* product title cont 50% width */
        .us_prod_title_price_qty_cont {
            width: 50%;
            padding: 5px;
            position: relative;
            text-align: center;
        }

        /* product title font size 1.5rem and weight 600 */
        .us_prod_title_price_qty_cont h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 10px 0px 10px 0px;
            color: var(--rio-primary-color);
        }

        /* align price to center and make font size 1.6rem */
        .us_prod_title_price_qty_cont p {
            font-size: 2rem;
            margin: 28px 10px 0px 0px;
            color: var(--rio-secondary-color, #d26e4b);
            font-weight: 600;
        }

        .us_prod_cont {
            position: relative;
            width: 33.333%;
        }

        /* text decoration none for all links */
        .us_prod_cont a {
            text-decoration: none !important;
        }

        /* disable up and down arrows for qty input */
        .us_prod_qty_cont input::-webkit-outer-spin-button,
        .us_prod_qty_cont input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .us_prod_qty_cont {
            display: flex;
            align-items: center;
            padding-left: 13px;
            padding-bottom: 8px;
            padding-right: 10px;
            position: absolute;
            bottom: 4%;
            left: 8%;
        }

        /* input plus and minus buttons display inline; input and buttons max height 30px; buttons max width 30px */
        .us_prod_qty_cont input {
            display: inline;
            height: 35px;
            width: 60px;
            line-height: 0.6;
            border: 1px solid #ddd;
            box-shadow: none;
            background: white;
            box-sizing: border-box;
            text-align: center;
        }

        .us_prod_qty_cont button {
            display: inline;
            height: 35px;
            width: 35px;
            line-height: 0.6;
            padding: 0;
            background: #dcdcdc;
            text-align: center;
            border: none;
            cursor: pointer;
        }

        /* light background for checkbox input cont */
        .us_prod_checkbox_cont {
            background: #f8f8f8;
            width: 15%;
            padding: 0px;
            text-align: center;
            position: relative;
        }

        /* checkbox vertical align center, horizontal align center, slightly increase width and height and add small box shadow */
        .us_prod_checkbox_cont input {
            width: 20px;
            height: 20px;
            box-shadow: 0px 2px 3px lightgrey;
            position: absolute;
            top: 42%;
            left: 31%;
            cursor: pointer;
        }

        /* us_prod_cont display content flex */
        .us_prod_inner_cont {
            display: flex;
            border-radius: 5px;
            box-shadow: 0px 0px 3px lightgrey;
            margin: 10px;
            box-sizing: border-box;
            position: relative;
        }

        /* product img cont 40% width */
        .us_prod_img_cont {
            width: 35%;
            padding: 15px 0px 15px 15px;
        }

        /* product checkbox cont 10% width */
        .us_prod_checkbox_cont {
            width: 15%;
            padding: 0px;
        }

        /* h2 title display block and font-weight semi-bold, large font size */
        .us_title_cont h2 {
            font-weight: 600;
            font-size: 2rem;
            margin: 0px 0px 40px 0px;
            text-align: center;
            color: #666;
        }

        /* countdown clock text align center, large font, bright background, 15px padding top and bottom, bold text, box shadow, 40px margin bottom */
        #countdown-clock {
            text-align: center;
            font-size: 2.5rem;
            background: #f8f8f8;
            padding: 15px 0px;
            font-weight: 600;
            border: 5px dotted #e3e3e3;
            margin-bottom: 40px;
            color: #666;
            border-radius: 5px;
        }

        /* time in minutes */
        .us_time_minutes {
            background: var(--rio-alert-color);
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            margin-right: 10px;
            margin-left: 5px;
            text-shadow: none;
        }

        /* time in seconds */
        .us_time_seconds {
            background: var(--rio-alert-color);
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            text-shadow: none;
        }

        /* product img width 100% */
        .us_prod_img_cont img {
            width: 100%;
        }

        .star-rating {
            left: 5px;
            top: 6px;
        }

        .mfp-content .star-rating {
            top: 0px;
            left: 0px;
        }

        .mfp-content form {
            overflow-x: hidden;
        }

        a.button.button-primary.us_go_to_cart_btn {
            width: 100%;
            margin-top: 40px;
            border-radius: 5px;
            box-shadow: 0px 0px 4px lightgray;
        }

        .woocommerce-form-coupon-toggle {
            display: none;
        }

        div#us_checkout_form {
            background: #f8f8f8;
            padding: 30px 30px 0 30px;
            margin-top: 40px;
            border-radius: 5px;
            box-shadow: 0px 0px 3px lightgrey;
        }

        div#order_review {
            background: white;
        }

        button#pbs_bundle_atc {
            display: none;
        }

        div#us_checkout_form {
            display: none;
            position: relative;
        }

        /* 1600 */
        @media screen and (max-width: 1600px) {}

        /* 1536 */
        @media screen and (max-width: 1536px) {}

        /* 1440 */
        @media screen and (max-width: 1440px) {}

        /* 1366 */
        @media screen and (max-width: 1366px) {}

        /* 1280 */
        @media screen and (max-width: 1280px) {}

        /* 962 */
        @media screen and (max-width: 962px) {
            .us_prod_cont {
                width: 50%;
            }

            .us_prod_title_price_qty_cont h3 {
                font-size: 1.7rem;
            }

            .us_prod_title_price_qty_cont p {
                font-size: 1.8rem;
                margin: 40px 10px 0px 0px;
            }

            .us_prod_checkbox_cont input {
                left: 36%;
            }

            .us_prod_qty_cont {
                left: 15%;
            }

            .star-rating {
                left: 18px;
            }

            #countdown-clock {
                font-size: 2.2rem;
            }
        }

        /* 810 */
        @media screen and (max-width: 810px) {
            .us_prod_title_price_qty_cont h3 {
                font-size: 1.6rem;
            }

            .us_prod_title_price_qty_cont p {
                font-size: 1.7rem;
                margin: 26px 0px 0px;
            }

            .star-rating {
                left: 6px;
            }

            .us_prod_qty_cont {
                left: 7%;
            }

            .mfp-product .mfp-content,
            .mfp-product .mfp-preloader,
            .mfp-product .product {
                height: 80%;
            }


        }

        /* 800*/
        @media screen and (max-width: 800px) {}

        /* 768 */
        @media screen and (max-width: 768px) {
            .us_prod_title_price_qty_cont h3 {
                font-size: 1.5rem;
            }

            .star-rating {
                left: 0px;
            }

            .us_prod_qty_cont {
                left: 4%;
            }

            .us_prod_checkbox_cont input {
                left: 33%;
            }
        }

        /* 414 */
        @media screen and (max-width: 414px) {
            .us_prod_cont {
                width: 100%;
            }

            .star-rating {
                left: 4px;
            }

            .us_prod_qty_cont {
                left: 7%;
            }

            .mfp-product .mfp-content,
            .mfp-product .mfp-preloader,
            .mfp-product .product {
                height: initial;
            }
        }

        /* 393 */
        @media screen and (max-width: 393px) {
            .us_prod_qty_cont {
                left: 5%;
            }

            .star-rating {
                left: 2px;
            }
        }

        /* 390 */
        @media screen and (max-width: 390px) {
            .us_prod_qty_cont {
                left: 4%;
            }

            .star-rating {
                left: 0px;
            }
        }

        /* 360 */
        @media screen and (max-width: 360px) {
            .star-rating {
                left: -5px;
            }

            .us_prod_qty_cont input {
                width: 45px;
            }

            .us_prod_qty_cont {
                bottom: 0;
            }
        }

        /* 328 */
        @media screen and (max-width: 328px) {
            .us_cont {
                margin-left: -15px;
                margin-right: -15px;
            }
        }
    </style>
<?php }


/**
 * Register clicks
 */
add_action('wp_ajax_sbwc_ocus_register_clicks', 'sbwc_ocus_register_clicks');
add_action('wp_ajax_nopriv_sbwc_ocus_register_clicks', 'sbwc_ocus_register_clicks');

function sbwc_ocus_register_clicks()
{

    check_ajax_referer('us register clicks', '_ajax_nonce');

    // get product id
    $product_id = $_POST['product_id'];

    // get clicks
    $clicks = get_transient('sbwc_ocus_clicks');

    // if no clicks, set to empty array
    if (!$clicks) {
        $clicks = array();
    }

    // if product id does not exist in $clicks, set to 0
    if (!isset($clicks[$product_id])) {
        $clicks[$product_id] = 0;
    }

    // increment clicks
    $clicks[$product_id]++;

    // set transient
    $click_transient_set =  set_transient('sbwc_ocus_clicks', $clicks, DAY_IN_SECONDS);

    if ($click_transient_set) :
        wp_send_json_success(['clicks transient set' => get_transient('sbwc_ocus_clicks')]);
    else :
        wp_send_json('clicks transient not set');
    endif;
}

/**
 * Fetch and return checkout form
 */
add_action('wp_ajax_sbwc_ocus_get_co_form', 'sbwc_ocus_get_co_form');
add_action('wp_ajax_nopriv_sbwc_ocus_get_co_form', 'sbwc_ocus_get_co_form');

function sbwc_ocus_get_co_form()
{

    check_ajax_referer('us get checkout form', '_ajax_nonce');

    $cart = WC()->cart;

    // if is $_POST['cart_item_key'], remove item from cart
    if ($_POST['cart_item_key']) {

        // remove item from cart
        $cart->remove_cart_item($_POST['cart_item_key']);

        // calculate totals
        $cart->calculate_totals();
    }

    // add flag $_SESSION['us_checkout_form'] to session (used to determine whether user has already been offered upsells)
    session_start();
    $_SESSION['us_checkout_form'] = true;

    // init checkout
    $checkout = WC()->checkout();

    do_action('woocommerce_before_checkout_form', $checkout);

    // If checkout registration is disabled and not logged in, the user cannot checkout.
    if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
        echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
        return;
    }

?>

    <form id="sbwc_ocus_checkout_form" name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

        <?php if ($checkout->get_checkout_fields()) : ?>

            <?php do_action('woocommerce_checkout_before_customer_details'); ?>

            <div class="col2-set" id="customer_details" style="display: none;">
                <div class="col-1">
                    <?php do_action('woocommerce_checkout_billing'); ?>
                </div>

                <div class="col-2">
                    <?php do_action('woocommerce_checkout_shipping'); ?>
                </div>
            </div>

            <?php do_action('woocommerce_checkout_after_customer_details'); ?>

        <?php endif; ?>

        <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

        <h3 id="order_review_heading"><?php esc_html_e('Your order', 'woocommerce'); ?></h3>

        <?php do_action('woocommerce_checkout_before_order_review'); ?>

        <div id="order_review" class="woocommerce-checkout-review-order">
            <?php do_action('woocommerce_checkout_order_review'); ?>
        </div>

        <?php do_action('woocommerce_checkout_after_order_review'); ?>

    </form>

<?php do_action('woocommerce_after_checkout_form', $checkout);

    wp_die();
}
