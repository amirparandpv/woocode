<?php
/*
Plugin Name: WoCode
Description: در افزونه ووکد سعی شده است تمامی کد های مورد استفاده در بخش های سایت فروشگاهی ساخته شده با ووکامرس جمع آوری و به آسانی مورد استفاده قرار بگیرد
  License: GPL2
  Plugin URL: https://amirparand.ir
  Description: مقایسه حرفه ای بین محصولات 
  Version: 1.1
  Author: Amir_Parand
  Author URI: https://amirparand.ir
  Text Domain:  WoCode
  Requires at least: 6.0
  Requires PHP: 5.6
*/
// اضافه کردن لینک به بخش افزونه‌های وردپرس 
function my_plugin_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=user_woocode_settings">تنظیمات</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'my_plugin_settings_link' );

function enqueue_woocommerce_orders_styles() {
    // Enqueue the CSS file
    wp_enqueue_style( 'woocommerce-orders-style', plugins_url( '/css/style.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'enqueue_woocommerce_orders_styles' );

function add_custom_css() {
    wp_enqueue_style( 'custom-style', plugins_url( '/css/style.css', __FILE__ ) );
}
add_action( 'admin_enqueue_scripts', 'add_custom_css' );

function enqueue_custom_styles() {
    wp_enqueue_style('custom-styles', plugin_dir_url( __FILE__ ) . 'css/style-order.css');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_styles');



// افزودن کد کوتاهبرای استفاده در  تعدا نظرات کاربر متنها و صفحات وردپرس ️--------------------------------------✔
function display_user_comment_counts_shortcode() {
                            global $wpdb;
                            $where = 'WHERE comment_approved = 1 AND user_id <> 0';
                            $comment_counts = (array) $wpdb->get_results("
                                SELECT user_id, COUNT( * ) AS total
                                FROM {$wpdb->comments}
                                {$where}
                                GROUP BY user_id
                                ", object);
                            foreach ( $comment_counts as $count ) {
                                $user = get_userdata($count->user_id);
                                echo '' . $count->total . '
                                ';
                            }
}
add_shortcode('display_user_comment_counts', 'display_user_comment_counts_shortcode');

// کد کوتاه برای روز های همراهی  ️--------------------------------------------------------------------------------✔
function display_accnt_yearsold_counts_shortcode() {
  $today_date      = new DateTime( date( 'Y-m-d', strtotime( 'today' ) ) );
    $register_date  = get_the_author_meta( 'user_registered', get_current_user_id() );
    $registered = new DateTime( date( 'Y-m-d', strtotime( $register_date ) ) );
    $interval_date   = $today_date->diff( $registered );
        if( $interval_date->days < 31 ) {
            echo '' . $interval_date->format('%d روز');
            }
        elseif( $interval_date->days < 365 ) {
            echo '' . $interval_date->format('%m ماه و  %d روز');
            }
        elseif( $interval_date->days > 365 ) {
            echo '' . $interval_date->format('%y سال و %m ماه و %d روز');
            }
}
add_shortcode('display_accnt_yearsold_counts', 'display_accnt_yearsold_counts_shortcode');


//  کد   استفاده برای مجموع خرید انجام شده -----------------------------------------------------------------------✔
function display_accnt_sumorders_counts_shortcode() {
global $wpdb;

$user_id = get_current_user_id(); // Current user ID

$user_purchases_total_sum = $wpdb->get_var( "
    SELECT SUM(pm.meta_value) FROM {$wpdb->prefix}postmeta as pm
    INNER JOIN {$wpdb->prefix}posts as p ON pm.post_id = p.ID
    INNER JOIN {$wpdb->prefix}postmeta as pm2 ON pm.post_id = pm2.post_id
    WHERE p.post_status LIKE 'wc-completed' AND p.post_type LIKE 'shop_order'
    AND pm.meta_key LIKE '_order_total' AND pm2.meta_key LIKE '_customer_user'
    AND pm2.meta_value LIKE $user_id
" );
$user_id = get_current_user_id();

$string = wc_get_customer_total_spent( $user_id );

echo $string;
}
add_shortcode('display_accnt_sumorders_counts', 'display_accnt_sumorders_counts_shortcode');



//  کد   استفاده برتعدادموع خرید انجام شده  ️----------------------------------------------------------------------✔
function display_accnt_order_counts_shortcode() {
    // Check if WooCommerce is active
    if ( class_exists( 'WooCommerce' ) ) {
        // Get the current user ID
        $user_id = get_current_user_id();
        
        // Ensure the user is logged in
        if ( $user_id ) {
            // Query to get completed orders count for the current user
            $args = array(
                'customer_id' => $user_id,
                'status'      => 'completed',
                'return'      => 'ids', // Return only the order IDs
            );
            $completed_orders = wc_get_orders( $args );
            $completed_orders_count = count( $completed_orders );
            
            // Display the formatted message
            return '<p>' . sprintf( esc_html__('%d   سفارش', 'your-text-domain'), $completed_orders_count ) . '</p>';
        } else {
            // If user is not logged in, display a message
            return '<p>' . esc_html__('برای مشاهده تعداد سفارشات تکمیل شده خود، باید وارد شوید.', 'your-text-domain') . '</p>';
        }
    } else {
        // If WooCommerce is not active, display an error message
        return '<p>' . esc_html__('ووکامرس فعال نیست.', 'your-text-domain') . '</p>';
    }
}
// Register the shortcode
add_shortcode('display_accnt_order_counts', 'display_accnt_order_counts_shortcode');

// کد کوتاه برای نمایش لیست مورد با محدودیت 3 مورد  ️-wishlist-------------------------------------------------------------✔
function get_user_wishlist_products() {
    global $wpdb;

    // فرض می‌کنیم کاربر وارد شده است
    $user_id = get_current_user_id();

    // نام جداول را دریافت کنید
    $products_table = $wpdb->prefix . 'woodmart_wishlist_products';
    $wishlists_table = $wpdb->prefix . 'woodmart_wishlists';

    // بازیابی ID لیست علاقه کاربر
    $wishlist_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT ID FROM $wishlists_table WHERE user_id = %d",
        $user_id
    ) );

    if ( ! $wishlist_id ) {
        return array();
    }

    // بازیابی ۳ محصول از جدول محصولات لیست علاقه به ترتیب زمانی نزولی
    $products = $wpdb->get_results( $wpdb->prepare(
        "SELECT product_id FROM $products_table WHERE wishlist_id = %d ORDER BY date_added DESC LIMIT 3",
        $wishlist_id
    ), ARRAY_A );

    return $products;
}

function display_user_wishlist() {
    $wishlist_products = get_user_wishlist_products();

    if ( empty( $wishlist_products ) ) {
        return 'محصول مورد علاقه‌ای یافت نشد.';
    }

    // بررسی تنظیمات نمایش
    $display_mode = get_option( 'my_wishlist_display_mode' ) ? 'horizontal' : 'vertical';

    // HTML structure
    $output = '<div class="wishlist-container">';
    $output .= '<ul id="wishlist" class="user-wishlist ' . $display_mode . '">';

    $count = 0;
    foreach ( $wishlist_products as $product ) {
        if ( $count >= 3 ) break; // محدودیت نمایش 3 محصول
        $product_id = $product['product_id'];
        $product_obj = wc_get_product( $product_id );
        if ( $product_obj ) {
            $product_title = $product_obj->get_title();
            $product_price = number_format($product_obj->get_price()) . ' تومان';
            $product_image = $product_obj->get_image('thumbnail');
            $product_link = get_permalink( $product_id );

            $output .= '<li class="favorite-product">';
            $output .= '<div class="product-info">';
            $output .= '<h3><a href="' . esc_url( $product_link ) . '">' . esc_html( $product_title ) . '</a></h3>';
            $output .= '<span class="product-price">' . esc_html( $product_price ) . '</span>';
            $output .= '</div>';
            $output .= '<a href="' . esc_url( $product_link ) . '" class="product-thumbnail">' . $product_image . '</a>';
            $output .= '</li>';

            $count++;
        }
    }

    $output .= '</ul>';
    $output .= '</div>';

    return $output;
	
}

// ثبت شورت‌کد
add_shortcode( 'user_wishlist', 'display_user_wishlist' );




//  نمایش 3 مورد از وضعیت سفارش های کاربری  - ️----------------------------------------------------------------------✔
function display_last_5_orders_shortcode() {
    // Get the last 3 orders
    $orders = wc_get_orders( array(
        'limit'   => 3,
        'orderby' => 'date',
        'order'   => 'DESC',
    ) );

    // Output the orders
    if ( $orders ) {
        echo '<ul class="orders-list">'; // Adding a class for styling
        foreach ( $orders as $order ) {
            $status_class = ''; // Default status class

            switch ( $order->get_status() ) {
                case 'completed':
                    $status_class = 'completed';
                    break;
                case 'processing':
                    $status_class = 'processing';
                    break;
                case 'on-hold':
                    $status_class = 'on-hold';
                    break;
                case 'pending':
                    $status_class = 'pending';
                    break;
                case 'cancelled':
                    $status_class = 'cancelled';
                    break;
                case 'refunded':
                    $status_class = 'refunded';
                    break;
                case 'failed':
                    $status_class = 'failed';
                    break;
                default:
                    // For other statuses, no additional class
                    break;
            }

            printf(
                '<li class="order-item %s">
                    <span class="b2"> کد سفارش %d <span> %s </span> </span>
                    <span class="b1">وضعیت: %s  %s </span>
                </li>',
                esc_attr( $status_class ), // Add status class
                $order->get_id(),
                wc_format_datetime( $order->get_date_created() ),
                wc_get_order_status_name( $order->get_status() ),
                $order->get_formatted_order_total()
            );
        }
        echo '</ul>';
    } else {
        echo 'No orders found.';
    }
}
add_shortcode( 'display_last_orders', 'display_last_5_orders_shortcode' );



//--------------------------محصولات در حال پردازش  ---------------------------------
function get_user_orders_count() {
    // دریافت اطلاعات کاربر وارد شده
    $user_id = get_current_user_id();

    if ($user_id == 0) {
        return 'لطفا وارد شوید.';
    }

    // دریافت سفارشات کاربر وارد شده
    $args = array(
        'customer_id' => $user_id,
        'status' => 'processing', // وضعیت مورد نظر را می‌توانید تغییر دهید
        'limit' => -1, // بدون محدودیت در تعداد سفارشات
    );
    $orders = wc_get_orders($args);
    $order_count = count($orders);

    // ساخت خروجی
    $output = esc_html($order_count) . ' سفارش';
    return $output;
}
add_shortcode('user_orders_count', 'get_user_orders_count');

//------------------------------------- مرجوعی محصولات ---------------------------------
function get_user_refunded_orders_count() {
    // دریافت اطلاعات کاربر وارد شده
    $user_id = get_current_user_id();

    if ($user_id == 0) {
        return 'لطفا وارد شوید.';
    }

    // دریافت سفارشات مرجوع شده کاربر وارد شده
    $args = array(
        'customer_id' => $user_id,
        'status' => 'refunded', // وضعیت سفارش مرجوع شده
        'limit' => -1, // بدون محدودیت در تعداد سفارشات
    );
    $orders = wc_get_orders($args);
    $order_count = count($orders);

    // ساخت خروجی
    $output = esc_html($order_count) . ' سفارش';
    return $output;
}
add_shortcode('user_refunded_orders_count', 'get_user_refunded_orders_count');





// افزودن قابلیت wishlist در صفحه جداگانه سایت ووکامرسی
// 1. Register new endpoint (URL) for My Account page
// Note: Re-save Permalinks or it will give 404 error

function bbloomer_add_wishlist_endpoint() {
add_rewrite_endpoint( 'wishlist', EP_ROOT | EP_PAGES );
}

add_action( 'init', 'bbloomer_add_wishlist_endpoint' );

// ------------------
// 2. Add new query var

function bbloomer_wishlist_query_vars( $vars ) {
$vars[] = 'wishlist';
return $vars;
}

add_filter( 'query_vars', 'bbloomer_wishlist_query_vars', 0 );

// ------------------
// 3. Insert the new endpoint into the My Account menu

function bbloomer_add_wishlist_link_my_account( $items ) {
$items['wishlist'] = 'لیست‌های من';
return $items;
}

add_filter( 'woocommerce_account_menu_items', 'bbloomer_add_wishlist_link_my_account' );

// ------------------
// 4. Add content to the new tab

function bbloomer_wishlist_content() {
    // Retrieve the user input value
    $user_input = get_option('user_woocode_text_field');
    echo '<h3>لیست مورد علاقه</h3><p></i></p>';
    // Display the user input value safely
echo do_shortcode( $user_input );



}

add_action( 'woocommerce_account_wishlist_endpoint', 'bbloomer_wishlist_content' );

// اضافه کردن برگه تنظیمات  ️--------------------------------------------------------------------------------✔
function woocode_plugin_settings_page() {
    // افزودن منوی اصلی
    add_menu_page(
        'تنظیمات افزونه کد های مورد نیاز ووکامرس', // عنوان صفحه
        'تنظیمات ووکد',                          // نام منو
        'manage_options',                           // قابلیت دسترسی
        'user_woocode_settings',                    // شناسه منو
        'user_woocode_settings_page',               // تابع برای نمایش صفحه تنظیمات
        'dashicons-admin-generic',                  // آیکون منو (اختیاری)
        90                                         // موقعیت منو (اختیاری)
    );

    // افزودن زیرمنو
    add_submenu_page(
        'user_woocode_settings',                    // شناسه منوی والد
        'جایگزاری کلمات',                          // عنوان زیرمنو
        'جایگزاری کلمات',                          // نام زیرمنو
        'manage_options',                           // قابلیت دسترسی
        'replace_woocode_settings',                 // شناسه زیرمنو
        'replace_woocode_settings_page'             // تابع برای نمایش صفحه زیرمنو
    );  
	// افزودن زیرمنو
    add_submenu_page(
        'user_woocode_settings',                    // شناسه منوی والد
        'جایگذاری قیمت ',                          // عنوان زیرمنو
        'جایگذاری قیمت ',                           // نام زیرمنو
        'manage_options',                           // قابلیت دسترسی
        'replace_price_settings',                 // شناسه زیرمنو
        'replace_price_settings_page'             // تابع برای نمایش صفحه زیرمنو
    );
}
add_action('admin_menu', 'woocode_plugin_settings_page');


// صفحه تنظیمات افزونه
function user_woocode_settings_page() {
    // Retrieve the current value of the input fields
    $current_value = get_option('user_woocode_text_field');
    $wishlist_display_mode = get_option('my_wishlist_display_mode');
    $notification_message = get_option('notification_message', ''); // پیام سفارشی با مقدار پیش‌فرض خالی
    ?>

    <div>
        <h1>تنظیمات افزونه کد های مورد نیاز ووکامرس</h1>
        
        <div class="wrap11">
            <h2>لیست کد های کوتاه مورد استفاده در سایت</h2>
            <p>برای نمایش و استفاده از کد های کوتاه در محتوای صفحات یا نوشته‌های وردپرس، می‌توانید از کد کوتاه های زیر استفاده کنید. این کد را در ویرایشگر کلاسیک یا ویرایشگر بلوک وردپرس یا المنتور وارد کنید. (برای استفاده درست کد های کوتاه را بین [ ] قرار دهید)</p>
            <div>
                <h3>کدهای مرتبط با سفارش:</h3>
                <ul>
                    <li>کد کوتاه برای نمایش محصولات در حال پردازش: [user_orders_count]</li>
                    <li>کد کوتاه برای نمایش محصولات مرجوعی: [user_refunded_orders_count]</li>
                    <li>نمایش آخرین وضعیت سفارش با محدودیت 3 مورد: [display_last_orders]</li>
                </ul>
                
                <h3>کدهای مرتبط با کاربر و حساب کاربری:</h3>
                <ul>
                    <li>کد کوتاه برای نمایش تعداد نظرات داده شده توسط کاربر: [display_user_comment_counts]</li>
                    <li>کد کوتاه برای نمایش روز های همراهی توسط کاربر: [display_accnt_yearsold_counts]</li>
                    <li>کد کوتاه برای نمایش مجموع هزینه های خرید انجام شده توسط کاربر: [display_accnt_sumorders_counts]</li>
                    <li>کد کوتاه برای نمایش تعداد خرید انجام شده توسط کاربر: [display_accnt_order_counts]</li>
                </ul>
                
                <h3>سایر کدها:</h3>
                <ul>
                    <li>کد کوتاه برای نمایش 3 مورد لیست مورد علاقه کاربر شبیه دیجیکالا: [wishlist_products]</li>
                    <li>کد کوتاه برای نمایش یک پیام در صفحه حساب کاربری: [notification_message]</li>
                </ul>
                
                <h3>نمایش لیست مورد علاقه به صورت افقی:</h3>
                <table>
                    <tr valign="top">
                        <th scope="row">نمایش لیست مورد علاقه به صورت افقی:</th>
                        <td>
                            <input type="checkbox" id="my_wishlist_display_mode" name="my_wishlist_display_mode" value="1" <?php checked(1, $wishlist_display_mode, true); ?> />
                            <label for="my_wishlist_display_mode">فعال</label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

<div class="wrap21">
    <h2>افزونه کد های مورد نیاز حساب کاربری ووکامرس</h2>
    <form method="post" action="options.php">
        <?php settings_fields('user_woocode_settings_group');
do_settings_sections('user_woocode_settings');		?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">کد کوتاه قالب ساخته شده برای wishlist:</th>
                <td><input type="text" name="user_woocode_text_field" value="<?php echo esc_attr($current_value); ?>" />
				                    <li>قالب محصولات مورد علاقه را در المنتور ساخته و کد کوتاه آن را در کادر قرار دهید. سپس اقدام به بروزرسانی پیوند های یکتا سایت نمایید. تا صفحه wishlist به صورت اتوماتیک به حساب کاربری اضافه شود. </li>
</td>
            </tr>
            <tr valign="top">
                <th scope="row">پیام سفارشی:</th>
                <td><textarea name="notification_message" rows="5" cols="50" placeholder="پیام خود را وارد کنید..."><?php echo esc_attr($notification_message); ?></textarea>
								<li>شما می توانید پیام مورد نظر خود را از طریق کادر بالا بنویسیدو با کد کوتاه [notification_message] در سایت نمایش دهید</li></td>
            </tr>
        </table>
        <?php submit_button('ذخیره تغییرات'); ?>
    </form>
</div>

    </div>

    <?php
}

function user_woocode_settings_init() {
    register_setting('user_woocode_settings_group', 'user_woocode_text_field');
    register_setting('user_woocode_settings_group', 'my_wishlist_display_mode');
    register_setting('user_woocode_settings_group', 'notification_message');
}
add_action('admin_init', 'user_woocode_settings_init');



//include_once(plugin_dir_path( __FILE__ ) . 'order/woocommerce-order-details.php');
include_once(plugin_dir_path( __FILE__ ) . 'order/notfication.php');
include_once(plugin_dir_path( __FILE__ ) . 'order/replace-word.php');
include_once(plugin_dir_path( __FILE__ ) . 'order/replace-price.php');

?>