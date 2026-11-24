<?php
defined('ABSPATH') || exit;

function canriv_order_decision_label($order) {
    if (!$order) {
        return '';
    }
    if (in_array($order->get_status(), ['pending', 'on-hold', 'processing'], true)) {
        return $order->get_meta('canriv_admin_decision') === 'accepted'
            ? 'Accepted'
            : 'Pending';
    }
    return wc_get_order_status_name($order->get_status());
}

add_filter('woocommerce_order_details_status', function ($text, $order) {
    $label = canriv_order_decision_label($order);
    return preg_replace_callback('/(<mark class="order-status">).*?(<\/mark>)/', static function ($match) use ($label) {
        return $match[1] . esc_html($label) . $match[2];
    }, $text, 1);
}, 10, 2);

function canriv_order_filters() {
    $statuses = wc_get_order_statuses();
    $params = [];
    foreach (['order_ref', 'order_state', 'order_period', 'order_view'] as $key) {
        $value = $_GET[$key] ?? '';
        $params[$key] = is_string($value) ? sanitize_text_field(wp_unslash($value)) : '';
    }
    $args = ['type' => 'shop_order', 'orderby' => 'date', 'order' => 'DESC', 'status' => array_keys($statuses)];
    if (isset($statuses[$params['order_state']])) {
        $args['status'] = [$params['order_state']];
    }
    if (in_array($params['order_period'], ['7', '30', '90'], true)) {
        $args['date_created'] = '>=' . (time() - (int) $params['order_period'] * DAY_IN_SECONDS);
    }
    if ($params['order_ref'] !== '') {
        $ref = ltrim($params['order_ref'], '#');
        $args['post__in'] = [ctype_digit($ref) && (int) $ref > 0 ? (int) $ref : PHP_INT_MAX];
    }
    return [$args, array_filter($params, static fn($value) => $value !== ''), $statuses];
}

add_action('admin_post_canriv_export_orders', function () {
    if (!current_user_can('manage_options')) {
        wp_die('Administrators only.', '', ['response' => 403]);
    }
    check_admin_referer('canriv_export_orders');
    [$args] = canriv_order_filters();
    nocache_headers();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="canriv-orders.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Order', 'Placed', 'Customer', 'Status', 'Total', 'Currency'], ',', '"', '');
    for ($page = 1; ; $page++) {
        $orders = wc_get_orders($args + ['limit' => 100, 'page' => $page]);
        foreach ($orders as $order) {
            $row = [$order->get_order_number(), $order->get_date_created()?->date(DATE_ATOM), $order->get_formatted_billing_full_name(),
                canriv_order_decision_label($order), $order->get_total(), $order->get_currency()];
            fputcsv($out, array_map(static fn($value) => preg_match('/^[\x00-\x20]*[=+@-]|^[\t\r\n]/', (string) $value) ? "'" . $value : $value, $row), ',', '"', '');
        }
        if (count($orders) < 100) {
            break;
        }
    }
    fclose($out);
    exit;
});

add_action('admin_post_canriv_update_request', function () {
    if (!current_user_can('manage_options')) wp_die('Administrators only.', '', ['response' => 403]);
    $id = absint($_POST['request_id'] ?? 0);
    check_admin_referer('canriv_update_request_' . $id);
    $status = isset($_POST['request_status']) && is_string($_POST['request_status']) ? sanitize_key(wp_unslash($_POST['request_status'])) : '';
    $type = get_post_meta($id, 'canriv_request_type', true);
    $return_args = ['order_view' => $type === 'reserve' ? 'reservations' : 'quotes', 'inspect_request' => $id];
    $return = add_query_arg($return_args, canriv_page_url('order-management')) . '#inspector-panel';
    if (get_post_type($id) !== 'canriv_request' || !in_array($type, ['reserve', 'contact', 'rfq', 'home'], true) || !in_array($status, ['accepted', 'declined'], true)) {
        wp_die('This request can no longer be updated.', '', ['response' => 400]);
    }
    $current_status = get_post_meta($id, 'canriv_request_status', true) ?: 'pending';
    if ($current_status === 'pending') {
        update_post_meta($id, 'canriv_request_status', $status);
        $return = add_query_arg('request_update', 'saved', $return);
    } else {
        $return = add_query_arg('request_update', $current_status === $status ? 'already_saved' : 'already_decided', $return);
    }
    wp_safe_redirect($return);
    exit;
});

add_action('admin_post_canriv_update_order', function () {
    if (!current_user_can('manage_options')) wp_die('Administrators only.', '', ['response' => 403]);
    $id = absint($_POST['order_id'] ?? 0);
    check_admin_referer('canriv_update_order_' . $id);
    $order = wc_get_order($id);
    $decision = isset($_POST['decision']) && is_string($_POST['decision']) ? sanitize_key(wp_unslash($_POST['decision'])) : '';
    if (!$order || !in_array($decision, ['accept', 'decline', 'complete'], true)) {
        wp_die('This order can no longer be updated.', '', ['response' => 400]);
    }
    if ($decision === 'complete') {
        if (!$order->has_status('processing') || $order->get_meta('canriv_admin_decision') !== 'accepted') {
            wp_die('Accept the order before marking it complete.', '', ['response' => 400]);
        }
        $order->update_status('completed', 'Order marked complete by an administrator.');
    } else {
        if (!in_array($order->get_status(), ['pending', 'on-hold', 'processing'], true) || $order->get_meta('canriv_admin_decision') === 'accepted') {
            wp_die('This order can no longer be updated.', '', ['response' => 400]);
        }
        if ($decision === 'accept') {
            if ($order->get_status() !== 'processing') $order->update_status('processing', 'Order accepted by an administrator.');
            $order->update_meta_data('canriv_admin_decision', 'accepted');
            $order->save();
        } else {
            $order->update_status('cancelled', $order->is_paid()
                ? 'Order declined by an administrator. Payment was captured; issue a refund manually.'
                : 'Order declined by an administrator.');
        }
    }
    wp_safe_redirect(add_query_arg(['order_view' => 'orders', 'inspect_order' => $id], canriv_page_url('order-management')) . '#inspector-panel');
    exit;
});

function canriv_order_directory() {
    if (!current_user_can('manage_options')) {
        return '';
    }
    [$args, $filters, $statuses] = canriv_order_filters();
    $base = canriv_page_url('order-management');
    $view = $filters['order_view'] ?? '';
    $view = in_array($view, ['reservations', 'quotes', 'requests'], true) ? $view : 'orders';
    $url = static fn($params = []) => add_query_arg($params + $filters, $base);
    $page = isset($_GET['orders_page']) && is_scalar($_GET['orders_page']) ? max(1, absint($_GET['orders_page'])) : 1;
    $query = wc_get_orders($args + ['limit' => 10, 'page' => $page, 'paginate' => true]);
    $pages = max(1, (int) $query->max_num_pages);
    if ($page > $pages) {
        $page = $pages;
        $query = wc_get_orders($args + ['limit' => 10, 'page' => $page, 'paginate' => true]);
    }
    $orders = $query->orders;
    $requested_id = isset($_GET['inspect_order']) && is_scalar($_GET['inspect_order']) ? absint($_GET['inspect_order']) : 0;
    $selected = $view === 'orders' ? ($orders[0] ?? null) : null;
    foreach ($orders as $order) {
        if ($order->get_id() === $requested_id && $view === 'orders') {
            $selected = $order;
        }
    }
    $request_args = ['post_type' => 'canriv_request', 'post_status' => 'private', 'posts_per_page' => -1];
    $request_query = new WP_Query($request_args + ['meta_key' => 'canriv_request_type', 'meta_value' => 'reserve']);
    $quote_query = new WP_Query($request_args + ['meta_key' => 'canriv_request_type', 'meta_value' => ['rfq', 'home', 'contact'], 'meta_compare' => 'IN']);
    $all_requests_query = new WP_Query($request_args + ['meta_key' => 'canriv_request_type', 'meta_value' => ['reserve', 'rfq', 'home', 'contact'], 'meta_compare' => 'IN']);
    $requests = $view === 'quotes' ? $quote_query->posts : ($view === 'reservations' ? $request_query->posts : $all_requests_query->posts);
    $requested_request_id = isset($_GET['inspect_request']) && is_scalar($_GET['inspect_request']) ? absint($_GET['inspect_request']) : 0;
    $selected_request = null;
    foreach ($requests as $request) {
        if ((int) $request->ID === $requested_request_id) {
            $selected_request = $request;
            break;
        }
    }
    $count_orders = array_sum(array_map(static fn($status) => wc_orders_count(substr($status, 3)), array_keys($statuses)));
    $active = wc_orders_count('processing') + wc_orders_count('pending') + wc_orders_count('on-hold');
    $icon = static fn($name) => '<span class="material-symbols-outlined canriv-directory-icon" aria-hidden="true">' . esc_html($name) . '</span>';
    $section = static function ($name) use ($view, $base, $url, $page, $pages, $orders, $selected, $requests, $selected_request, $icon, $filters, $statuses, $count_orders, $request_query, $quote_query, $all_requests_query) {
        ob_start();
        require __DIR__ . '/assets/order-management-data.php';
        return ob_get_clean();
    };
    $create = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()
        ? 'admin.php?page=wc-orders&action=new' : 'post-new.php?post_type=shop_order';
    $template = file_get_contents(__DIR__ . '/assets/order-management.html');
    if ($view !== 'orders') {
        $template = str_replace('<div class="canriv-directory canriv-orders ', '<div class="canriv-directory canriv-orders canriv-requests-view ', $template);
    }
    return strtr($template, [
        '%%order-management%%' => esc_url($base),
        '%%registered-users%%' => esc_url(canriv_page_url('registered-users')),
        '%%canriv-analytics%%' => esc_url(canriv_page_url('canriv-analytics')),
        '%%home%%' => esc_url(canriv_page_url('home')),
        '%%export%%' => esc_url(wp_nonce_url(add_query_arg($filters + ['action' => 'canriv_export_orders'], admin_url('admin-post.php')), 'canriv_export_orders')),
        '%%neworder%%' => esc_url(admin_url($create)),
        '%%metric1%%' => number_format_i18n($count_orders),
        '%%metric2%%' => number_format_i18n($active),
        '%%metric3%%' => number_format_i18n($request_query->found_posts),
        '%%metric4%%' => number_format_i18n(wc_orders_count('completed')),
        '%%range%%' => '<strong>WooCommerce orders</strong><span> · ' . esc_html(number_format_i18n($query->total)) . ' matching records</span>',
        '%%tabs%%' => $section('tabs'),
        '%%filters%%' => $view === 'orders' ? $section('filters') : '',
        '%%rows%%' => $section('rows'),
        '%%pagination%%' => $section('pagination'),
        '%%reservations%%' => $section('requests'),
        '%%inspector%%' => $section('inspector'),
    ]);
}
