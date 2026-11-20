<?php
defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', function () {
    $site_kit = get_option('googlesitekit_analytics-4_settings', []);
    if ((defined('GOOGLESITEKIT_VERSION') && !empty($site_kit['measurementID']) && !empty($site_kit['useSnippet'])) ||
        current_user_can('manage_options') || is_preview() || is_cart() || is_checkout() || is_account_page() ||
        is_page(['order-management', 'registered-users', 'canriv-analytics']) ||
        !is_singular(['page', 'product']) || post_password_required()) {
        return;
    }
    $measurement_id = 'G-DDNEQZXPBR';
    $page_url = strtok(get_permalink(get_queried_object_id()), '?');
    wp_enqueue_script('canriv-ga4', 'https://www.googletagmanager.com/gtag/js?id=' . $measurement_id, [], null, ['strategy' => 'async']);
    wp_add_inline_script('canriv-ga4', 'window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag("js", new Date());
gtag("config", ' . wp_json_encode($measurement_id) . ', ' . wp_json_encode([
        'page_location' => $page_url,
        'allow_google_signals' => false,
        'allow_ad_personalization_signals' => false,
    ], JSON_HEX_TAG) . ');', 'before');
});

add_action('init', function () {
    if (get_option('canriv_audience_schema') === '1') {
        return;
    }
    global $wpdb;
    $table = $wpdb->prefix . 'canriv_audience_daily';
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta("CREATE TABLE $table (
        day date NOT NULL,
        object_id bigint(20) unsigned NOT NULL,
        activity varchar(20) NOT NULL,
        hits bigint(20) unsigned NOT NULL DEFAULT 0,
        PRIMARY KEY  (day,object_id,activity)
    ) " . $wpdb->get_charset_collate() . ';');
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table))) === $table) {
        update_option('canriv_audience_schema', '1');
    }
});

function canriv_record_audience_activity($id, $activity) {
    if (current_user_can('manage_options') || get_option('canriv_audience_schema') !== '1') {
        return;
    }
    global $wpdb;
    $table = $wpdb->prefix . 'canriv_audience_daily';
    $wpdb->query($wpdb->prepare(
        "INSERT INTO $table (day, object_id, activity, hits) VALUES (%s, %d, %s, 1)
         ON DUPLICATE KEY UPDATE hits = hits + 1",
        current_time('Y-m-d'), $id, $activity
    ));
}

add_action('template_redirect', function () {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET' || is_preview() || is_feed() ||
        !is_singular(['page', 'product']) || is_cart() || is_checkout() || is_account_page() ||
        is_page(['order-management', 'registered-users', 'canriv-analytics'])) {
        return;
    }
    $id = get_queried_object_id();
    if (get_post_status($id) === 'publish' && !post_password_required($id)) {
        canriv_record_audience_activity($id, is_singular('product') ? 'product_view' : 'page_view');
    }
}, 20);

add_action('woocommerce_add_to_cart', function ($cart_key, $product_id) {
    if (!is_admin() || wp_doing_ajax()) {
        canriv_record_audience_activity($product_id, 'add_to_cart');
    }
}, 10, 2);

function canriv_audience_dashboard() {
    if (!current_user_can('manage_options')) {
        return '';
    }
    global $wpdb;
    $period = $_GET['audience_days'] ?? '';
    $days = is_string($period) && in_array($period, ['1', '7', '30'], true) ? (int) $period : 30;
    $start = current_datetime()->setTime(0, 0)->modify('-' . ($days - 1) . ' days');
    $start_day = $start->format('Y-m-d');
    $table = $wpdb->prefix . 'canriv_audience_daily';
    $activity = ['page_view' => 0, 'product_view' => 0, 'add_to_cart' => 0];
    $daily = [];
    for ($day = $start; $day <= current_datetime(); $day = $day->modify('+1 day')) {
        $daily[$day->format('Y-m-d')] = 0;
    }
    $pages = [];
    $products = [];
    if (get_option('canriv_audience_schema') === '1') {
        $totals = $wpdb->get_results($wpdb->prepare("SELECT activity, SUM(hits) AS total FROM $table WHERE day >= %s GROUP BY activity", $start_day));
        foreach ($totals as $row) {
            $activity[$row->activity] = (int) $row->total;
        }
        $by_day = $wpdb->get_results($wpdb->prepare("SELECT day, SUM(hits) AS total FROM $table WHERE day >= %s AND activity IN ('page_view','product_view') GROUP BY day", $start_day));
        foreach ($by_day as $row) {
            if (isset($daily[$row->day])) {
                $daily[$row->day] = (int) $row->total;
            }
        }
        $pages = $wpdb->get_results($wpdb->prepare("SELECT a.object_id, SUM(a.hits) AS views
            FROM $table a JOIN $wpdb->posts p ON p.ID = a.object_id
            WHERE a.day >= %s AND a.activity IN ('page_view','product_view') AND p.post_status = 'publish'
            GROUP BY a.object_id ORDER BY views DESC, a.object_id ASC LIMIT 8", $start_day));
        $products = $wpdb->get_results($wpdb->prepare("SELECT a.object_id,
            SUM(CASE WHEN a.activity = 'product_view' THEN a.hits ELSE 0 END) AS views,
            SUM(CASE WHEN a.activity = 'add_to_cart' THEN a.hits ELSE 0 END) AS carts
            FROM $table a JOIN $wpdb->posts p ON p.ID = a.object_id
            WHERE a.day >= %s AND p.post_type = 'product' AND p.post_status = 'publish'
            GROUP BY a.object_id HAVING views > 0 OR carts > 0 ORDER BY views DESC, carts DESC, a.object_id ASC LIMIT 15", $start_day));
    }
    $orders = wc_get_orders(['limit' => 1, 'paginate' => true, 'date_created' => '>=' . $start->getTimestamp(),
        'status' => array_keys(wc_get_order_statuses())]);
    $request_count = static fn($types) => (new WP_Query([
        'post_type' => 'canriv_request', 'post_status' => 'private', 'posts_per_page' => 1, 'fields' => 'ids',
        'meta_key' => 'canriv_request_type', 'meta_value' => $types, 'meta_compare' => 'IN',
        'date_query' => [['after' => $start->format('Y-m-d H:i:s'), 'inclusive' => true]],
    ]))->found_posts;
    $analytics = get_option('googlesitekit_analytics-4_settings', []);
    $status = empty($analytics['measurementID']) ? 'Local activity · GA4 tag installed' : 'Local activity · GA4 configured in Site Kit';
    $base = canriv_page_url('canriv-analytics');
    $section = static function ($name) use ($days, $start, $daily, $pages, $products, $base) {
        ob_start();
        require __DIR__ . '/assets/canriv-analytics-data.php';
        return ob_get_clean();
    };
    return strtr(file_get_contents(__DIR__ . '/assets/canriv-analytics.html'), [
        '%%order-management%%' => esc_url(canriv_page_url('order-management')),
        '%%registered-users%%' => esc_url(canriv_page_url('registered-users')),
        '%%canriv-analytics%%' => esc_url($base),
        '%%home%%' => esc_url(canriv_page_url('home')),
        '%%analytics-status%%' => esc_html($status),
        '%%sitekit%%' => esc_url(admin_url('admin.php?page=googlesitekit-dashboard')),
        '%%woocommerce%%' => esc_url(admin_url('admin.php?page=wc-admin&path=/analytics/overview')),
        '%%metric1%%' => number_format_i18n($activity['page_view'] + $activity['product_view']),
        '%%metric2%%' => number_format_i18n($activity['product_view']),
        '%%metric3%%' => number_format_i18n($activity['add_to_cart']),
        '%%metric4%%' => number_format_i18n($orders->total),
        '%%metric5%%' => number_format_i18n($request_count(['reserve'])),
        '%%metric6%%' => number_format_i18n($request_count(['rfq', 'home'])),
        '%%filters%%' => $section('filters'),
        '%%traffic%%' => $section('traffic'),
        '%%daily%%' => $section('daily'),
        '%%product-heading%%' => $section('product-heading'),
        '%%products%%' => $section('products'),
        '%%product-footer%%' => $section('product-footer'),
    ]);
}
