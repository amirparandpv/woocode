<?php
// تابع برای نمایش صفحه جایگزینی کلمات
function replace_woocode_settings_page() {
    ?>
    <div class="wrap11">
        <h1>جایگزینی کلمات</h1>
        <form id="replace-woocode-settings-form" method="post" action="options.php">
            <?php
            settings_fields('replace_woocode_settings_group');
            do_settings_sections('replace_woocode_settings');
            submit_button();
            ?>
        </form>
		
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('#replace-woocode-settings-form').on('submit', function(e) {
            e.preventDefault();

            const data = $(this).serialize() + '&action=replace_woocode_save_settings&nonce=<?php echo wp_create_nonce("replace_woocode_save_settings_nonce"); ?>';

            $.post(ajaxurl, data, function(response) {
                if (response.success) {
                    alert('تنظیمات با موفقیت ذخیره شد.');
                } else {
                    alert('خطایی رخ داد.');
                }
            });
        });
    });
    </script>
    <?php
}

// ثبت تنظیمات و فیلدهای فرم
function woocode_settings_init() {
    // ثبت تنظیمات جایگزینی کلمات
    register_setting('replace_woocode_settings_group', 'replace_words');

    add_settings_section(
        'replace_woocode_main_section',
        'تنظیمات جایگزینی کلمات',
        'replace_woocode_section_callback',
        'replace_woocode_settings'
    );

    add_settings_field(
        'replace_words',
        'کلمات جایگزین',
        'replace_words_callback',
        'replace_woocode_settings',
        'replace_woocode_main_section'
    );
}
add_action('admin_init', 'woocode_settings_init');

// کال‌بک برای بخش تنظیمات جایگزینی کلمات
function replace_woocode_section_callback() {
    echo 'تنظیمات جایگزینی کلمات را اینجا وارد کنید.';
}

// کال‌بک برای فیلد کلمات جایگزین
function replace_words_callback() {
    $replace_words = get_option('replace_words', array());
    ?>
    <table id="replace-words-table">
        <tr>
            <th>کلمه موجود</th>
            <th>کلمه جایگزین</th>
            <th>عملیات</th>
        </tr>
        <?php
        if (!empty($replace_words)) {
            foreach ($replace_words as $index => $words) {
                ?>
                <tr>
                    <td><input type="text" name="replace_words[<?php echo $index; ?>][replace]" value="<?php echo esc_attr($words['replace']); ?>" /></td>
                    <td><input type="text" name="replace_words[<?php echo $index; ?>][replacement]" value="<?php echo esc_attr($words['replacement']); ?>" /></td>
                    <td><button type="button" class="button remove-row">حذف</button></td>
                </tr>
                <?php
            }
        }
        ?>
    </table>
    <button type="button" class="button add-row">اضافه کردن کلمه جدید</button>
    <script>
    jQuery(document).ready(function($) {
        let rowCount = $('#replace-words-table tr').length - 1; // تعداد ردیف‌های موجود

        $('.add-row').on('click', function() {
            const newRow = `<tr>
                <td><input type="text" name="replace_words[${rowCount}][replace]" /></td>
                <td><input type="text" name="replace_words[${rowCount}][replacement]" /></td>
                <td><button type="button" class="button remove-row">حذف</button></td>
            </tr>`;
            $('#replace-words-table').append(newRow);
            rowCount++;
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        $('#replace-woocode-settings-form').on('submit', function(e) {
            e.preventDefault();

            const data = $(this).serialize() + '&action=replace_woocode_save_settings&nonce=<?php echo wp_create_nonce("replace_woocode_save_settings_nonce"); ?>';

            $.post(ajaxurl, data, function(response) {
                if (response.success) {
                    alert('تنظیمات با موفقیت ذخیره شد.');
                } else {
                    alert('خطایی رخ داد.');
                }
            });
        });
    });
    </script>
    <?php
}

// تابع برای جایگزینی کلمات در ترجمه‌ها
function mw_translate_words_array($translated) {
    $replace_words = get_option('replace_words', array());

    if (!empty($replace_words)) {
        foreach ($replace_words as $words) {
            if (!empty($words['replace']) && !empty($words['replacement'])) {
                $translated = str_ireplace($words['replace'], $words['replacement'], $translated);
            }
        }
    }

    return $translated;
}
add_filter('gettext', 'mw_translate_words_array');
add_filter('ngettext', 'mw_translate_words_array');

// کال‌بک برای ذخیره تنظیمات با استفاده از Ajax
add_action('wp_ajax_replace_woocode_save_settings', 'replace_woocode_save_settings');

function replace_woocode_save_settings() {
    check_ajax_referer('replace_woocode_save_settings_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('شما اجازه دسترسی به این صفحه را ندارید.');
    }

    if (isset($_POST['replace_words'])) {
        $replace_words = $_POST['replace_words'];
        update_option('replace_words', $replace_words);
        wp_send_json_success();
    } else {
        wp_send_json_error('داده‌های ارسالی نامعتبر هستند.');
    }
}
?>
