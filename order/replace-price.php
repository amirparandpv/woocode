<?php
// خروج در صورت دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

// Hook برای افزودن صفحه تنظیمات
function replace_price_settings_page() {
    ?>
    <div class="wrap11">
        <h1><?php _e('تنظیمات جایگزینی قیمت', 'textdomain'); ?></h1>
        <form id="replace-price-settings-form" method="post" action="options.php">
            <?php
            settings_fields('replace_price_settings_group');
            do_settings_sections('replace_price_settings');
            submit_button(__('ذخیره تغییرات', 'textdomain'), 'primary', 'cfwc_save_button');
            ?>
        </form>
        <div id="cfwc_message" style="display: none;"></div>
		<div>
    <p>
        راهنمای استفاده از تیک‌ها و فیلد قیمت سفارشی:
    </p>
    <ol>
        <li>
            <p>
                متن قیمت  سفارشی:
                <ul>
                    <li>در بخش "جایگزینی قیمت" می‌توانید متنی را که می‌خواهید به جای قیمت نمایش داده شود، وارد کنید. این متن می‌تواند هر چیزی باشد، مثلاً "رایگان" یا "تماس بگیرید".</li>
                </ul>
            </p>
        </li>
        <li>
            <p>
                نمایش محصولات ناموجود به صورت بلور شده:
                <ul>
                    <li>اگر این گزینه را انتخاب کنید، محصولاتی که ناموجود هستند به صورت بلور شده (شفافیت و افکت بلور) نمایش داده می‌شوند.</li>
                </ul>
            </p>
        </li>
        <li>
            <p>
                نمایش محصولات ناموجود در آرشیو:
                <ul>
                    <li>این گزینه به شما امکان می‌دهد تا محصولاتی که ناموجود هستند را در صفحات آرشیو محصولات نمایش دهید، به جای مخفی کردن آن‌ها.</li>
                </ul>
            </p>
        </li>
    </ol>
    <p>
        نکات مهم:
    </p>
    <ul>
        <li>تغییراتی که در این صفحه اعمال می‌کنید، پس از ذخیره شدن فوراً به نمایش در می‌آید.</li>
        <li>این تنظیمات فقط بر روی محصولات اعمال می‌شود و بر روی دیگر بخش‌های سایت تاثیری ندارد.</li>
        <li>متن قیمت سفارشی به جای قیمت فقط در صفحات محصولات نمایش داده می‌شود و بر روی قیمت‌های دیگر (مانند سبد خرید) تاثیری ندارد.</li>
    </ul>
</div>

    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#replace-price-settings-form').on('submit', function(e) {
            e.preventDefault();
            var customTextValue = $('#cfwc_custom_free_text').val();
            var showBlurredValue = $('#show_out_of_stock_products_blurred').prop('checked') ? 'yes' : 'no';
            var showInArchiveValue = $('#show_out_of_stock_in_archive').prop('checked') ? 'yes' : 'no';
            var data = {
                'action': 'cfwc_save_custom_free_text',
                'security': '<?php echo wp_create_nonce("cfwc_save_custom_free_text_nonce"); ?>',
                'cfwc_custom_free_text': customTextValue,
                'show_out_of_stock_products_blurred': showBlurredValue,
                'show_out_of_stock_in_archive': showInArchiveValue
            };

            $.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
                if (response.success) {
                    $('#cfwc_message').text(response.data.message).css('color', 'green').show();
                } else {
                    $('#cfwc_message').text(response.data.message).css('color', 'red').show();
                }
            });
        });
    });
    </script>
    <?php
}

// نصب فیلدهای تنظیمات
function replace_price_settings_init() {
    register_setting('replace_price_settings_group', 'cfwc_custom_free_text');
    register_setting('replace_price_settings_group', 'show_out_of_stock_products_blurred'); // فیلد جدید برای چک باکس
    register_setting('replace_price_settings_group', 'show_out_of_stock_in_archive'); // فیلد جدید برای چک باکس

    add_settings_section(
        'replace_price_main_section',
        __('تنظیمات متن قیمت سفارشی', 'textdomain'),
        'replace_price_section_callback',
        'replace_price_settings'
    );

    add_settings_field(
        'cfwc_custom_free_text',
        __('متن آزاد سفارشی', 'textdomain'),
        'cfwc_custom_free_text_callback',
        'replace_price_settings',
        'replace_price_main_section'
    );

    // افزودن فیلد چک باکس برای نمایش محصولات ناموجود به صورت بلور شده
    add_settings_field(
        'show_out_of_stock_products_blurred',
        __('نمایش محصولات ناموجود به صورت بلور شده', 'textdomain'),
        'show_out_of_stock_products_blurred_callback',
        'replace_price_settings',
        'replace_price_main_section'
    );

    // افزودن فیلد چک باکس برای نمایش محصولات ناموجود در آرشیو
    add_settings_field(
        'show_out_of_stock_in_archive',
        __('نمایش محصولات ناموجود در آرشیو', 'textdomain'),
        'show_out_of_stock_in_archive_callback',
        'replace_price_settings',
        'replace_price_main_section'
    );
}
add_action('admin_init', 'replace_price_settings_init');

// تابع بازخوانی بخش
function replace_price_section_callback() {
    echo '<p>' . __('متن آزاد سفارشی برای نمایش قیمت خود را وارد کنید.', 'textdomain') . '</p>';
}

// تابع بازخوانی فیلد متن آزاد سفارشی
function cfwc_custom_free_text_callback() {
    $cfwc_custom_text = get_option('cfwc_custom_free_text', 'رایگان');
    ?>
    <input type="text" id="cfwc_custom_free_text" name="cfwc_custom_free_text" value="<?php echo esc_attr($cfwc_custom_text); ?>" />
    <?php
}

// تابع بازخوانی چک باکس برای اعمال افکت بلور بر روی محصولات ناموجود
function show_out_of_stock_products_blurred_callback() {
    $show_blurred = get_option('show_out_of_stock_products_blurred', false);
    ?>
    <label for="show_out_of_stock_products_blurred">
        <input type="checkbox" id="show_out_of_stock_products_blurred" name="show_out_of_stock_products_blurred" value="1" <?php checked($show_blurred, true); ?> />
        <?php _e('نمایش محصولات ناموجود به صورت بلور شده', 'textdomain'); ?>
    </label>
    <?php
}

// تابع بازخوانی چک باکس برای نمایش محصولات ناموجود در آرشیو
function show_out_of_stock_in_archive_callback() {
    $show_in_archive = get_option('show_out_of_stock_in_archive', false);
    ?>
    <label for="show_out_of_stock_in_archive">
        <input type="checkbox" id="show_out_of_stock_in_archive" name="show_out_of_stock_in_archive" value="1" <?php checked($show_in_archive, true); ?> />
        <?php _e('نمایش محصولات ناموجود در آرشیو', 'textdomain'); ?>
    </label>
    <?php
}

// ذخیره متن آزاد سفارشی از طریق Ajax
add_action('wp_ajax_cfwc_save_custom_free_text', 'cfwc_save_custom_free_text_callback');
function cfwc_save_custom_free_text_callback() {
    check_ajax_referer('cfwc_save_custom_free_text_nonce', 'security');

    if (isset($_POST['cfwc_custom_free_text']) && current_user_can('manage_options')) {
        update_option('cfwc_custom_free_text', sanitize_text_field($_POST['cfwc_custom_free_text']));
        update_option('show_out_of_stock_products_blurred', isset($_POST['show_out_of_stock_products_blurred']) && $_POST['show_out_of_stock_products_blurred'] == 'yes');
        update_option('show_out_of_stock_in_archive', isset($_POST['show_out_of_stock_in_archive']) && $_POST['show_out_of_stock_in_archive'] == 'yes');
        wp_send_json_success(array('message' => __('تنظیمات با موفقیت ذخیره شدند.', 'textdomain')));
    } else {
        wp_send_json_error(array('message' => __('شما مجاز به انجام این کار نیستید.', 'textdomain')));
    }
}

// اصلاح قیمت نمایش داده شده محصول
function amr_price_free_zero($price, $product) {
    $custom_text = get_option('cfwc_custom_free_text', 'رایگان');
    $show_blurred = get_option('show_out_of_stock_products_blurred', false);

    // بررسی اگر قیمت معمولی صفر است
    if ($product->get_regular_price() === '0' || $product->get_regular_price() === 0) {
        $price = '<span class="woocommerce-Price-amount amount">' . esc_html($custom_text) . '</span>';
    }

    // بررسی برای اعمال افکت بلور بر روی محصولات ناموجود
    if ($show_blurred && !$product->is_in_stock()) {
        $price = '<span class="woocommerce-Price-amount amount outofstock">' . esc_html($custom_text) . '</span>';
    }

    return $price;
}
add_filter('woocommerce_get_price_html', 'amr_price_free_zero', 10, 2);

// CSS داخلی برای اعمال افکت بلور بر روی محصولات ناموجود
function cfwc_inline_styles() {
    $show_blurred = get_option('show_out_of_stock_products_blurred', false);
    if ($show_blurred) {
        ?>
        <style>
        .outofstock {
            filter: blur(0.5px); /* اعمال افکت بلور */
            opacity: 0.5; /* کاهش شفافیت */
        }
        </style>
        <?php
    }
}
add_action('wp_head', 'cfwc_inline_styles');

// تابع برای مخفی کردن محصولات ناموجود با اصلاح کوئری
add_action('woocommerce_product_query', 'hide_out_of_stock');
function hide_out_of_stock($q) {
    // بررسی اینکه گزینه نمایش محصولات ناموجود در آرشیو انتخاب شده باشد
    $show_in_archive = get_option('show_out_of_stock_in_archive', false);
    if ($show_in_archive) {
        $q->set('orderby', 'meta_value');
        $q->set('meta_key', '_stock_status');
        $q->set('order', 'ASC');
    }
}

// Hook برای اصلاح کوئری محصولات بر اساس گزینه چک باکس
function cfwc_show_out_of_stock_in_archive($query) {
    if (!is_admin() && is_post_type_archive('product') && $query->is_main_query()) {
        // بررسی اینکه گزینه نمایش محصولات ناموجود در آرشیو انتخاب شده باشد
        $show_in_archive = get_option('show_out_of_stock_in_archive', false);
        if ($show_in_archive) {
            hide_out_of_stock($query);
        }
    }
}
add_action('pre_get_posts', 'cfwc_show_out_of_stock_in_archive');

?>

