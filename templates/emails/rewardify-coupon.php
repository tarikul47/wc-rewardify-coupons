<?php
if (!defined('ABSPATH'))
    exit;
?>

<?php do_action('woocommerce_email_header', $email_heading, $email); ?>
<p>
    <?php echo sprintf(__('Hi %s,', 'woocommerce'), $order->get_billing_first_name()); ?>
</p>
<p>
    <?php _e('Thank you for your generous donation! As a token of our appreciation, here is your exclusive reward coupon:', 'woocommerce'); ?>
</p>
<h2>
    <?php echo esc_html($coupon_code); ?>
</h2>
<p>
    <?php _e('Use this coupon on your next purchase to enjoy a 100% discount. Thank you for supporting us!', 'woocommerce'); ?>
</p>

<?php do_action('woocommerce_email_footer', $email); ?>