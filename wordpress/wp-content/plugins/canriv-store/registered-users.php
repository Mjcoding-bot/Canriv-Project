<?php
defined('ABSPATH') || exit;

add_filter('map_meta_cap', function ($caps, $cap, $actor_id, $args) {
    if (!in_array($cap, ['edit_user', 'edit_users', 'promote_user'], true) || empty($args[0]) ||
        (int) $args[0] === (int) $actor_id) {
        return $caps;
    }
    $target = get_userdata((int) $args[0]);
    return $target && in_array('customer', $target->roles, true) ? ['do_not_allow'] : $caps;
}, 10, 4);

function canriv_directory_query_args() {
    $params = [];
    foreach (['account_search', 'account_role'] as $key) {
        $value = $_GET[$key] ?? '';
        $params[$key] = is_string($value) ? sanitize_text_field(wp_unslash($value)) : '';
    }
    if (!isset(wp_roles()->roles[$params['account_role']]) || $params['account_role'] === 'subscriber') {
        $params['account_role'] = '';
    }
    $args = ['orderby' => ['registered' => 'DESC', 'ID' => 'DESC'], 'role__not_in' => ['subscriber']];
    if ($params['account_role'] !== '') {
        $args['role'] = $params['account_role'];
    }
    if ($params['account_search'] !== '') {
        $args['search'] = '*' . $params['account_search'] . '*';
        $args['search_columns'] = ['user_login', 'user_email', 'display_name'];
    }
    return [$args, array_filter($params, static fn($value) => $value !== '')];
}

function canriv_directory_role($user) {
    $labels = wp_roles()->get_names();
    return implode(', ', array_map(static fn($role) => translate_user_role($labels[$role] ?? $role), $user->roles)) ?: 'No role assigned';
}

add_action('admin_post_canriv_export_directory', function () {
    if (!current_user_can('manage_options')) {
        wp_die('Administrators only.', '', ['response' => 403]);
    }
    check_admin_referer('canriv_export_directory');
    [$args] = canriv_directory_query_args();
    nocache_headers();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="canriv-account-directory.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Username', 'Email', 'Role', 'Registered'], ',', '"', '');
    for ($page = 1; ; $page++) {
        $users = get_users($args + ['number' => 200, 'paged' => $page]);
        foreach ($users as $user) {
            $row = [$user->ID, $user->display_name, $user->user_login, $user->user_email, canriv_directory_role($user), $user->user_registered];
            $row = array_map(static fn($value) => preg_match('/^[\x00-\x20]*[=+@-]|^[\t\r\n]/', (string) $value) ? "'" . $value : $value, $row);
            fputcsv($output, $row, ',', '"', '');
        }
        if (count($users) < 200) {
            break;
        }
    }
    fclose($output);
    exit;
});

function canriv_registered_directory() {
    if (!current_user_can('manage_options')) {
        return '';
    }
    [$args, $filters] = canriv_directory_query_args();
    $base = canriv_page_url('registered-users');
    $url = static fn($params = []) => add_query_arg($filters + $params, $base);
    $page = isset($_GET['account_page']) && is_scalar($_GET['account_page']) ? max(1, absint($_GET['account_page'])) : 1;
    $size = 10;
    $total = (int) (new WP_User_Query($args + ['number' => 1, 'fields' => 'ID']))->get_total();
    $pages = max(1, (int) ceil($total / $size));
    $page = min($page, $pages);
    $users = get_users($args + ['number' => $size, 'paged' => $page]);
    $selected_id = isset($_GET['inspect_account']) && is_scalar($_GET['inspect_account']) ? absint($_GET['inspect_account']) : 0;
    $selected = $selected_id ? get_user_by('id', $selected_id) : ($users[0] ?? false);
    $counts = count_users();
    $roles = $counts['avail_roles'];
    $counts['total_users'] -= (int) ($roles['subscriber'] ?? 0);
    $staff = count(get_users(['role__in' => ['administrator', 'shop_manager'], 'fields' => 'ID']));
    $known_roles = ['customer', 'administrator', 'shop_manager'];
    $other = $counts['total_users'] - count(get_users(['role__in' => $known_roles, 'fields' => 'ID']));
    $month = (new WP_User_Query(['number' => 1, 'fields' => 'ID', 'role__not_in' => ['subscriber'], 'date_query' => [
        ['after' => current_time('Y-m-01 00:00:00', true), 'inclusive' => true],
    ]]))->get_total();
    $icon = static fn($name) => '<span class="material-symbols-outlined canriv-directory-icon" aria-hidden="true">' . esc_html($name) . '</span>';
    $initial = static fn($user) => esc_html(mb_strtoupper(mb_substr($user->display_name ?: $user->user_login, 0, 1)));
    $regions = WC()->countries->get_states('PH');
    $section = static function ($name) use ($users, $selected, $icon, $initial, $regions, $url, $page, $pages, $filters, $base, $roles, $counts) {
        ob_start();
        require __DIR__ . '/assets/registered-users-data.php';
        return ob_get_clean();
    };
    return strtr(file_get_contents(__DIR__ . '/assets/registered-users.html'), [
        '%%order-management%%' => esc_url(canriv_page_url('order-management')),
        '%%registered-users%%' => esc_url($base),
        '%%canriv-analytics%%' => esc_url(canriv_page_url('canriv-analytics')),
        '%%home%%' => esc_url(canriv_page_url('home')),
        '%%export%%' => esc_url(wp_nonce_url(add_query_arg($filters + ['action' => 'canriv_export_directory'], admin_url('admin-post.php')), 'canriv_export_directory')),
        '%%wpusers%%' => esc_url(admin_url('users.php')),
        '%%newuser%%' => esc_url(admin_url('user-new.php')),
        '%%metric1%%' => number_format_i18n($counts['total_users']),
        '%%metric2%%' => number_format_i18n($roles['customer'] ?? 0),
        '%%metric4%%' => number_format_i18n($staff),
        '%%metric5%%' => number_format_i18n($other),
        '%%month%%' => '+' . number_format_i18n($month),
        '%%range%%' => $total ? sprintf('Showing %d–%d of %s', ($page - 1) * $size + 1, min($page * $size, $total), number_format_i18n($total)) : 'No matches',
        '%%rows%%' => $section('rows'),
        '%%filters%%' => $section('filters'),
        '%%pagination%%' => $section('pagination'),
        '%%inspector%%' => $section('inspector'),
    ]);
}
