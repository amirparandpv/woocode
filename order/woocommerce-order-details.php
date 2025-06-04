<?php
// افزودن فیلدهای سفارشی به صفحه سفارش در داشبورد مدیریت
function add_custom_order_meta_fields($order) {
    // بررسی وضعیت تنظیمات برای غیرفعال کردن نمایش جزئیات سفارش
    $disable_order_details = get_option('disable_order_details');

    // اگر تنظیمات برای غیرفعال کردن جزئیات سفارش فعال باشد، کد اجرا نمی‌شود
    if ($disable_order_details) {
        return;
    }

    // بقیه کد جزئیات سفارش را اینجا قرار دهید
    $tracking_number = get_post_meta($order->get_id(), '_tracking_number', true);
    $order_date = $order->get_date_created()->format('Y-m-d H:i:s');
    $shipping_first_name = $order->get_shipping_first_name();
    $shipping_last_name = $order->get_shipping_last_name();
    $shipping_phone = $order->get_billing_phone();
    $shipping_address = $order->get_formatted_shipping_address();
    $shipping_cost = $order->get_shipping_total();
    $payment_method = $order->get_payment_method_title();
    $order_total = $order->get_total();
    $order_status = wc_get_order_status_name($order->get_status());
    
    echo '<div class="order-details-wrapper">';

    echo '<div class="order-details-content">';
    echo '<div class="order-details-info">';
    echo '<h3 class="order-details-title">' . __('جزئیات سفارش', 'woocommerce-order-details') . '</h2>';
    echo '<ul class="order-details-info-list">';
    echo '<li><span class="info-label">' . __('شماره سفارش محصول: ', 'woocommerce-order-details') . '</span><span class="info-value">' . esc_html($order->get_order_number()) . '</span></li>';
    echo '<li><span class="info-label">' . __('تاریخ ثبت سفارش:', 'woocommerce-order-details') . '</span><span class="info-value">' . esc_html($order_date) . '</span></li>';
    echo '<li><span class="info-label">' . __('نام و نام خانوادگی تحویل گیرنده:', 'woocommerce-order-details') . '</span><span class="info-value">' . esc_html($shipping_first_name . ' ' . $shipping_last_name) . '</span></li>';
    echo '<li><span class="info-label">' . __('شماره موبایل:', 'woocommerce-order-details') . '</span><span class="info-value">' . esc_html($shipping_phone) . '</span></li>';
    echo '<li><span class="info-label">' . __('آدرس تحویل:', 'woocommerce-order-details') . '</span><span class="info-value">' . esc_html($shipping_address) . '</span></li>';
    echo '</ul>';
    echo '<ul class="order-details-info-list">';
    echo '<li><span class="info-label">' . __('هزینه ارسال:', 'woocommerce-order-details') . '</span><span class="info-value">' . wc_price($shipping_cost) . '</span></li>';
    echo '<li><span class="info-label">' . __('نوع پرداخت:', 'woocommerce-order-details') . '</span><span class="info-value">' . esc_html($payment_method) . '</span></li>';
    echo '<li><span class="info-label">' . __('قیمت نهایی سفارش:', 'woocommerce-order-details') . '</span><span class="info-value">' . wc_price($order_total) . '</span></li>';
    echo '<li><span class="info-label">' . __('وضعیت سفارش:', 'woocommerce-order-details') . '</span><span class="info-value">' . esc_html($order_status) . '</span></li>';
    echo '</ul>';
    echo '<div class="ce1">';
    echo '<ul class="order-details-info-list2">';
    echo '<li><span class="info-label" style="width: 100% !important;">با استفاده از سامانه رهگیری پست می‌توانید از وضعیت مرسوله باخبر شوید.</span></li>';

    echo '<li><span class="info-label">' . __('کد رهگیری  ارسال محصول :', 'woocommerce-order-details') . '</span><span class="info-value">' . esc_html($tracking_number) . '</span></li>';

    echo '</ul>';
    echo '</div>';

    echo '</div>'; // بسته‌کردن .order-details-info


    echo '<div class="order-products">';
    echo '<h3 class="order-products-title">' . __('محصولات خریداری شده', 'woocommerce-order-details') . '</h3>';
    echo '<ul class="order-products-list">';
    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $product_name = $product ? $product->get_name() : __('محصول حذف شده', 'woocommerce');
        $product_price = $item->get_total();
        $product_quantity = $item->get_quantity();
        echo '<li class="product-item">';
        echo '<div class="product-thumbnail">' . $product->get_image() . '</div>';
        echo '<div class="product-details">';
        echo '<span class="product-name">' . esc_html($product_name) . '</span>';echo '<br>';
        echo '<span class="product-price">' . wc_price($product_price) . '</span>';echo '<br>';
        echo '<span class="product-quantity">' . __('تعداد:', 'woocommerce-order-details') . ' ' . esc_html($product_quantity) . '</span>';echo '<br>';
        echo '</div>'; // بسته‌کردن .product-details
        echo '</li>'; // بسته‌کردن .product-item
    }
    echo '</ul>'; // بسته‌کردن .order-products-list
    echo '</div>'; // بسته‌کردن .order-products
    echo '</div>'; // بسته‌کردن .order-details-content
    echo '</div>'; // بسته‌کردن .order-details-wrapper

}
add_action('woocommerce_order_details_after_order_table_items', 'add_custom_order_meta_fields');
?>
