<?php

add_action('woocommerce_thankyou', function () {

    // check if Polylang exists and get current lang and upsell product ids
    if (function_exists('pll_current_language')) {
        $current_lang = pll_current_language();
        $upsell_product_ids = get_option('sbwc_order_confirmation_upsells_riode_product_ids_' . $current_lang);
    }else{
        $upsell_product_ids = get_option('sbwc_order_confirmation_upsells_riode_product_ids');
    }

    // bail if no upsell ids
    if (!$upsell_product_ids || $upsell_product_ids == '') return;

    // debug
    // <pre>
    // print_r($upsell_product_ids);
    // </pre>

    // explode
    $upsell_product_ids = explode(',', $upsell_product_ids); ?>

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
            $product = wc_get_product($product_id); ?>

            <!-- if is not simple or variable product, continue -->
            <?php if ($product->get_type() !== 'simple' && $product->get_type() !== 'variable') continue; ?>

            <div class="us_prod_cont">

                <!-- inner cont -->
                <div class="us_prod_inner_cont" product-type="<?php echo $product->get_type(); ?>">

                    <!-- image and rating container -->
                    <div class="us_prod_img_cont">
                        <a href="<?php echo get_permalink($product_id); ?>">
                            <?php echo $product->get_image(); ?>
                            <?php echo wc_get_rating_html($product->get_average_rating()); ?>
                        </a>
                    </div>

                    <!-- title, price and qty input (with plus and minus buttons) container -->
                    <div class="us_prod_title_price_qty_cont">
                        <a href="<?php echo get_permalink($product_id); ?>">
                            <h3><?php echo substr($product->get_name(), 0, 25) . '...'; ?></h3>
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
                        <input type="checkbox" name="us_checkbox" id="us_checkbox" qty="1" value="<?php echo $product_id; ?>" var-id="">
                    </div>

                </div>

            </div>

            <!-- if is variable, create lightbox with product shortcode -->
            <?php if ($product->get_type() === 'variable') : ?>

                <!-- lightbox -->
                <div id="us_lightbox_<?php echo $product_id; ?>" class="us_lightbox">

                    <!-- lightbox content -->
                    <div class="us_lightbox_content">

                        <!-- close button -->
                        <span class="us_close">&times;</span>

                        <!-- product shortcode -->
                        <?php echo do_shortcode('[product_page id="' . $product_id . '"]'); ?>

                    </div>

                </div>

            <?php endif; ?>

        <?php endforeach; ?>

        <!-- js -->
        <script>
            jQuery(document).ready(function($) {
                
                // ********************
                // plus minus on click
                // ********************
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

            });
        </script>

        <style>
            /* lightbox initially display none */
            .us_lightbox {
                display: none;
                position: fixed;
                z-index: 9999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0, 0, 0, 0.4);
            }

            /* lightbox content */
            .us_lightbox_content {
                background-color: #fefefe;
                margin: 10% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 80%;
                height: 80%;
                overflow: auto;
            }

            /* flexbox for .us_cont */
            .us_cont {
                display: flex;
                flex-wrap: wrap;
                margin-bottom: 60px;
                margin-top: 30px;
            }

            /* product title font size 1.5rem and weight 600 */
            .us_prod_title_price_qty_cont h3 {
                font-size: 1.1rem;
                font-weight: 600;
                margin: 0px 0px 10px 0px;
                color: #666;
            }

            /* text decoration none for all links */
            .us_prod_cont a {
                text-decoration: none !important;
            }

            /* align price to center and make font size 1.6rem */
            .us_prod_title_price_qty_cont p {
                text-align: center;
                font-size: 1.2rem;
                margin: 0px 0px 10px 0px;
                color: #666;
            }

            /* disable up and down arrows for qty input */
            .us_prod_qty_cont input::-webkit-outer-spin-button,
            .us_prod_qty_cont input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            /* qty input max width 50px */
            .us_prod_qty_cont input {
                max-width: 50px;
                text-align: center;
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
            }

            /* product img cont 40% width */
            .us_prod_img_cont {
                width: 40%;
                padding: 15px;
            }


            /* product title cont 50% width */
            .us_prod_title_price_qty_cont {
                width: 45%;
                padding: 5px;
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

            .us_prod_qty_cont {
                display: flex;
                align-items: center;
                padding-left: 13px;
                padding-bottom: 8px;
            }

            /* input plus and minus buttons display inline; input and buttons max height 30px; buttons max width 30px */
            .us_prod_qty_cont input {
                display: inline;
                height: 30px;
                width: 60px;
                line-height: 0.6;
                border: 1px solid #ddd;
                box-shadow: none;
                background: white;
                box-sizing: border-box;
            }

            .us_prod_qty_cont button {
                display: inline;
                height: 30px;
                width: 30px;
                line-height: 0.6;
                padding: 0;
                background: #dcdcdc;
                text-align: center;
            }

            /* countdown clock text align center, large font, bright background, 15px padding top and bottom, bold text, box shadow, 40px margin bottom */
            #countdown-clock {
                text-align: center;
                font-size: 2rem;
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

            @media (min-width: 1200px) {
                .us_prod_cont {
                    width: 33.33%;
                }
            }

            @media (min-width: 768px) and (max-width: 1199px) {
                .us_prod_cont {
                    width: 50%;
                }
            }

            @media (max-width: 767px) {
                .us_prod_cont {
                    width: 100%;
                }
            }
        </style>

    </div>

<?php });




?>