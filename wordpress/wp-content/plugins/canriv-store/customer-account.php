<?php
defined('ABSPATH') || exit;

add_filter('body_class', function ($classes) {
    if (is_account_page() && is_wc_endpoint_url('view-order')) {
        $classes[] = 'canriv-view-order-page';
    }
    return $classes;
});

add_action('woocommerce_view_order', function () {
    echo '<a class="canriv-account-back-link" href="' . esc_url(wc_get_page_permalink('myaccount')) . '"><span class="material-symbols-outlined" aria-hidden="true">arrow_back</span> Back to My Account</a>';
}, 1);

add_action('template_redirect', function () {
    if (is_user_logged_in() && is_account_page() && is_wc_endpoint_url('edit-account')) {
        wp_safe_redirect(wc_get_page_permalink('myaccount') . '#canriv-password');
        exit;
    }
});

function canriv_password_is_allowed($password, $email) {
    $length = mb_strlen($password, 'UTF-8');
    $common = ['password', 'password1', '12345678', '123456789', 'qwerty123', 'password1234', 'password12345', '123456789012', 'qwertyuiop123', 'canriv123456'];
    return $length >= 8 && $length <= 128 && $password === trim($password) &&
        !preg_match('/^(.)\1+$/us', $password) && !in_array(strtolower($password), $common, true) &&
        strcasecmp($password, (string) strstr($email, '@', true)) !== 0;
}

function canriv_customer_account() {
    if (!is_user_logged_in() || is_wc_endpoint_url()) {
        return do_shortcode('[woocommerce_my_account]');
    }

    $user = wp_get_current_user();
    $customer = new WC_Customer($user->ID);
    $orders = wc_get_orders(['customer_id' => $user->ID, 'limit' => 6, 'paginate' => true, 'orderby' => 'date', 'order' => 'DESC']);
    $requests = get_posts([
        'post_type' => 'canriv_request', 'post_status' => 'private', 'author' => $user->ID,
        'numberposts' => 6, 'meta_key' => 'canriv_request_type', 'meta_value' => 'reserve',
    ]);
    $provinces = WC()->countries->get_states('PH');
    $initials = mb_substr($user->first_name ?: $user->display_name, 0, 1) . mb_substr($user->last_name, 0, 1);
    ob_start();
    require __DIR__ . '/assets/customer-account.php';
    return ob_get_clean();
}

add_action('admin_post_canriv_save_customer_profile', function () {
    check_admin_referer('canriv_save_customer_profile');
    $user_id = get_current_user_id();
    $fields = ['first_name', 'last_name', 'display_name', 'email', 'phone', 'address_1', 'address_2', 'barangay', 'city', 'state', 'postcode'];
    $values = [];
    foreach ($fields as $field) {
        $value = $_POST[$field] ?? '';
        $values[$field] = is_string($value) ? sanitize_text_field(wp_unslash($value)) : '';
    }
    $finish = static function ($status) {
        wp_safe_redirect(add_query_arg('profile_status', $status, wc_get_page_permalink('myaccount')) . '#canriv-profile', 303);
        exit;
    };
    $phone = canriv_normalize_ph_mobile($values['phone']);
    if ($phone === false) {
        $finish('phone_invalid');
    }
    if (!$values['first_name'] || !$values['last_name'] || !$values['display_name'] || !$values['address_1'] || !$values['barangay'] ||
        !$values['city'] || !isset(WC()->countries->get_states('PH')[$values['state']]) ||
        !preg_match('/^\d{4}$/', $values['postcode']) || !is_email($values['email'])) {
        $finish('invalid');
    }
    $email_owner = email_exists($values['email']);
    if ($email_owner && (int) $email_owner !== $user_id) {
        $finish('email_used');
    }
    $password_field = static function ($field) {
        $value = $_POST[$field] ?? '';
        return is_string($value) ? wp_unslash($value) : '';
    };
    $current = $password_field('password_current');
    $password = $password_field('password_new');
    $confirm = $password_field('password_confirm');
    $change_password = $current !== '' || $password !== '' || $confirm !== '';
    if ($change_password) {
        if ($current === '' || !wp_check_password($current, wp_get_current_user()->user_pass, $user_id)) {
            $finish('password_current');
        }
        if ($password !== $confirm || !canriv_password_is_allowed($password, $values['email'])) {
            $finish('password_invalid');
        }
    }
    $customer = new WC_Customer($user_id);
    $customer->set_first_name($values['first_name']);
    $customer->set_last_name($values['last_name']);
    $customer->set_display_name($values['display_name']);
    if ($change_password) {
        $customer->set_password($password);
    }
    $customer->set_email($values['email']);
    $customer->set_billing_email($values['email']);
    $customer->set_billing_phone($phone);
    foreach (['first_name', 'last_name', 'address_1', 'city', 'state', 'postcode', 'country'] as $field) {
        $value = $field === 'country' ? 'PH' : $values[$field];
        $customer->set_props(['billing_' . $field => $value, 'shipping_' . $field => $value]);
    }
    $address_extra = implode(', ', array_filter([$values['address_2'], $values['barangay']]));
    $customer->set_shipping_address_2($address_extra);
    $customer->set_billing_address_2($address_extra);
    $customer->update_meta_data('canriv_address_unit', $values['address_2']);
    $customer->update_meta_data('canriv_address_barangay', $values['barangay']);
    $notes = $_POST['installation_notes'] ?? '';
    $customer->update_meta_data('canriv_installation_notes', is_string($notes) ? mb_substr(sanitize_textarea_field(wp_unslash($notes)), 0, 500) : '');
    $customer->save();
    $finish('saved');
});
