<?php

if (!defined('ABSPATH')) {
    exit;
}

class Rewardify_Custom_Fields
{
    public $specific_product_id;

    public function __construct()
    {
        $this->specific_product_id = 36;
        // Hooks
        add_action('woocommerce_before_add_to_cart_button', [$this, 'add_custom_fields_to_product']);
        add_filter('woocommerce_add_cart_item_data', [$this, 'save_custom_fields_to_cart'], 10, 2);
        add_filter('woocommerce_get_item_data', [$this, 'display_custom_fields_in_cart'], 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'save_custom_fields_to_order'], 10, 4);
    }

    // Add custom fields to specific product page
    public function add_custom_fields_to_product()
    {
        global $product;

        if ($product->get_id() == $this->specific_product_id) {
            echo '<div class="custom-product-fields">';
            echo '<label for="rc_text_field">Enter your text:</label>';
            echo '<input type="text" id="rc_text_field" name="rc_text_field" value="" class="input-text" />';

            echo '<label for="rc_textarea_field">Enter your message:</label>';
            echo '<textarea id="rc_textarea_field" name="rc_textarea_field" rows="4" class="input-textarea"></textarea>';
            echo '</div>';
        }
    }

    // Validate custom fields
    public function validate_custom_product_fields($passed, $product_id, $quantity)
    {
        if ($product_id == $this->specific_product_id) {
            if (empty($_POST['rc_text_field'])) {
                wc_add_notice('Please fill in the text field.', 'error');
                $passed = false;
            }

            if (empty($_POST['rc_textarea_field'])) {
                wc_add_notice('Please fill in the message field.', 'error');
                $passed = false;
            }
        }

        return $passed;
    }

    // Save custom fields to cart item data
    public function save_custom_fields_to_cart($cart_item_data, $product_id)
    {
        if ($product_id == $this->specific_product_id) {
            if (isset($_POST['rc_text_field'])) {
                $cart_item_data['rc_text_field'] = sanitize_text_field($_POST['rc_text_field']);
            }

            if (isset($_POST['rc_textarea_field'])) {
                $cart_item_data['rc_textarea_field'] = sanitize_textarea_field($_POST['rc_textarea_field']);
            }
        }

        return $cart_item_data;
    }

    // Display custom fields in cart
    public function display_custom_fields_in_cart($item_data, $cart_item)
    {
        if (isset($cart_item['rc_text_field'])) {
            $item_data[] = [
                'key' => 'Custom Text',
                'value' => $cart_item['rc_text_field']
            ];
        }

        if (isset($cart_item['rc_textarea_field'])) {
            $item_data[] = [
                'key' => 'Custom Message',
                'value' => $cart_item['rc_textarea_field']
            ];
        }

        return $item_data;
    }

    // Save custom fields to order meta
    public function save_custom_fields_to_order($item, $cart_item_key, $values, $order)
    {
        if (isset($values['rc_text_field'])) {
            $item->add_meta_data('Custom Text', $values['rc_text_field']);
        }

        if (isset($values['rc_textarea_field'])) {
            $item->add_meta_data('Custom Message', $values['rc_textarea_field']);
        }
    }
}
