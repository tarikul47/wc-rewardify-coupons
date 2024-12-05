<?php
if (!defined('ABSPATH'))
    exit;

class WC_Email_Rewardify_Coupon extends WC_Email
{
    public function __construct()
    {
        $this->id = 'rewardify_coupon';
        $this->title = 'Rewardify Coupon';
        $this->description = 'Send a unique coupon code to users who purchase donation products.';
        $this->heading = 'Your Exclusive Reward Coupon';
        $this->subject = 'Here is your Reward Coupon!';
        $this->template_html = 'emails/rewardify-coupon.php';

        add_action('woocommerce_rewardify_coupon_notification', [$this, 'trigger'], 10, 2);

        parent::__construct();

        $this->recipient = '';
    }

    public function trigger($order_id, $coupon_code)
    {
        $this->object = wc_get_order($order_id);
        $this->recipient = $this->object->get_billing_email();
        $this->coupon_code = $coupon_code;

        if ($this->is_enabled() && $this->get_recipient()) {
            $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
        } else {
        }
    }


    public function get_content_html()
    {
        return wc_get_template_html(
            'emails/rewardify-coupon.php', // Template file name
            [
                'order' => $this->object,
                'coupon_code' => $this->coupon_code,
                'email_heading' => $this->get_heading(),
                'email' => $this
            ],
            '', // Default WooCommerce override path
            RC_PLUGIN_DIR . 'templates/' // Path to your custom templates
        );
    }
}
