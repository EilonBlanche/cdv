<?php

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}

if ( function_exists( 'wc' ) ) {
    class XT_Woo_Floating_Cart_AJAX
    {
        /**
        - Hook in ajax handlers.
        */
        public static function init()
        {
            add_filter(
                'woocommerce_add_to_cart_fragments',
                array( __CLASS__, 'cart_fragments' ),
                1,
                1
            );
            add_filter(
                'woocommerce_update_order_review_fragments',
                array( __CLASS__, 'cart_fragments' ),
                1,
                1
            );
            add_filter(
                'woocommerce_remove_cart_item',
                array( __CLASS__, 'remove_cart_item' ),
                10,
                2
            );
            add_filter(
                'woocommerce_cart_item_restored',
                array( __CLASS__, 'cart_item_restored' ),
                10,
                2
            );
            self::add_ajax_events();
        }
        
        /**
        - Add custom ajax events here
        */
        public static function add_ajax_events()
        {
            // woocommerce_EVENT => nopriv
            $ajax_events = array(
                'xt_woofc_update_cart' => true,
            );
            foreach ( $ajax_events as $ajax_event => $nopriv ) {
                add_action( 'wp_ajax_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
                
                if ( $nopriv ) {
                    add_action( 'wp_ajax_nopriv_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
                    // WC AJAX can be used for frontend ajax requests
                    add_action( 'wc_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
                }
            
            }
        }
        
        public static function cart_fragments( $fragments )
        {
            WC()->cart->calculate_totals();
            $type = ( !empty($_POST['type']) ? $_POST['type'] : null );
            $refresh_fragments = !empty($_GET['wc-ajax']) && ($_GET['wc-ajax'] === 'get_refreshed_fragments' || $_GET['wc-ajax'] === 'get_refreshed_fragments');
            $add_to_cart = !empty($_GET['wc-ajax']) && $_GET['wc-ajax'] === 'add_to_cart';
            $update_order_review = !empty($_GET['wc-ajax']) && $_GET['wc-ajax'] === 'update_order_review';
            $coupon_action = !empty($_GET['wc-ajax']) && ($_GET['wc-ajax'] === 'xt_woofc_apply_coupon' || $_GET['wc-ajax'] === 'xt_woofc_remove_coupon');
            $notices_selector = ( $coupon_action || $update_order_review || $refresh_fragments ? 'xt_woofc-notices-wrapper' : 'woocommerce-notices-wrapper' );
            $total = xt_woofc_checkout_total();
            $count = WC()->cart->get_cart_contents_count();
            // get notices
            ob_start();
            wc_print_notices();
            $notices = ob_get_clean();
            $fragments["." . $notices_selector] = '<div class="' . esc_attr( $notices_selector ) . '">' . $notices . '</div>';
            
            if ( !in_array( $type, array(
                'remove',
                'undo',
                'update',
                'totals'
            ) ) && !$update_order_review && !$coupon_action || $add_to_cart ) {
                $list = xt_woo_floating_cart_template( 'parts/cart/list', array(), true );
                $fragments['.xt_woofc-list'] = $list;
            } else {
                
                if ( in_array( $type, array( 'update' ) ) && !empty($_POST['cart_item_key']) ) {
                    $cart_item_key = sanitize_text_field( $_POST['cart_item_key'] );
                    $cart_item = WC()->cart->cart_contents[$cart_item_key];
                    $product = xt_woofc_item_product( $cart_item, $cart_item_key );
                    $vars = array(
                        'cart_item_key' => $cart_item_key,
                        'cart_item'     => $cart_item,
                        'product'       => $product,
                    );
                    $price = xt_woo_floating_cart_template( 'parts/cart/list/product/price', $vars, true );
                    $quantity = xt_woo_floating_cart_template( 'parts/cart/list/product/quantity', $vars, true );
                    $fragments['li[data-key="' . $cart_item_key . '"] .xt_woofc-price'] = $price;
                    $fragments['li[data-key="' . $cart_item_key . '"] .xt_woofc-quantity'] = $quantity;
                }
            
            }
            
            $fragments['.xt_woofc-checkout span.amount'] = '<span class="amount">' . $total . '</span>';
            $fragments['.xt_woofc-count li:nth-child(1)'] = '<li>' . $count . '</li>';
            $fragments['.xt_woofc-count li:nth-child(2)'] = '<li>' . ($count + 1) . '</li>';
            
            if ( xt_woo_floating_cart()->fs()->can_use_premium_code__premium_only() && xt_woofc_option_bool( 'cart_checkout_form', false ) || xt_woofc_option_bool( 'enable_totals', false ) ) {
                ob_start();
                xt_woo_floating_cart()->frontend()->render_totals__premium_only();
                $fragments['.xt_woofc-body .xt_woofc-cart-totals'] = ob_get_clean();
            }
            
            return $fragments;
        }
        
        /**
         * Update floating cart
         */
        public static function xt_woofc_update_cart()
        {
            $type = filter_var( $_POST['type'], FILTER_SANITIZE_STRING );
            $cart_item_key = null;
            if ( !empty($_POST['cart_item_key']) ) {
                $cart_item_key = filter_var( $_POST['cart_item_key'], FILTER_SANITIZE_STRING );
            }
            
            if ( $type == 'update' && !empty($cart_item_key) ) {
                $cart_item_qty = intval( $_POST['cart_item_qty'] );
                WC()->cart->set_quantity( $cart_item_key, $cart_item_qty, true );
            } else {
                
                if ( $type == 'remove' && !empty($cart_item_key) ) {
                    WC()->cart->remove_cart_item( $cart_item_key );
                } else {
                    if ( $type == 'undo' && !empty($cart_item_key) ) {
                        WC()->cart->restore_cart_item( $cart_item_key );
                    }
                }
            
            }
            
            WC_Ajax::get_refreshed_fragments();
        }
        
        /**
         * AJAX apply coupon on checkout page.
         */
        public static function xt_woofc_apply_coupon()
        {
            
            if ( !empty($_POST['coupon_code']) ) {
                WC()->cart->add_discount( wc_format_coupon_code( wp_unslash( $_POST['coupon_code'] ) ) );
            } else {
                wc_add_notice( WC_Coupon::get_generic_coupon_error( WC_Coupon::E_WC_COUPON_PLEASE_ENTER ), 'error' );
            }
            
            WC_Ajax::get_refreshed_fragments();
        }
        
        /**
         * AJAX remove coupon on cart and checkout page.
         */
        public static function xt_woofc_remove_coupon()
        {
            $coupon = ( isset( $_POST['coupon'] ) ? wc_format_coupon_code( wp_unslash( $_POST['coupon'] ) ) : false );
            
            if ( empty($coupon) ) {
                wc_add_notice( __( 'Sorry there was a problem removing this coupon.', 'woocommerce' ), 'error' );
            } else {
                WC()->cart->remove_coupon( $coupon );
                wc_add_notice( __( 'Coupon has been removed.', 'woocommerce' ) );
            }
            
            WC_Ajax::get_refreshed_fragments();
        }
        
        /**
         * AJAX update shipping method on cart page.
         * Override native function because the nonce check is failing if caching plugin enabled
         */
        public static function update_shipping_method()
        {
            //check_ajax_referer( 'update-shipping-method', 'security' );
            wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );
            $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
            $posted_shipping_methods = ( isset( $_POST['shipping_method'] ) ? wc_clean( wp_unslash( $_POST['shipping_method'] ) ) : array() );
            if ( is_array( $posted_shipping_methods ) ) {
                foreach ( $posted_shipping_methods as $i => $value ) {
                    $chosen_shipping_methods[$i] = $value;
                }
            }
            WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
            WC_Ajax::get_refreshed_fragments();
        }
        
        public static function remove_cart_item( $cart_item_key, $cart )
        {
            $position = array_search( $cart_item_key, array_keys( $cart->cart_contents ) );
            WC()->session->set( 'xt_woofc_removed_position', $position );
        }
        
        public static function cart_item_restored( $cart_item_key, $cart )
        {
            $bundled_product = function_exists( 'wc_pb_is_bundled_cart_item' );
            
            if ( !$bundled_product ) {
                $position = WC()->session->get( 'xt_woofc_removed_position' );
                $restored_item = $cart->cart_contents[$cart_item_key];
                array_splice(
                    $cart->cart_contents,
                    $position,
                    0,
                    array( $restored_item )
                );
                $cart->cart_contents = self::replace_array_key( $cart->cart_contents, "0", $cart_item_key );
            }
            
            WC()->session->__unset( 'xt_woofc_removed_position' );
        }
        
        public static function replace_array_key( $arr, $oldkey, $newkey )
        {
            
            if ( array_key_exists( $oldkey, $arr ) ) {
                $keys = array_keys( $arr );
                $keys[array_search( $oldkey, $keys )] = $newkey;
                return array_combine( $keys, $arr );
            }
            
            return $arr;
        }
    
    }
    XT_Woo_Floating_Cart_AJAX::init();
}
