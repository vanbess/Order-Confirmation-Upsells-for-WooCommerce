<?php

add_action('woocommerce_thankyou', function () {

    // check if Polylang exists and get current lang and upsell product ids
    if (function_exists('pll_current_language')) {
        $current_lang = pll_current_language();
        $upsell_product_ids = get_option('sbwc_order_confirmation_upsells_riode_product_ids_' . $current_lang);
    } else {
        $upsell_product_ids = get_option('sbwc_order_confirmation_upsells_riode_product_ids');
    }

    // bail if no upsell ids
    if (!$upsell_product_ids || $upsell_product_ids == '') return;

    //debug
    // echo '<pre>';
    // print_r($upsell_product_ids);
    // echo '</pre>';

    // explode
    $upsell_product_ids = explode(',', $upsell_product_ids); ?>

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

            endforeach; ?>

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

                            // if product id and cart key not in cart_item_keys array, push product id and cart item key to cart_item_keys array
                            if (!cart_item_keys[product_id] && !cart_item_keys[cart_item_key]) {
                                // cart_item_keys[product_id] = cart_item_key;
                                cart_item_keys[product_id] = window.location.href + '?remove_item=' + cart_item_key + '&_wpnonce=' + nonce;
                            }

                        });

                        // debug
                        console.log(cart_item_keys);


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

                            console.log('unchecked');

                            // get product id from checkbox value
                            let product_id = $(this).val();

                            // retrieve remove from cart url from cart_item_keys array
                            let remove_from_cart_url = cart_item_keys[product_id];

                            // create dummy element with remove from cart url
                            let remove_from_cart_url_dummy = $('<a href="' + remove_from_cart_url + '">Remove</a>');

                            // trigger click on dummy element
                            remove_from_cart_url_dummy.trigger('click');

                            // trigger fragment refresh
                            $(document).trigger('wc_fragment_refresh');

                        }
                    });

                    // ---------------------------------------------------
                    // if added to cart successfully, hide .mfp-product
                    // ---------------------------------------------------
                    $(document).on('added_to_cart', function(event, fragments, cart_hash, $button) {

                        // close popup
                        $.magnificPopup.close();

                        // if any checkbox is checked, append go to cart button to upsell container
                        if ($('.us_checkbox:checked').length) {

                            // if go to cart button does not exist
                            if (!$('.us_go_to_cart_btn').length) {

                                // append go to cart button
                                $('.us_cont').append('<a href="<?php echo wc_get_checkout_url(); ?>" class="button button-primary us_go_to_cart_btn"><?php _e('Go to Checkout', 'woocommerce'); ?></a>');

                            }
                        }

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


                });
            </script>

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
                    background: var(--wp--preset--color--vivid-red);
                    padding: 5px 10px;
                    border-radius: 5px;
                    color: white;
                    margin-right: 10px;
                    margin-left: 5px;
                    text-shadow: none;
                }

                /* time in seconds */
                .us_time_seconds {
                    background: var(--wp--preset--color--vivid-red);
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

                /* 1600 */
                @media screen and (max-width: 1600px) {
                }

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
                    .us_cont{
                        margin-left: -15px;
                        margin-right: -15px;
                    }
                }

            </style>

        </div>
    </div>

<?php });




?>