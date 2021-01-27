<?php
/**
 * Functions.php
 *
 * @package  Theme_Customisations
 * @author   WooThemes
 * @since    1.0.0
 */

if (!defined('ABSPATH')) {
    exit(); // Exit if accessed directly.
}

/**
 * functions.php
 * Add PHP snippets here
 */

/**
 * Additional fields for products
 */

add_action(
    'woocommerce_product_options_general_product_data',
    'woocommerce_product_custom_fields'
);
add_action(
    'woocommerce_process_product_meta',
    'woocommerce_product_custom_fields_save'
);

function woocommerce_product_custom_fields()
{
    global $woocommerce, $post;
    echo '<div class=" product_custom_text ">';
    woocommerce_wp_text_input([
        'id' => '_custom_product_text',
        'label' => __('Additional data input field name', 'woocommerce'),
        'placeholder' => 'Jakis tekst',
        'desc_tip' => 'true',
    ]);
    echo '</div>';
}

function woocommerce_product_custom_fields_save($post_id)
{
    $woocommerce_custom_product_text = $_POST['_custom_product_text'];
    if (!empty($woocommerce_custom_product_text)) {
        update_post_meta(
            $post_id,
            '_custom_product_text',
            esc_attr($woocommerce_custom_product_text)
        );
    }
}

function hook_custom_fields_into_view()
{
    global $post;
    echo get_post_meta($post->ID, '_custom_product_text', true);
}
add_action(
    'woocommerce_single_product_summary',
    'hook_custom_fields_into_view',
    45
);

/**
 * Input field for product custom text
 */

// ADMIN: Add checkbox in product panel to select if creating custom text for product should be available
function woocommerce_product_custom_text_input()
{
    $args = [
        'id' => 'woocommerce_custom_text_admin_checkbox',
        'label' => __('Allow custom text?', 'cwoa'),
    ];
    woocommerce_wp_checkbox($args);
}

add_action(
    'woocommerce_product_options_general_product_data',
    'woocommerce_product_custom_text_input'
);

// ADMIN: Save information about allowing creating custom text for product (or not)
function save_woocommerce_product_custom_text_input($post_id)
{
    $product = wc_get_product($post_id);
    $woocommerce_custom_product_text_allowed = isset(
        $_POST['woocommerce_custom_text_admin_checkbox']
    )
        ? $_POST['woocommerce_custom_text_admin_checkbox']
        : false;
    $product->update_meta_data(
        'woocommerce_custom_text_admin_checkbox',
        $woocommerce_custom_product_text_allowed
    );
    $product->save();
}
add_action(
    'woocommerce_process_product_meta',
    'save_woocommerce_product_custom_text_input'
);

// PRODUCT: Show custom text input field if creating custom text is allowed
function woocommerce_product_custom_text_input_display()
{
    global $post;
    $product = wc_get_product($post->ID);
    $woocommerce_custom_product_text_allowed = $product->get_meta(
        'woocommerce_custom_text_admin_checkbox'
    );
    if ($woocommerce_custom_product_text_allowed) {
        printf(
            '<div><label>%s</label><input type="text" id="woocommerce_product_custom_text_input" name="woocommerce_product_custom_text_input" value=""></div>',
            esc_html("Wpisz własny tekst")
        );
    }
}

add_action(
    'woocommerce_before_add_to_cart_button',
    'woocommerce_product_custom_text_input_display'
);

// PRODUCT/CART/CHECKOUT - Save custom text value inputted by user
function woocommerce_add_custom_text_to_cart_item(
    $cart_item_data,
    $product_id,
    $variation_id
) {
    $product_custom_text = filter_input(
        INPUT_POST,
        'woocommerce_product_custom_text_input'
    );

    if (empty($product_custom_text)) {
        return $cart_item_data;
    }

    $cart_item_data['product_custom_text'] = $product_custom_text;

    return $cart_item_data;
}

add_filter(
    'woocommerce_add_cart_item_data',
    'woocommerce_add_custom_text_to_cart_item',
    10,
    3
);

// CART: Display custom text in cart
function woocommerce_display_custom_text_cart($item_data, $cart_item)
{
    if (empty($cart_item['product_custom_text'])) {
        return $item_data;
    }

    $item_data[] = [
        'key' => __('Custom text', 'woocommerce'),
        'value' => wc_clean($cart_item['product_custom_text']),
        'display' => '',
    ];

    return $item_data;
}

add_filter(
    'woocommerce_get_item_data',
    'woocommerce_display_custom_text_cart',
    10,
    2
);

// ================== END OF CUSTOM TEXT FUNCTIONS

/**
 * Radio buttons for product custom color
 */

// ADMIN: Helper function for populating list of available colors
function woocommerce_wp_multi_select($field, $variation_id = 0)
{
    global $thepostid, $post;

    if ($variation_id == 0) {
        $the_id = empty($thepostid) ? $post->ID : $thepostid;
    } else {
        $the_id = $variation_id;
    }

    $field['class'] = isset($field['class']) ? $field['class'] : 'select short';
    $field['wrapper_class'] = isset($field['wrapper_class'])
        ? $field['wrapper_class']
        : '';
    $field['name'] = isset($field['name']) ? $field['name'] : $field['id'];

    $meta_data = maybe_unserialize(get_post_meta($the_id, $field['id'], true));
    $meta_data = $meta_data ? $meta_data : [];

    $field['value'] = isset($field['value']) ? $field['value'] : $meta_data;

    echo '<p class="form-field ' .
        esc_attr($field['id']) .
        '_field ' .
        esc_attr($field['wrapper_class']) .
        '"><label for="' .
        esc_attr($field['id']) .
        '">' .
        wp_kses_post($field['label']) .
        '</label><select id="' .
        esc_attr($field['id']) .
        '" name="' .
        esc_attr($field['name']) .
        '" class="' .
        esc_attr($field['class']) .
        '" multiple="multiple">';

    foreach ($field['options'] as $key => $value) {
        echo '<option value="' .
            esc_attr($key) .
            '" ' .
            (in_array($key, $field['value']) ? 'selected="selected"' : '') .
            '>' .
            esc_html($value) .
            '</option>';
    }
    echo '</select> ';
    if (!empty($field['description'])) {
        if (isset($field['desc_tip']) && false !== $field['desc_tip']) {
            echo '<img class="help_tip" data-tip="' .
                esc_attr($field['description']) .
                '" src="' .
                esc_url(WC()->plugin_url()) .
                '/assets/images/help.png" height="16" width="16" />';
        } else {
            echo '<span class="description">' .
                wp_kses_post($field['description']) .
                '</span>';
        }
    }
}

add_action(
    'woocommerce_product_options_general_product_data',
    'woocommerce_product_available_color_options',
    20
);

// ADMIN: Show multiselection list to define available product colors (FIXME selecting should be more flexible, not hardcoded)
function woocommerce_product_available_color_options()
{
    global $post;

    echo '<div class="options_group hide_if_variable"">';

    woocommerce_wp_multi_select([
        'id' => 'woocommerce_custom_color',
        'name' => 'woocommerce_custom_color[]',
        'label' => __('Pick product available colors', 'woocommerce'),
        'options' => [
            'default' => __('Default', 'woocommerce'),
            'red' => __('Red', 'woocommerce'),
            'black' => __('Black', 'woocommerce'),
            'green' => __('Green', 'woocommerce'),
            'blue' => __('Blue', 'woocommerce'),
        ],
    ]);

    echo '</div>';
}

add_action(
    'woocommerce_process_product_meta',
    'woocommerce_save_product_available_colors',
    30,
    1
);

// ADMIN: Save product custom colors selection
function woocommerce_save_product_available_colors($post_id)
{
    if (isset($_POST['woocommerce_custom_color'])) {
        $post_data = $_POST['woocommerce_custom_color'];
        // Data sanitization
        $sanitize_data = [];
        if (is_array($post_data) && sizeof($post_data) > 0) {
            foreach ($post_data as $value) {
                $sanitize_data[] = esc_attr($value);
            }
        }
        update_post_meta($post_id, 'woocommerce_custom_color', $sanitize_data);
    }
}

// PRODUCT: Display radio buttons with available product custom colors
function woocommerce_product_available_colors_display()
{
    global $post;
    $product = wc_get_product($post->ID);
    $woocommerce_custom_colors = $product->get_meta('woocommerce_custom_color');

    foreach ($woocommerce_custom_colors as $color) {
        printf(
            '<input type="radio" name="custom_color" value="' .
                esc_attr($color) .
                '">' .
                esc_attr($color) .
                '<br>'
        );
    }
}

add_action(
    'woocommerce_before_add_to_cart_button',
    'woocommerce_product_available_colors_display'
);

// PRODUCT/CART: Save information about selected color by user
function woocommerce_add_custom_color_to_cart_item(
    $cart_item_data,
    $product_id,
    $variation_id
) {
    $product_custom_color = filter_input(INPUT_POST, 'custom_color');

    if (empty($product_custom_color)) {
        return $cart_item_data;
    }

    $cart_item_data['product_custom_color'] = $product_custom_color;

    return $cart_item_data;
}

add_filter(
    'woocommerce_add_cart_item_data',
    'woocommerce_add_custom_color_to_cart_item',
    10,
    3
);

// CART: Display custom color in cart
function woocommerce_display_custom_color_cart($item_data, $cart_item)
{
    if (empty($cart_item['product_custom_color'])) {
        return $item_data;
    }

    $item_data[] = [
        'key' => __('Custom color', 'woocommerce'),
        'value' => wc_clean($cart_item['product_custom_color']),
        'display' => '',
    ];

    return $item_data;
}

add_filter(
    'woocommerce_get_item_data',
    'woocommerce_display_custom_color_cart',
    10,
    2
);

// ================== END OF CUSTOM COLOR FUNCTIONS

/**
 * Upload extra image from disk
 */

// ADMIN: Add checkbox in product panel to select if uploading custom image for product should be available
function woocommerce_product_custom_image_upload()
{
    $args = [
        'id' => 'woocommerce_custom_image_admin_checkbox',
        'label' => __('Allow custom image?', 'cwoa'),
    ];
    woocommerce_wp_checkbox($args);
}

add_action(
    'woocommerce_product_options_general_product_data',
    'woocommerce_product_custom_image_upload'
);

// ADMIN: Save information about allowing uploading custom image for product (or not)
function save_woocommerce_product_custom_image_upload($post_id)
{
    $product = wc_get_product($post_id);
    $woocommerce_custom_product_image_allowed = isset(
        $_POST['woocommerce_custom_image_admin_checkbox']
    )
        ? $_POST['woocommerce_custom_image_admin_checkbox']
        : false;
    $product->update_meta_data(
        'woocommerce_custom_image_admin_checkbox',
        $woocommerce_custom_product_image_allowed
    );
    $product->save();
}
add_action(
    'woocommerce_process_product_meta',
    'save_woocommerce_product_custom_image_upload'
);

// PRODUCT: Show custom image upload option if it's allowed
function woocommerce_product_custom_image_upload_display()
{
    global $post;
    $product = wc_get_product($post->ID);
    $woocommerce_custom_product_image_allowed = $product->get_meta(
        'woocommerce_custom_image_admin_checkbox'
    );
    if ($woocommerce_custom_product_image_allowed) {
         $uploadFile   = "";
         $uploadFile   .='<div id="upload_image">';
         $uploadFile .='<input id="upload_custom_img" name="upload_custom_img" type="file"    multiple="false">';
         $uploadFile .='<span id="">';
         $uploadFile .='</span>';
         $uploadFile .='</div>';
         echo $uploadFile;
    }
}

add_action(
    'woocommerce_before_add_to_cart_button',
    'woocommerce_product_custom_image_upload_display'
);

//PRODUCT/CART/CHECKOUT - Save custom image selected by user
function woocommerce_add_custom_image_to_cart_item(
    $cart_item_data,
    $product_id,
    $variation_id
) {
    $product_custom_image = filter_input(
        INPUT_POST,
        'upload_custom_img'
    );

    if (empty($product_custom_image)) {
        return $cart_item_data;
    }

    $cart_item_data['product_custom_image'] = $product_custom_image;

    return $cart_item_data;
}

add_filter(
    'woocommerce_add_cart_item_data',
    'woocommerce_add_custom_image_to_cart_item',
    10,
    3
);

// CART: Display custom text in cart
function woocommerce_display_custom_image_cart($item_data, $cart_item)
{
    if (empty($cart_item['product_custom_image'])) {
        return $item_data;
    }

    $item_data[] = [
        'key' => __('Custom image', 'woocommerce'),
        'value' => wc_clean($cart_item['product_custom_image']),
        'display' => '',
    ];

    return $item_data;
}

add_filter(
    'woocommerce_get_item_data',
    'woocommerce_display_custom_image_cart',
    10,
    2
);

// ================== END OF CUSTOM IMAGE FUNCTIONS
