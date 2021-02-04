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
            '<div class="custom-field-container">
                        <label>%s</label>
                            <input 
                                type="text" 
                                id="woocommerce_product_custom_text_input" 
                                name="woocommerce_product_custom_text_input" 
                                value="Wprowadź tekst">
                            </div>',
            esc_html("Wpisz własny tekst")
        );
    }
}

add_action(
    'woocommerce_before_add_to_cart_button',
    'woocommerce_product_custom_text_input_display'
);

// PRODUCT: DISPLAY ADDITIONAL FIELDS IF CREATING CUSTOM TEXT IS ALLOWED
function woocommerce_product_custom_text_additional_fields_display()
{
    global $post;
    $product = wc_get_product($post->ID);
    $woocommerce_custom_product_text_allowed = $product->get_meta(
        'woocommerce_custom_text_admin_checkbox'
    );
    if ($woocommerce_custom_product_text_allowed) {
        printf(
            '<div class="custom-field-container">
                        <label>Wielkość czcionki</label>
                            <input 
                                type="number" 
                                id="woocommerce_product_custom_text_font_size" 
                                name="woocommerce_product_custom_text_font_size" 
                                value="10">
                            </div>',
        );
    }
}

add_action(
    'woocommerce_before_add_to_cart_button',
    'woocommerce_product_custom_text_additional_fields_display'
);


// PRODUCT/CART/CHECKOUT - Save custom text value inputted by user
function woocommerce_add_custom_text_to_cart_item(
    $cart_item_data,
    $product_id,
    $variation_id
)
{
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

// ORDER/ADMIN: Display custom text in order details and in admin panel
function woocommerce_display_custom_text_order($item, $cart_item_key, $values, $order)
{
    foreach ($item as $cart_item_key => $values) {
        if (!empty($values['product_custom_text'])) {
            $item->add_meta_data(__('Custom text', 'text'), $values['product_custom_text'], true);
        }
    }
}

add_action('woocommerce_checkout_create_order_line_item', 'woocommerce_display_custom_text_order', 10, 4);


// ================== END OF CUSTOM TEXT FUNCTIONS

/**
 * Product custom color
 */
// ADMIN: Show checkbox to unable product color customizing
function woocommerce_product_custom_color_input()
{
    $args = [
        'id' => 'woocommerce_custom_color_admin_checkbox',
        'label' => __('Allow custom color?', 'cwoa'),
    ];
    woocommerce_wp_checkbox($args);
}

add_action(
    'woocommerce_product_options_general_product_data',
    'woocommerce_product_custom_color_input'
);

// ADMIN: Save product custom colors selection
function save_woocommerce_product_custom_color($post_id)
{
    $product = wc_get_product($post_id);
    $woocommerce_custom_color_allowed = isset(
        $_POST['woocommerce_custom_color_admin_checkbox']
    )
        ? $_POST['woocommerce_custom_color_admin_checkbox']
        : false;
    $product->update_meta_data(
        'woocommerce_custom_color_admin_checkbox',
        $woocommerce_custom_color_allowed
    );
    $product->save();
}

add_action(
    'woocommerce_process_product_meta',
    'save_woocommerce_product_custom_color'
);

// PRODUCT: Display HTML5 color picker
function woocommerce_product_available_colors_display()
{
    printf('<div class="custom-field-container">
	<label>Wybierz kolor</label>
	<input style="width: 100px" type="color" id="favcolor" name="favcolor" value="#ffffff">
	</div>');
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
)
{
    $product_custom_color = filter_input(INPUT_POST, 'favcolor');

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
    if (!empty($cart_item['product_custom_color'])) {

        $item_data[] = [
            'key' => __('Custom color', 'woocommerce'),
            'value' => $cart_item['product_custom_color'],
            'display' => '',

        ];
    }
    return $item_data;
}

add_filter(
    'woocommerce_get_item_data',
    'woocommerce_display_custom_color_cart',
    10,
    2
);

function woocommerce_display_custom_color_order($item, $cart_item_key, $values, $order)
{
    foreach ($item as $cart_item_key => $values) {
        if (!empty($values['product_custom_color'])) {
            $item->add_meta_data(__('Custom color', 'color'), $values['product_custom_color'], true);
        }
    }
}

add_action('woocommerce_checkout_create_order_line_item', 'woocommerce_display_custom_color_order', 10, 4);

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
        ?>
		<div class="custom-field-container">
			<p class="form-row validate-required" id="cimg">
				<label for="file_field"><?php echo __("Upload Image") . ': '; ?>
					<input type='file' name='custom_image' accept='image/*'>
					<input type='submit' name='submit_cimg' accept='image/*' value="Prześlij obrazek">
				</label>
			</p>
		</div>

        <?php
    }
}

add_action(
    'woocommerce_before_add_to_cart_button',
    'woocommerce_product_custom_image_upload_display'
);

// PRODUCT: Show uploaded image() on product page
function woocommerce_product_custom_image_show_on_upload()
{
    $file_upload = array();

    if (isset($_FILES['custom_image']) && !empty($_FILES['custom_image'])) {
        $upload = wp_upload_bits($_FILES['custom_image']['name'], null, file_get_contents($_FILES['custom_image']['tmp_name']));
        $filetype = wp_check_filetype(basename($upload['file']), null);
        $upload_dir = wp_upload_dir();
        $upl_base_url = is_ssl() ? str_replace('http://', 'https://', $upload_dir['baseurl']) : $upload_dir['baseurl'];
        $base_name = basename($upload['file']);

        $file_upload = array(
            'guid' => $upl_base_url . '/' . _wp_relative_upload_path($upload['file']), // Url
            'file_type' => $filetype['type'], // File type
            'file_name' => $base_name, // File name
            'title' => ucfirst(preg_replace('/\.[^.]+$/', '', $base_name)), // Title
        );

    }

    ?>
    <script>
        jQuery(document).ready(function ($) {
            const mainImageContainer = document.getElementsByClassName('woocommerce-product-gallery__image')[0];
            mainImageContainer.insertAdjacentHTML('afterend', `
                    <div id="canvas-wrapper" style="position: absolute">
                        <canvas id="product-canvas"></canvas>
                    </div>
                `);
            const canvas = new fabric.Canvas('product-canvas');


            // declare where to put canvas on product
            const canvasWrapper = document.getElementById('canvas-wrapper');
            canvasWrapper.style.left = '12%';
            canvasWrapper.style.top = '31%';

            // initial canvas properties
            canvas.setWidth(0.77 * mainImageContainer.clientWidth);
            canvas.setHeight(0.39 * mainImageContainer.clientHeight);

            // resize canvas when body changes
            document.body.onresize = () => {
                canvas.setWidth(0.77 * mainImageContainer.clientWidth);
                canvas.setHeight(0.39 * mainImageContainer.clientHeight);
            }

            const initialText = 'Wprowadź tekst';
            canvas.fillStyle = "#fff";
            const textBox = new fabric.Text(initialText, {
                left: 0,
                top: 0,
                fontSize: 10,
                editable: false,
                lockScalingY: true
            });
            canvas.add(textBox);

            const imgUrl = "<?php print(empty($file_upload) ? '' : print($file_upload['guid']))?>"
            console.log('imgUrl: ', imgUrl);

            if (imgUrl !== '') {
                new fabric.Image.fromURL("<?php print($file_upload['guid'])?>", img => {
                    img.set({top: 50, left: 50, height: 300, width: 300, scaleX: .50, scaleY: .50});
                    canvas.add(img);
                });
            }

            const textInput = document.getElementById('woocommerce_product_custom_text_input');

            textInput.oninput = () => {
                textBox.text = textInput.value;
                canvas.renderAll();
            };


            // handle change background color
            const colorPicker = document.getElementById('favcolor');
            colorPicker.oninput = () => mainImageContainer.style.backgroundColor = colorPicker.value;

            // handle change custom text font size
            const fontSize = document.getElementById('woocommerce_product_custom_text_font_size');
            fontSize.oninput = () => {
                textBox.fontSize = fontSize.value;
                canvas.renderAll();
            }
        });
    </script>
    <?php
}

add_action(
    'woocommerce_before_add_to_cart_button',
    'woocommerce_product_custom_image_show_on_upload'
);

//PRODUCT/CART/CHECKOUT - Save custom image selected by user
function woocommerce_add_custom_image_to_cart_item(
    $cart_item_data,
    $product_id,
    $variation_id
)
{
    $product_custom_image = filter_input(
        INPUT_POST,
        'custom_image'
    );

    if (!empty($product_custom_image)) {


        $cart_item_data['product_custom_image'] = $product_custom_image;
    }
    return $cart_item_data;
}

add_filter(
    'woocommerce_add_cart_item_data',
    'woocommerce_add_custom_image_to_cart_item',
    10,
    3
);

// CART: Display custom image in cart
function woocommerce_display_custom_image_cart($item_data, $cart_item)
{
    if (!empty($cart_item['product_custom_image'])) {

        $item_data[] = [
            'key' => __('Custom image', 'woocommerce'),
            'value' => wc_clean($cart_item['product_custom_image']),
            'display' => '',
        ];
    }
    return $item_data;
}


add_filter(
    'woocommerce_get_item_data',
    'woocommerce_display_custom_image_cart',
    10,
    2
);

function woocommerce_display_custom_image_order($item, $cart_item_key, $values, $order)
{
    foreach ($item as $cart_item_key => $values) {
        if (!empty($values['product_custom_image'])) {
            $item->add_meta_data(__('Custom image', 'image'), $values['product_custom_image'], true);
        }
    }
}

add_action('woocommerce_checkout_create_order_line_item', 'woocommerce_display_custom_color_order', 10, 4);

// ================== END OF CUSTOM IMAGE FUNCTIONS
