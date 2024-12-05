<?php
/**
 * Plugin Name: Rewardify Coupons
 * Description: Automatically generates and sends unique coupons as rewards for qualifying donation purchases.
 * Version: 1.0
 * Author: Tarikul Islam
 * Author URI: mailto:tarikul47@gmail.com
 * Requires Plugins: WooCommerce, woo-donations-pro
 * License: GPL2
 */

if (!defined('ABSPATH'))
    exit;

class RewardifyCoupons
{
    public function __construct()
    {
        // Ensure WooCommerce and Donation Plugin are active
        if (!$this->check_dependencies()) {
            add_action('admin_notices', [$this, 'dependency_notice']);
            return;
        }

        define('RC_PLUGIN_DIR', plugin_dir_path(__FILE__));

        // Load custom email
        add_filter('woocommerce_email_classes', [$this, 'register_coupon_email']);

        // Hook into order completed action
        add_action('woocommerce_order_status_completed', [$this, 'process_donation_order']);

        // Include the custom fields class
        require_once RC_PLUGIN_DIR . 'includes/class-rewardify-custom-fields.php';

        // Initialize the class
        new Rewardify_Custom_Fields();

    }

    private function check_dependencies()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        return is_plugin_active('woocommerce/woocommerce.php') && is_plugin_active('woo-donations-pro/woo-donations-pro.php');
    }

    public function dependency_notice()
    {
        echo '<div class="error"><p><strong>Rewardify Coupons</strong> requires WooCommerce and the Woo Donations Pro plugin to be active.</p></div>';
    }

    public function register_coupon_email($email_classes)
    {
        require_once RC_PLUGIN_DIR . 'includes/class-wc-email-rewardify-coupon.php';
        $email_classes['WC_Email_Rewardify_Coupon'] = new WC_Email_Rewardify_Coupon();
        return $email_classes;
    }


    public function process_donation_order($order_id)
    {
        $order = wc_get_order($order_id);

        // Define qualifying donation products
        $donation_products = [13]; // Replace with actual product IDs

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $product_price = $item->get_total();

            // Check if product is donatable and price >= 500
            if (in_array($product_id, $donation_products) && $product_price >= 500) {

                $user_email = $order->get_billing_email();
                $coupon_code = $this->create_unique_coupon($user_email);

                $mailer = WC()->mailer();

                // Trigger the custom email
                do_action('woocommerce_rewardify_coupon_notification', $order_id, $coupon_code);
                break;
            }
        }
    }

    private function create_unique_coupon($user_email)
    {
        $coupon_code = strtoupper(wp_generate_password(8, false)); // Generate a unique code

        $coupon = new WC_Coupon();
        $coupon->set_code($coupon_code);
        $coupon->set_discount_type('percent'); // Set discount type to percent for 100% discount
        $coupon->set_amount(100); // Set discount amount to 100%
        $coupon->set_individual_use(true); // Restrict individual use
        $coupon->set_usage_limit(1); // Allow only one use
        $coupon->set_email_restrictions([$user_email]); // Restrict to the current user's email

        $coupon->save(); // Save the coupon in WooCommerce

        return $coupon_code;
    }
}

new RewardifyCoupons();
