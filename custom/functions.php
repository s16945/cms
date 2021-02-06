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
        'key' => __('Własny tekst', 'woocommerce'),
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
            $item->add_meta_data(__('Własny tekst', 'text'), $values['product_custom_text'], true);
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
    global $post;
    $product = wc_get_product($post->ID);
    $woocommerce_custom_color_allowed = $product->get_meta(
        'woocommerce_custom_color_admin_checkbox'
    );

    if ($woocommerce_custom_color_allowed) {
        printf('<div class="custom-field-container">
        <label>Wybierz kolor</label>
        <input style="width: 100px" type="color" id="favcolor" name="favcolor" value="#ffffff">
        </div>');
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
            'key' => __('Wybrany kolor', 'woocommerce'),
            'value' => $cart_item['product_custom_color'],
            'display' => '<label></label><div style="display: block; width: 50px; height: 20px; border: 1px solid black; background-color: '.$cart_item['product_custom_color'].'"/>',

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
            $item->add_meta_data(__('Wybrany kolor', 'color'), $values['product_custom_color'], true);
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
                <label for="file_field" class="input-img"><?php echo __("Dodaj obrazek") . ': '; ?>
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

// This is a hack with empty field - this way we can access custom project image created by user
function product_custom_image_invisible_store() {
    ?>
        <input type="hidden" name="custom_project"/>
    <?php
};

add_action(
    'woocommerce_before_add_to_cart_button',
    'product_custom_image_invisible_store'
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
            canvasWrapper.style.left = '0';
            canvasWrapper.style.top = '0';

            // initial canvas properties
            canvas.setWidth(mainImageContainer.clientWidth);
            canvas.setHeight(mainImageContainer.clientHeight);

            // resize canvas when body changes
            document.body.onresize = () => {
                canvas.setWidth(mainImageContainer.clientWidth);
                canvas.setHeight(mainImageContainer.clientHeight);
            }

            const initialText = 'Wprowadź tekst';
            canvas.fillStyle = "#fff";
            const textBox = new fabric.Text(initialText, {
                left: 200,
                top: 200,
                fontSize: 10,
                editable: false,
                lockScalingY: true,
                lockScalingX: true,
            });
            canvas.add(textBox);

            const imgUrl = "<?php print(empty($file_upload) ? '' : print($file_upload['guid']))?>"

            if (imgUrl !== '') {
                new fabric.Image.fromURL("<?php print($file_upload['guid'])?>", img => {
                    img.set({top: 250, left: 250, height: 300, width: 300, scaleX: .50, scaleY: .50});
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
            colorPicker.oninput = () => {
                canvas.setBackgroundColor(colorPicker.value);
                canvas.renderAll();
            }

            // handle change custom text font size
            const fontSize = document.getElementById('woocommerce_product_custom_text_font_size');
            fontSize.oninput = () => {
                textBox.fontSize = fontSize.value;
                canvas.renderAll();
            }

            // get add to cart button, on click save image
            const imageSaver = document.getElementsByName('add-to-cart')[0];
            imageSaver.addEventListener('click', saveImage, false);

            function saveImage(e) {
				var base64Output = canvas.toDataURL({
                    format: 'png',
                    quality: 1,
                });

                // pass the value to our hidden field
                document.getElementsByName('custom_project')[0].value = base64Output;
            }

            // set image as canvas background
            $(window).on('load', function () {
                const img = $('img.zoomImg');
                new fabric.Image.fromURL(img[0].src, myImg => {
                    canvas.setBackgroundImage(myImg, canvas.renderAll.bind(canvas), {
                        scaleX: canvas.width / myImg.width,
                        scaleY: canvas.height / myImg.height
                    });
                });
            });

            // limit object moving
            canvas.on('object:moving', function (e) {
                const obj = e.target;
                // if object is too big ignore
                if (obj.currentHeight > obj.canvas.height || obj.currentWidth > obj.canvas.width) {
                    return;
                }
                obj.setCoords();
                // top-left  corner
                if (obj.getBoundingRect().top < 0.31 * canvas.height || obj.getBoundingRect().left < 0.12 * canvas.width) {
                    obj.top = Math.max(obj.top, 0.31 * canvas.height);
                    obj.left = Math.max(obj.left, 0.12 * canvas.width);
                }
                // bot-right corner
                if (
                    obj.getBoundingRect().top + obj.getBoundingRect().height > 0.71 * obj.canvas.height
                    || obj.getBoundingRect().left + obj.getBoundingRect().width > 0.88 * obj.canvas.width
                ) {
                    obj.top = Math.min(obj.top, 0.71 * obj.canvas.height - obj.getBoundingRect().height + obj.top - obj.getBoundingRect().top);
                    obj.left = Math.min(obj.left, 0.88 * obj.canvas.width - obj.getBoundingRect().width + obj.left - obj.getBoundingRect().left);
                }
            });
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
    $custom_img_value = $_POST['custom_project'];

    if (!empty($custom_img_value)) {
        $cart_item_data['product_custom_image'] = $custom_img_value;
    }
    return $cart_item_data;
}

add_filter(
    'woocommerce_add_cart_item_data',
    'woocommerce_add_custom_image_to_cart_item',
    10,
    3
);

/* Hide base64 url in cart
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
*/
function woocommerce_display_custom_image_order($item, $cart_item_key, $values, $order)
{
    foreach ($item as $cart_item_key => $values) {
        if (!empty($values['product_custom_image'])) {
            $item->add_meta_data(__('Custom image', 'img'), $values['product_custom_image'], true);        }
    }
}

add_action('woocommerce_checkout_create_order_line_item', 'woocommerce_display_custom_color_order', 10, 4);

// CART: Display custom item image thumbnail in cart
function woocommerce_display_custom_item_cart( $_product_img, $cart_item, $cart_item_key ) {

    $img      =   '<img src="'.$cart_item['product_custom_image'].'" />';
    return $img;
}

add_filter( 'woocommerce_cart_item_thumbnail', 'woocommerce_display_custom_item_cart', 10, 3 );

// ================== END OF CUSTOM IMAGE FUNCTIONS


