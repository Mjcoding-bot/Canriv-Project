<?php
/**
 * Plugin Name: Canriv Store
 * Description: Canriv's Elementor storefront forms and staff pages.
 * Requires Plugins: elementor, woocommerce
 */

defined('ABSPATH') || exit;

function canriv_page_url($slug) {
    $pages = get_option('canriv_pages', []);
    if (!empty($pages[$slug])) {
        return get_permalink($pages[$slug]);
    }
    return home_url('/?pagename=' . rawurlencode($slug));
}

function canriv_header() {
    $links = ['home' => 'Home', 'company-story' => 'Company Story', 'products-services' => 'Products & Services', 'catalog' => 'Shop', 'reviews' => 'Reviews', 'careers' => 'Careers', 'contact' => 'Contact'];
    if (!empty(get_option('canriv_pages', [])['completed-projects'])) {
        $links = array_slice($links, 0, 3, true) + ['completed-projects' => 'Completed Projects'] + $links;
    }
    $navigation = '';
    foreach ($links as $slug => $label) {
        $current = ($slug === 'home' && is_front_page()) || is_page((int) (get_option('canriv_pages', [])[$slug] ?? 0));
        $navigation .= '<a href="' . esc_url(canriv_page_url($slug)) . '"' . ($current ? ' aria-current="page"' : '') . '>' . esc_html($label) . '</a>';
    }
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $account = esc_url(canriv_page_url('account'));
    return '<header class="canriv-site-header"><div class="canriv-site-header-inner">' .
        '<a class="canriv-site-brand" href="' . esc_url(canriv_page_url('home')) . '" aria-label="Canriv home">' . canriv_snowman('canriv-site-monogram') . '<span>CANRIV<small>AIRCON SUPPLY &amp; INSTALLATION</small></span></a>' .
        '<nav class="canriv-site-nav" aria-label="Main navigation">' . $navigation . '</nav>' .
        '<div class="canriv-site-actions"><a class="canriv-site-cart" href="' . esc_url(canriv_page_url('cart')) . '" aria-label="Cart, ' . (int) $count . ' items">' .
        '<span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span><span class="canriv-site-cart-count">' . (int) $count . '</span></a>' .
        '<a class="canriv-site-signin" href="' . $account . '">' . (is_user_logged_in() ? 'My Account' : 'Sign In') . '</a>' .
        (is_user_logged_in() ? '' : '<a class="canriv-site-register" href="' . esc_url(canriv_page_url('registration')) . '">Register</a>') .
        '<a class="canriv-site-profile" href="' . $account . '" aria-label="My account"><span class="material-symbols-outlined" aria-hidden="true">person</span></a></div>' .
        '<details class="canriv-site-mobile"><summary><span class="material-symbols-outlined" aria-hidden="true">menu</span>Menu</summary>' .
        '<nav aria-label="Mobile navigation">' . $navigation . '<a href="' . $account . '">' . (is_user_logged_in() ? 'My Account' : 'Sign In') . '</a></nav></details></div></header>';
}

function canriv_snowman($class) {
    return '<img class="' . esc_attr($class) . '" src="' . esc_url(plugins_url('assets/canriv-snowman.png', __FILE__)) . '" width="48" height="48" alt="">';
}

function canriv_company_section($section) {
    ob_start();
    require __DIR__ . '/assets/company-information.php';
    return ob_get_clean();
}

function canriv_career_roles() {
    return [
        'hr-officer' => ['HR Officer', 'office', 'Coordinates recruitment and onboarding, maintains employee records, and helps staff with workplace questions.'],
        'purchasing' => ['Purchasing', 'office', 'Sources equipment and supplies, compares supplier quotes, and tracks orders and delivery dates.'],
        'project-supervisor' => ['Project Supervisor', 'operations', 'Coordinates installation crews, schedules, and materials on site, and tracks each project through handover.'],
        'company-driver' => ['Company Driver', 'operations', 'Transports staff, tools, and air conditioning equipment between the office and job sites.'],
        'ac-electrician' => ['AC Electrician', 'technical', 'Handles the electrical connections and checks required to install and service air conditioning units.'],
        'ac-installer' => ['AC Installer', 'technical', 'Mounts indoor and outdoor units, connects piping and drainage, and tests the equipment before handover.'],
        'head-engineer' => ['Head Engineer', 'technical', 'Leads the engineering team, reviews equipment requirements and project plans, and guides technical decisions on site.'],
        'sales-marketing-assistant' => ['Sales and Marketing Assistant', 'office', 'Responds to product inquiries, helps prepare quotations, and supports promotions and customer follow-ups.'],
        'safety-officer' => ['Safety Officer', 'operations', 'Helps crews identify site hazards, follow safe work practices, and document safety checks.'],
        'billing-collection' => ['Billing and Collection', 'office', 'Prepares invoices, records customer payments, and follows up on outstanding balances.'],
        'supervisor' => ['Supervisor', 'operations', 'Assigns day-to-day tasks, monitors team progress, and helps resolve operational issues.'],
    ];
}

function canriv_career_role_markup($format) {
    $groups = ['office' => ['Office', 'person'], 'operations' => ['Operations', 'inventory_2'], 'technical' => ['Technical', 'engineering']];
    $html = '';
    foreach (canriv_career_roles() as $slug => [$title, $group, $description]) {
        if ($format === 'career-options') {
            $html .= '<option value="' . esc_attr($slug) . '">' . esc_html($title) . '</option>';
            continue;
        }
        [$department, $icon] = $groups[$group];
        $html .= '<li class="canriv-career-role" data-job-role="' .
            esc_attr($slug) . '" data-job-category="' . esc_attr($group) . '">' .
            '<span class="material-symbols-outlined canriv-career-icon" aria-hidden="true">' . esc_html($icon) . '</span>' .
            '<div class="canriv-career-copy"><p class="font-label-code text-label-sm uppercase text-primary">' . esc_html($department) . '</p>' .
            '<h3 class="font-headline-sm text-headline-sm text-on-surface">' . esc_html($title) . '</h3>' .
            '<p class="canriv-career-description">' . esc_html($description) . '</p></div>' .
            '<a class="canriv-career-interest" href="#apply-form" data-apply-role="' . esc_attr($slug) .
            '" aria-label="' . esc_attr('Express interest in ' . $title) . '">Express interest <span aria-hidden="true">→</span></a></li>';
    }
    return $html;
}

function canriv_footer() {
    $groups = [
        'Company' => ['company-story' => 'Company story', 'products-services' => 'Products & services', 'completed-projects' => 'Completed projects', 'careers' => 'Careers', 'contact' => 'Contact us'],
        'Customers' => ['catalog' => 'Shop equipment', 'reservation' => 'Request pickup', 'reviews' => 'Product reviews', 'account' => 'My account'],
    ];
    $icon = static fn($name) => '<span class="material-symbols-outlined" aria-hidden="true">' . esc_html($name) . '</span>';
    $html = '<footer class="canriv-footer" aria-label="Site footer">' .
        '<div class="canriv-footer-cta"><div><span class="canriv-footer-eyebrow">YOUR NEXT PROJECT STARTS HERE</span>' .
        '<h2>Better air.<br><span>Better spaces.</span></h2><p>Find the right cooling equipment or talk to our team about installation and service.</p></div>' .
        '<div class="canriv-footer-cta-actions"><a href="' . esc_url(canriv_page_url('contact')) . '">Talk to our team ' . $icon('arrow_outward') . '</a>' .
        '<a href="' . esc_url(canriv_page_url('catalog')) . '">Explore equipment ' . $icon('arrow_forward') . '</a></div></div>' .
        '<div class="canriv-footer-inner"><div class="canriv-footer-services">' .
        '<div>' . $icon('ac_unit') . '<span>Air conditioning<strong>Residential & commercial</strong></span></div>' .
        '<div>' . $icon('engineering') . '<span>Installation & service<strong>Support for your equipment</strong></span></div>' .
        '<div>' . $icon('inventory_2') . '<span>Equipment reservations<strong>Request a pickup schedule</strong></span></div></div>' .
        '<div class="canriv-footer-grid"><div class="canriv-footer-brand">' .
        '<a class="canriv-footer-logo" href="' . esc_url(canriv_page_url('home')) . '">' . canriv_snowman('canriv-footer-monogram') .
        '<span class="canriv-footer-wordmark">CANRIV<small>CANRIV-J CORPORATION</small></span></a>' .
        '<p>Mechanical contracting, HVAC supply and installation, and elevator systems in the Philippines.</p>' .
        '<a class="canriv-footer-story" href="' . esc_url(canriv_page_url('company-story')) . '">Discover our story ' . $icon('arrow_forward') . '</a></div>';
    foreach ($groups as $heading => $links) {
        $html .= '<nav class="canriv-footer-column" aria-label="' . esc_attr($heading . ' links') . '"><h2>' . esc_html($heading) . '</h2>';
        foreach ($links as $slug => $label) {
            if ($slug === 'completed-projects' && empty(get_option('canriv_pages', [])[$slug])) {
                continue;
            }
            $html .= '<a href="' . esc_url(canriv_page_url($slug)) . '">' . esc_html($label) . '</a>';
        }
        $html .= '</nav>';
    }
    return $html . '<div class="canriv-footer-column canriv-footer-contact"><h2>Let’s talk</h2>' .
        '<a href="mailto:sales@canriv.com">' . $icon('mail') . '<span><small>EMAIL OUR TEAM</small>sales@canriv.com</span></a>' .
        '<a href="tel:+639454481254">' . $icon('call') . '<span><small>CALL FOR INQUIRIES</small>0945 448 1254</span></a>' .
        '<a class="canriv-footer-contact-link" href="' . esc_url(canriv_page_url('contact')) . '">Send an inquiry ' . $icon('north_east') . '</a></div></div>' .
        '<div class="canriv-footer-bottom"><small>&copy; ' . esc_html(wp_date('Y')) . ' Canriv-J Corporation. All rights reserved.</small>' .
        '<a href="https://www.facebook.com/CanrivJCorp">Canriv-J Corporation on Facebook</a></div></div></footer>';
}

add_action('template_redirect', function () {
    if (function_exists('is_shop') && is_shop() && get_queried_object_id() !== (int) (get_option('canriv_pages', [])['catalog'] ?? 0)) {
        wp_safe_redirect(canriv_page_url('catalog'));
        exit;
    }
    if (is_page(['order-management', 'canriv-analytics', 'registered-users'])) {
        nocache_headers();
        if (!is_user_logged_in()) {
            wp_safe_redirect(canriv_page_url('account'));
            exit;
        }
        if (!current_user_can('manage_options')) {
            wp_die('This page is for Canriv administrators.', '', ['response' => 403]);
        }
    }
    if (is_account_page() && !is_wc_endpoint_url() && current_user_can('manage_options')) {
        wp_safe_redirect(canriv_page_url('order-management'));
        exit;
    }
});

add_filter('woocommerce_login_redirect', function ($redirect, $user) {
    return user_can($user, 'manage_options') ? canriv_page_url('order-management') : $redirect;
}, 10, 2);

add_action('woocommerce_before_customer_login_form', function () {
    echo '<div class="canriv-signin"><aside class="canriv-signin-showcase">' .
        '<p class="canriv-signin-label">Canriv customer portal</p><h2>Integrated Climate<br><span>&amp; Vertical Transit</span></h2>' .
        '<p>Shop air conditioners, view your orders, and manage your delivery details in one place.</p>' .
        '<div class="canriv-signin-features"><div><strong>Orders</strong><p>View your purchases and their status.</p></div>' .
        '<div><strong>Addresses</strong><p>Manage saved billing and delivery details.</p></div></div>' .
        '<div class="canriv-signin-support"><strong>Need help?</strong><p><a href="tel:+639454481254">0945 448 1254</a> · <a href="mailto:sales@canriv.com">sales@canriv.com</a></p></div>' .
        '</aside><section class="canriv-signin-card" aria-labelledby="canriv-signin-title">' .
        '<p class="canriv-signin-label">Customer access</p><h1 id="canriv-signin-title">Sign in to your Canriv account</h1>' .
        '<p>Use your email address or username to access your account.</p>';
});

add_action('woocommerce_after_customer_login_form', function () {
    echo '<p class="canriv-signin-register">New to Canriv? <a href="' . esc_url(canriv_page_url('registration')) .
        '">Create a customer account</a>.</p></section></div>';
});

add_action('wp_enqueue_scripts', function () {
    $slug = array_search(get_queried_object_id(), get_option('canriv_pages', []));
    if (!$slug) {
        wp_enqueue_style('canriv-fonts', plugins_url('assets/fonts.css', __FILE__), [], filemtime(__DIR__ . '/assets/fonts.css'));
        wp_enqueue_style('canriv-store', plugins_url('assets/store.css', __FILE__), [], filemtime(__DIR__ . '/assets/store.css'));
        return;
    }
    wp_enqueue_style('canriv-fonts', plugins_url('assets/fonts.css', __FILE__), [], filemtime(__DIR__ . '/assets/fonts.css'));
    wp_enqueue_style('canriv-' . $slug, plugins_url('assets/' . $slug . '.css', __FILE__), [], filemtime(__DIR__ . '/assets/' . $slug . '.css'));
    wp_enqueue_style('canriv-store', plugins_url('assets/store.css', __FILE__), ['canriv-' . $slug], filemtime(__DIR__ . '/assets/store.css'));
    wp_enqueue_script('canriv-store', plugins_url('assets/store.js', __FILE__), [], filemtime(__DIR__ . '/assets/store.js'), true);
    $script = __DIR__ . '/assets/' . $slug . '.js';
    if (is_file($script)) {
        wp_enqueue_script('canriv-' . $slug, plugins_url('assets/' . $slug . '.js', __FILE__), [], filemtime($script), true);
    }
});

add_filter('body_class', function ($classes) {
    if (!in_array(get_queried_object_id(), get_option('canriv_pages', []))) {
        $classes[] = 'canriv-native-footer';
    }
    return $classes;
});

add_action('wp_footer', function () {
    if (!in_array(get_queried_object_id(), get_option('canriv_pages', []))) {
        echo canriv_footer();
    }
});

add_action('wp_body_open', function () {
    if (!in_array(get_queried_object_id(), get_option('canriv_pages', []))) {
        echo canriv_header();
    }
});

add_action('init', function () {
    register_post_type('canriv_request', [
        'labels' => ['name' => 'Canriv Requests', 'singular_name' => 'Canriv Request'],
        'public' => false,
        'show_ui' => true,
        'map_meta_cap' => false,
        'capabilities' => array_fill_keys(['edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'publish_posts', 'read_private_posts', 'delete_posts', 'create_posts'], 'manage_options'),
        'supports' => ['title', 'editor'],
    ]);
});

function canriv_products() {
    return array_filter(array_map('wc_get_product', get_option('canriv_products', [])));
}

function canriv_product($sku) {
    $ids = get_option('canriv_products', []);
    return isset($ids[$sku]) ? wc_get_product($ids[$sku]) : false;
}

require_once __DIR__ . '/customer-account.php';

function canriv_cart_has_checkout() {
    return is_page(wc_get_page_id('cart')) && WC()->cart && !WC()->cart->is_empty();
}

add_filter('woocommerce_is_checkout', function ($checkout) {
    return $checkout || canriv_cart_has_checkout();
});

function canriv_purchase_layout($checkout) {
    $combined = !$checkout && canriv_cart_has_checkout();
    $checkout = $checkout || $combined;
    $content = do_shortcode($checkout ? '[woocommerce_checkout]' : '[woocommerce_cart]');
    if ($checkout && is_wc_endpoint_url()) {
        return $content;
    }
    if ($checkout && !is_user_logged_in() && WC()->checkout()->is_registration_required() &&
        !WC()->checkout()->is_registration_enabled()) {
        $content = '<section class="canriv-purchase-summary"><h2>Sign in to check out</h2>' .
            '<p>Your items will be here when you return.</p><a class="canriv-button" href="' .
            esc_url(canriv_page_url('account')) . '">Sign in</a> <a href="' .
            esc_url(canriv_page_url('registration')) . '">Create an account</a></section>';
    }
    if ($combined) {
        $content .= '<details id="canriv-edit-cart" class="canriv-purchase-edit"><summary>Edit quantities, remove items, or apply a coupon</summary>' .
            do_shortcode('[woocommerce_cart]') . '</details>';
    }
    $steps = ['Cart', 'Delivery details', 'Payment method', 'Order confirmation'];
    $active = $checkout ? 1 : 0;
    $html = '<div class="canriv-purchase ' . ($checkout ? 'canriv-purchase-checkout' : 'canriv-purchase-cart') . '">';
    $html .= '<header class="canriv-purchase-heading"><div><h1>' .
        ($checkout ? 'Secure order checkout' : 'Shopping cart') . '</h1></div></header>';
    $html .= '<ol class="canriv-purchase-steps" aria-label="Order progress">';
    foreach ($steps as $index => $label) {
        $state = $index === $active ? ' aria-current="step"' : '';
        $html .= '<li' . $state . '><span aria-hidden="true">' . ($index < $active ? '✓' : $index + 1) .
            '</span><div><small>Step ' . ($index + 1) . '</small><strong>' . esc_html($label) . '</strong></div></li>';
    }
    return $html . '</ol><div class="canriv-purchase-content">' . $content . '</div></div>';
}

add_action('woocommerce_checkout_before_customer_details', function () {
    if (!is_checkout()) {
        return;
    }
    echo '<div class="canriv-purchase-details"><section class="canriv-purchase-fulfillment">' .
        '<h2><span class="material-symbols-outlined" aria-hidden="true">local_shipping</span> Fulfillment &amp; site dispatch</h2>' .
        '<div class="canriv-fulfillment-choice"><span class="material-symbols-outlined" aria-hidden="true">warehouse</span>' .
        '<div><strong>Delivery or pickup</strong><p>Enter your site address to see available fulfillment options in the order summary.</p></div></div>' .
        '</section>';
});

add_action('woocommerce_checkout_after_customer_details', function () {
    if (!is_checkout()) {
        return;
    }
    echo '<section class="canriv-purchase-payment" aria-labelledby="canriv-payment-heading"><h2 id="canriv-payment-heading">' .
        '<span class="material-symbols-outlined" aria-hidden="true">payments</span> Payment method selection</h2>';
    woocommerce_checkout_payment();
    echo '</section></div>';
});

add_action('wp', function () {
    if (is_checkout()) {
        remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
    }
});

add_action('woocommerce_checkout_before_order_review_heading', function () {
    if (is_checkout()) {
        echo '<section class="canriv-purchase-summary" aria-labelledby="order_review_heading">';
    }
});

add_action('woocommerce_checkout_after_order_review', function () {
    if (is_checkout()) {
        if (canriv_cart_has_checkout()) {
            echo '<a class="canriv-summary-edit" href="#canriv-edit-cart">Edit cart or apply a coupon</a>';
        }
        echo '<button type="button" class="canriv-summary-submit" hidden>Place order</button></section>';
    }
});

add_filter('woocommerce_checkout_cart_item_quantity', function ($quantity, $item) {
    return $item['data']->get_image('woocommerce_thumbnail', ['class' => 'canriv-summary-product-image', 'alt' => '']) . $quantity;
}, 10, 2);

add_filter('woocommerce_checkout_fields', function ($fields) {
    $priorities = ['first_name' => 10, 'last_name' => 20, 'phone' => 30, 'email' => 40, 'company' => 45, 'country' => 50, 'state' => 60, 'city' => 70, 'postcode' => 80, 'address_1' => 90, 'address_2' => 100];
    foreach ($priorities as $key => $priority) {
        if (isset($fields['billing']['billing_' . $key])) {
            $fields['billing']['billing_' . $key]['priority'] = $priority;
        }
    }
    return $fields;
});

function canriv_form_message($registration = false) {
    $messages = [
        'received' => ['Request received. Canriv must confirm stock and scheduling before pickup.', 'success'],
        'sent' => ['Your inquiry was saved for Canriv staff.', 'success'],
        'registered' => ['Account created. Sign in to continue.', 'success'],
        'reviewed' => ['Your review was submitted for moderation.', 'success'],
        'applied' => ['Your application was saved for the Canriv team to review.', 'success'],
        'invalid' => ['Please check the required fields and submit again.', 'error'],
        'registration_phone' => ['Enter a Philippine mobile number such as 09171234567 or +639171234567.', 'error'],
        'registration_password' => ['Use a unique password of 8–128 characters and make sure both fields match.', 'error'],
        'registration_address' => ['Enter your complete delivery address and a four-digit postal code.', 'error'],
        'registration_signed_in' => ['You are already signed in. Sign out before creating another account.', 'error'],
        'duplicate' => ['An account already exists for this email address. Please sign in or reset your password.', 'error'],
        'failed' => ['We could not save your submission. Please try again.', 'error'],
    ];
    $status = sanitize_key($_GET['canriv_status'] ?? '');
    if (isset($messages[$status])) {
        [$message, $kind] = $messages[$status];
        $target = $registration ? ' id="registration-result" tabindex="-1"' : '';
        $next = $registration && $status === 'registered'
            ? ' <a href="' . esc_url(canriv_page_url('account')) . '">Sign in</a>'
            : '';
        return '<p' . $target . ' class="canriv-notice ' . esc_attr($kind) . '" role="status">' . esc_html($message) . $next . '</p>';
    }
    return '';
}

function canriv_registration_provinces() {
    $options = '<option value="">Select a province or Metro Manila</option>';
    foreach (WC()->countries->get_states('PH') as $code => $label) {
        $options .= '<option value="' . esc_attr($code) . '">' . esc_html($label) . '</option>';
    }
    return $options;
}

function canriv_fields($kind) {
    return canriv_form_message($kind === 'register') . '<input type="hidden" name="action" value="canriv_submit"><input type="hidden" name="kind" value="' .
        esc_attr($kind) . '">' . ($kind === 'review' ? '<input type="hidden" id="review-photo-limit" value="' . min(15 * MB_IN_BYTES, wp_max_upload_size()) . '">' : '') .
        wp_nonce_field('canriv_' . $kind, '_canriv_nonce', false, false);
}

function canriv_review_photos($comment_id) {
    $upload = $_FILES['photos'] ?? null;
    if (!$upload || !is_array($upload['error'] ?? null)) return [];
    $indexes = array_keys(array_filter($upload['error'], fn($error) => $error !== UPLOAD_ERR_NO_FILE));
    if (count($indexes) > 3) return new WP_Error('photos', 'Choose up to three photos.');
    $total = 0;
    foreach ($indexes as $i) {
        $total += (int) ($upload['size'][$i] ?? 0);
        if ($upload['error'][$i] !== UPLOAD_ERR_OK || !is_string($upload['tmp_name'][$i] ?? null) || !is_uploaded_file($upload['tmp_name'][$i]) ||
            !in_array(wp_get_image_mime($upload['tmp_name'][$i]), ['image/jpeg', 'image/png'], true) || $total > min(15 * MB_IN_BYTES, wp_max_upload_size())) {
            return new WP_Error('photos', 'Choose JPG or PNG photos, up to 15 MB in total.');
        }
    }
    if (!$indexes) return [];
    $directory = wp_upload_dir(null, false)['basedir'] . '/canriv-private-reviews';
    if (!wp_mkdir_p($directory) || !file_put_contents($directory . '/.htaccess', "Require all denied\n") ||
        !file_put_contents($directory . '/web.config', '<?xml version="1.0"?><configuration><system.webServer><security><authorization><remove users="*" roles="" verbs=""/><add accessType="Deny" users="*"/></authorization></security></system.webServer></configuration>')) {
        return new WP_Error('photos', 'Photo storage is unavailable.');
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    $private_upload = static fn($dirs) => array_merge($dirs, ['path' => $directory, 'subdir' => '/canriv-private-reviews']);
    $files = [];
    add_filter('upload_dir', $private_upload);
    try {
        foreach ($indexes as $i) {
            $mime = wp_get_image_mime($upload['tmp_name'][$i]);
            $file = ['name' => 'review-' . $comment_id . '-' . wp_generate_uuid4() . ($mime === 'image/png' ? '.png' : '.jpg'),
                'tmp_name' => $upload['tmp_name'][$i], 'type' => $mime, 'size' => $upload['size'][$i], 'error' => UPLOAD_ERR_OK];
            $saved = wp_handle_upload($file, ['test_form' => false, 'mimes' => ['jpg' => 'image/jpeg', 'png' => 'image/png']]);
            if (isset($saved['error'])) {
                foreach ($files as $name) wp_delete_file($directory . '/' . $name);
                return new WP_Error('photos', 'The photos could not be saved.');
            }
            $files[] = basename($saved['file']);
        }
    } finally {
        remove_filter('upload_dir', $private_upload);
    }
    return $files;
}

function canriv_serve_review_photo() {
    $comment = get_comment(absint($_GET['review'] ?? 0));
    if (!$comment || ($comment->comment_approved !== '1' && !current_user_can('manage_options'))) {
        status_header(404);
        exit;
    }
    $files = get_comment_meta($comment->comment_ID, 'canriv_photos', true);
    $name = $files[absint($_GET['photo'] ?? 0)] ?? '';
    $path = wp_upload_dir(null, false)['basedir'] . '/canriv-private-reviews/' . basename($name);
    if (!$name || !is_file($path)) {
        status_header(404);
        exit;
    }
    header('Content-Type: ' . wp_get_image_mime($path));
    header('X-Content-Type-Options: nosniff');
    readfile($path);
    exit;
}
add_action('admin_post_canriv_review_photo', 'canriv_serve_review_photo');
add_action('admin_post_nopriv_canriv_review_photo', 'canriv_serve_review_photo');
add_action('delete_comment', function ($id) {
    foreach ((array) get_comment_meta($id, 'canriv_photos', true) as $name) {
        if ($name) wp_delete_file(wp_upload_dir(null, false)['basedir'] . '/canriv-private-reviews/' . basename($name));
    }
});

function canriv_application_upload($id) {
    $file = $_FILES['resume'] ?? null;
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
    if (($file['error'] ?? null) !== UPLOAD_ERR_OK || !is_string($file['tmp_name'] ?? null) ||
        !is_uploaded_file($file['tmp_name']) || (int) $file['size'] > min(15 * MB_IN_BYTES, wp_max_upload_size())) {
        return new WP_Error('resume', 'Invalid application attachment.');
    }
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['pdf', 'docx', 'jpg', 'jpeg'], true)) return new WP_Error('resume', 'Unsupported attachment.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (($extension === 'pdf' && $mime !== 'application/pdf') ||
        (in_array($extension, ['jpg', 'jpeg'], true) && wp_get_image_mime($file['tmp_name']) !== 'image/jpeg')) {
        return new WP_Error('resume', 'The file content does not match its extension.');
    }
    if ($extension === 'docx') {
        $zip = new ZipArchive();
        if ($zip->open($file['tmp_name']) !== true) return new WP_Error('resume', 'Invalid Word document.');
        $valid = $zip->locateName('[Content_Types].xml') !== false && $zip->locateName('word/document.xml') !== false;
        $zip->close();
        if (!$valid) return new WP_Error('resume', 'Invalid Word document.');
    }
    $directory = wp_upload_dir(null, false)['basedir'] . '/canriv-private-applications';
    if (!wp_mkdir_p($directory) || !file_put_contents($directory . '/.htaccess', "Require all denied\n") ||
        !file_put_contents($directory . '/web.config', '<?xml version="1.0"?><configuration><system.webServer><security><authorization><remove users="*" roles="" verbs=""/><add accessType="Deny" users="*"/></authorization></security></system.webServer></configuration>')) {
        return new WP_Error('resume', 'Private storage unavailable.');
    }
    $file['name'] = 'application-' . $id . '-' . wp_generate_uuid4() . '.' . $extension;
    require_once ABSPATH . 'wp-admin/includes/file.php';
    $private_upload = static fn($dirs) => array_merge($dirs, ['path' => $directory, 'subdir' => '/canriv-private-applications']);
    add_filter('upload_dir', $private_upload);
    try {
        $saved = wp_handle_upload($file, ['test_form' => false, 'mimes' => ['pdf' => 'application/pdf', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'jpg|jpeg' => 'image/jpeg']]);
    } finally {
        remove_filter('upload_dir', $private_upload);
    }
    return isset($saved['error']) ? new WP_Error('resume', 'Attachment could not be saved.') : basename($saved['file']);
}

add_action('admin_post_canriv_application_file', function () {
    if (!current_user_can('manage_options')) wp_die('Administrator access required.', '', ['response' => 403]);
    $id = absint($_GET['request'] ?? 0);
    $name = get_post_meta($id, 'canriv_resume', true);
    $path = wp_upload_dir(null, false)['basedir'] . '/canriv-private-applications/' . basename($name);
    if (get_post_type($id) !== 'canriv_request' || !$name || !is_file($path)) wp_die('Attachment not found.', '', ['response' => 404]);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($name) . '"');
    header('X-Content-Type-Options: nosniff');
    readfile($path);
    exit;
});
add_action('before_delete_post', function ($id) {
    $name = get_post_meta($id, 'canriv_resume', true);
    if ($name) wp_delete_file(wp_upload_dir(null, false)['basedir'] . '/canriv-private-applications/' . basename($name));
});

function canriv_submit() {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        wp_die('Use the form to submit a request.', '', ['response' => 405]);
    }
    $text = function ($key, $max = 200) {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? mb_substr(sanitize_text_field(wp_unslash($value)), 0, $max) : '';
    };
    $kind = $text('kind');
    $routes = ['reserve' => 'reservation', 'contact' => 'contact', 'rfq' => 'products-services', 'home' => 'home', 'register' => 'registration', 'review' => 'reviews', 'career' => 'careers'];
    if (!isset($routes[$kind])) {
        wp_die('Unknown form.', '', ['response' => 400]);
    }
    $finish = function ($status) use ($routes, $kind) {
        $url = add_query_arg('canriv_status', $status, canriv_page_url($routes[$kind]));
        wp_safe_redirect($url . ($kind === 'register' ? '#registration-result' : ''), 303);
        exit;
    };
    if (!wp_verify_nonce($text('_canriv_nonce'), 'canriv_' . $kind)) {
        $finish('invalid');
    }
    $name = $text('name');
    if ($kind === 'register' && $text('first_name') && $text('last_name')) {
        $name = implode(' ', array_filter([$text('first_name'), $text('middle_initial'), $text('last_name')]));
    }
    $email = sanitize_email($text('email'));
    $phone = $text('phone', 32);
    if (!$name || !is_email($email)) {
        $finish('invalid');
    }
    if ($kind === 'register') {
        $phone = preg_replace('/[\s()-]+/', '', $phone);
        $password = (string) wp_unslash(is_string($_POST['password'] ?? null) ? $_POST['password'] : '');
        $confirm = (string) wp_unslash(is_string($_POST['confirm'] ?? null) ? $_POST['confirm'] : '');
        if (is_user_logged_in()) {
            $finish('registration_signed_in');
        }
        if ($password !== $confirm || !canriv_password_is_allowed($password, $email)) {
            $finish('registration_password');
        }
        if ($phone !== '' && !preg_match('/^(?:\+?63|0)?9\d{9}$/', $phone)) {
            $finish('registration_phone');
        }
        if (!$text('first_name') || !$text('last_name') || !$text('terms')) {
            $finish('invalid');
        }
        $province = $text('province');
        $address = [
            'first_name' => $text('first_name'),
            'last_name' => $text('last_name'),
            'country' => 'PH',
            'state' => $province,
            'city' => $text('city'),
            'address_1' => $text('street'),
            'address_2' => $text('barangay'),
            'postcode' => $text('postcode'),
        ];
        if (!array_key_exists($province, WC()->countries->get_states('PH')) || !$address['city'] ||
            !$address['address_1'] || !$address['address_2'] || !preg_match('/^\d{4}$/', $address['postcode'])) {
            $finish('registration_address');
        }
        if (email_exists($email)) {
            $finish('duplicate');
        }
        $account = wc_create_new_customer($email, '', $password, [
            'display_name' => $name,
            'first_name' => $text('first_name'),
            'last_name' => $text('last_name'),
        ]);
        if (is_wp_error($account)) {
            $finish(email_exists($email) ? 'duplicate' : 'failed');
        }
        $customer = new WC_Customer($account);
        foreach ($address as $field => $value) {
            $customer->set_props(['billing_' . $field => $value, 'shipping_' . $field => $value]);
        }
        $customer->set_billing_email($email);
        if ($phone !== '') {
            $customer->set_billing_phone('+63' . substr($phone, -10));
        }
        $customer->save();
        $finish('registered');
    }
    if ($kind === 'review') {
        $product = canriv_product($text('sku'));
        $rating = (int) $text('rating');
        $message = $text('message', 2000);
        if (!$product || $rating < 1 || $rating > 5 || !$message || !get_option('woocommerce_enable_reviews')) {
            $finish('invalid');
        }
        $user = wp_get_current_user();
        $moderate = static fn($approval) => is_wp_error($approval) ? $approval : 0;
        add_filter('pre_comment_approved', $moderate);
        try {
            $review = wp_new_comment([
                'comment_post_ID' => $product->get_id(),
                'comment_author' => $user->exists() ? $user->display_name : $name,
                'comment_author_email' => $user->exists() ? $user->user_email : $email,
                'comment_author_url' => '',
                'comment_content' => $message,
                'comment_type' => 'review',
                'user_id' => $user->ID,
                'comment_meta' => ['rating' => $rating],
            ], true);
        } finally {
            remove_filter('pre_comment_approved', $moderate);
        }
        if (is_wp_error($review) || !$review) {
            $finish('failed');
        }
        $photos = canriv_review_photos($review);
        if (is_wp_error($photos)) {
            wp_delete_comment($review, true);
            $finish('invalid');
        }
        update_comment_meta($review, 'canriv_photos', $photos);
        foreach (['title', 'location', 'profile', 'order_ref'] as $key) {
            update_comment_meta($review, 'canriv_' . $key, $text($key));
        }
        foreach (['cooling', 'energy', 'quiet'] as $aspect) {
            $value = (int) $text($aspect);
            if ($value >= 1 && $value <= 5) update_comment_meta($review, 'canriv_' . $aspect, $value);
        }
        $finish('reviewed');
    }

    $message = $text('message', 2000);
    $record = ['Name' => $name, 'Email' => $email, 'Phone' => $phone];
    if ($kind === 'career') {
        $role = canriv_career_roles()[$text('position')] ?? null;
        if (!$phone || !$text('consent') ||
            !$role ||
            !in_array($text('experience'), ['fresh', '1-2', '3-5', '5+'], true) ||
            !in_array($text('hub'), ['nueva-ecija', 'pampanga', 'baguio', 'pangasinan', 'ncr'], true)) $finish('invalid');
        $record += ['Position' => $role[0], 'Experience' => $text('experience'), 'Preferred work area' => $text('hub')];
    } elseif ($kind === 'reserve') {
        $product = canriv_product($text('sku'));
        $date = $text('date');
        if (!$product || !$product->is_purchasable() || !$phone || !$text('terms') ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) $date) ||
            $date < wp_date('Y-m-d') || !in_array($text('time'), ['Morning', 'Afternoon'], true)) {
            $finish('invalid');
        }
        $record += ['Product' => $product->get_name(), 'Pickup date' => $date, 'Time preference' => $text('time')];
    } else {
        if (!$message) {
            $finish('invalid');
        }
        $record['Subject'] = $text('subject');
        $record['Department'] = $text('department');
    }
    $record['Message'] = $message;
    $rows = [];
    foreach (array_filter($record) as $label => $value) {
        $rows[] = $label . ': ' . $value;
    }
    $id = wp_insert_post([
        'post_type' => 'canriv_request',
        'post_status' => 'private',
        'post_title' => $kind === 'career' ? 'Career application' : ($kind === 'reserve' ? 'Pickup request' : 'Canriv inquiry'),
        'post_content' => implode("\n", $rows),
        'post_author' => get_current_user_id(),
        'meta_input' => ['canriv_request_type' => $kind],
    ], true);
    if ($kind === 'career' && !is_wp_error($id) && $id) {
        $resume = canriv_application_upload($id);
        if (is_wp_error($resume)) {
            wp_delete_post($id, true);
            $finish('invalid');
        }
        if ($resume) {
            update_post_meta($id, 'canriv_resume', $resume);
            wp_update_post(['ID' => $id, 'post_content' => implode("\n", $rows) . "\nResume: " . add_query_arg(['action' => 'canriv_application_file', 'request' => $id], admin_url('admin-post.php'))]);
        }
        $finish('applied');
    }
    $finish(is_wp_error($id) || !$id ? 'failed' : ($kind === 'reserve' ? 'received' : 'sent'));
}
add_action('admin_post_canriv_submit', 'canriv_submit');
add_action('admin_post_nopriv_canriv_submit', 'canriv_submit');

function canriv_option($label, $name, $value = '') {
    return '<label>' . esc_html($label) . '<input name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" required></label>';
}

function canriv_product_select($purchasable_only = false) {
    $requested = sanitize_text_field($_GET['sku'] ?? '');
    $html = '<label>Product<select name="sku" required><option value="">Select a product</option>';
    foreach (canriv_products() as $sku => $product) {
        if (!$purchasable_only || $product->is_purchasable()) {
            $html .= '<option value="' . esc_attr($sku) . '"' . selected($requested, $sku, false) . '>' . esc_html($product->get_name()) . '</option>';
        }
    }
    return $html . '</select></label>';
}

function canriv_form($kind) {
    $user = wp_get_current_user();
    $form = '<form class="canriv-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">' . canriv_fields($kind) .
        canriv_option('Full name', 'name', $user->display_name) .
        '<label>Email<input type="email" name="email" required value="' . esc_attr($user->user_email) . '"></label>';
    if ($kind === 'review') {
        $form .= canriv_product_select() . '<label>Rating<select name="rating" required><option value="">Select a rating</option>' .
            '<option value="5">5 stars</option><option value="4">4 stars</option><option value="3">3 stars</option><option value="2">2 stars</option><option value="1">1 star</option></select></label>';
    } else {
        $form .= '<label>Phone<input type="tel" name="phone" autocomplete="tel"' . ($kind === 'reserve' ? ' required' : '') . '></label>';
    }
    if ($kind === 'reserve') {
        $form .= canriv_product_select(true) . '<label>Preferred pickup date<input type="date" name="date" min="' . esc_attr(wp_date('Y-m-d')) . '" required></label>' .
            '<label>Preferred time<select name="time" required><option>Morning</option><option>Afternoon</option></select></label>';
    } elseif ($kind !== 'review') {
        $form .= canriv_option('Subject', 'subject');
    }
    $form .= '<label>' . ($kind === 'review' ? 'Your review' : 'Message') . '<textarea name="message" maxlength="2000" rows="4"' . ($kind === 'reserve' ? '' : ' required') . '></textarea></label>';
    if ($kind === 'reserve') {
        $form .= '<label class="canriv-check"><input type="checkbox" name="terms" value="1" required> I understand pickup requires confirmation from Canriv.</label>';
    }
    $labels = ['reserve' => 'Request reservation', 'review' => 'Submit review', 'contact' => 'Send inquiry', 'rfq' => 'Request a quotation', 'home' => 'Request a quotation'];
    return $form . '<button type="submit">' . esc_html($labels[$kind]) . '</button></form>';
}

function canriv_reviews() {
    $products = array_values(get_option('canriv_products', []));
    $reviews = $products ? get_comments(['post__in' => $products, 'status' => 'approve', 'type' => 'review', 'number' => 20]) : [];
    $html = '<h2>Product reviews</h2>';
    foreach ($reviews as $review) {
        $html .= '<article><h3>' . esc_html(get_the_title($review->comment_post_ID)) . '</h3><p>' .
            esc_html(get_comment_meta($review->comment_ID, 'rating', true) . '/5 · ' . $review->comment_author) . '</p><h4>' .
            esc_html(get_comment_meta($review->comment_ID, 'canriv_title', true)) . '</h4><p>' .
            esc_html($review->comment_content) . '</p></article>';
        foreach ((array) get_comment_meta($review->comment_ID, 'canriv_photos', true) as $index => $photo) {
            if ($photo) $html .= '<img class="canriv-review-photo" loading="lazy" alt="Customer installation photo" src="' . esc_url(add_query_arg(['action' => 'canriv_review_photo', 'review' => $review->comment_ID, 'photo' => $index], admin_url('admin-post.php'))) . '">';
        }
    }
    if (!$reviews) {
        $html .= '<p>No approved product reviews yet.</p>';
    }
    return $html;
}

function canriv_staff_link($page, $label) {
    return '<a class="canriv-button" href="' . esc_url(admin_url($page)) . '">' . esc_html($label) . '</a>';
}

function canriv_table($headers, $rows) {
    $html = '<div class="canriv-table"><table><thead><tr>';
    foreach ($headers as $header) {
        $html .= '<th scope="col">' . esc_html($header) . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    foreach ($rows as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td>' . esc_html($cell) . '</td>';
        }
        $html .= '</tr>';
    }
    return $html . '</tbody></table></div>' . ($rows ? '' : '<p>No records yet.</p>');
}

function canriv_ops_header() {
    if (!current_user_can('manage_options')) return '';
    return '<header class="canriv-ops-top"><div class="canriv-ops-title"><strong>CANRIV</strong><span>ENG ADMIN</span><small>Heavy Equipment &amp; Chiller Portal</small></div>' .
        '<div class="canriv-ops-session"><span class="material-symbols-outlined" aria-hidden="true">schedule</span>' .
        '<time>' . esc_html(wp_date('H:i', null, new DateTimeZone('Asia/Manila'))) . ' PHT</time></div>' .
        '<div class="canriv-ops-identity"><span class="material-symbols-outlined" aria-hidden="true">person</span><div>' .
        esc_html(wp_get_current_user()->display_name) . '<small>Administrator</small></div><a href="' .
        esc_url(wp_logout_url(canriv_page_url('account'))) . '">Sign out</a></div></header>';
}

require_once __DIR__ . '/registered-users.php';
require_once __DIR__ . '/order-management.php';
require_once __DIR__ . '/audience-analytics.php';
require_once __DIR__ . '/product-details.php';

add_filter('woocommerce_gateway_title', function ($title, $gateway) {
    if ($gateway !== 'cod' || (!is_checkout() && !wp_doing_ajax())) {
        return $title;
    }
    $methods = WC()->session ? WC()->session->get('chosen_shipping_methods', []) : [];
    if (!$methods) {
        return $title;
    }
    foreach ($methods as $method) {
        if (strtok($method, ':') !== 'local_pickup') {
            return 'Cash on Delivery';
        }
    }
    return 'Pay at pickup';
}, 10, 2);

function canriv_ops_dashboard($type) {
    if (!current_user_can('manage_options')) {
        return '';
    }
    if ($type === 'users') {
        return canriv_registered_directory();
    }
    if ($type === 'orders') {
        return canriv_order_directory();
    }
    return canriv_audience_dashboard();
}

// Elementor must render these widgets after its cache so carts, nonces, and records stay current.
add_filter('elementor/element/is_dynamic_content', function ($dynamic, $data) {
    return $dynamic || (($data['widgetType'] ?? '') === 'html' && str_contains($data['settings']['html'] ?? '', '%%CANRIV:'));
}, 10, 2);

add_filter('elementor/widget/render_content', function ($content, $widget) {
    if ($widget->get_name() !== 'html' || !str_contains($content, '%%CANRIV:')) {
        return $content;
    }
    return preg_replace_callback('/%%CANRIV:([a-z-]+)%%/', function ($match) {
        $type = $match[1];
        return match ($type) {
            'header' => is_page(['order-management', 'registered-users', 'canriv-analytics']) ? canriv_ops_header() : canriv_header(),
            'company-brands', 'company-team', 'company-quote', 'company-project-story', 'aircon-shop', 'aircon-shop-photos' => canriv_company_section($type),
            'vertical-transport', 'vertical-transport-summary' => canriv_company_section($type),
            'home-featured' => canriv_company_section($type),
            'vertical-transport-url' => esc_url(canriv_page_url('products-services') . '#mitsubishi-vertical-transport'),
            'aircon-showroom-url' => esc_url(canriv_page_url('catalog') . '#mitsubishi-aircon'),
            'footer' => is_page(['order-management', 'registered-users', 'canriv-analytics']) ? '' : canriv_footer(),
            'cart' => canriv_purchase_layout(false),
            'checkout' => canriv_purchase_layout(true),
            'account' => canriv_customer_account(),
            'registration-fields' => canriv_fields('register'),
            'registration-action' => esc_url(admin_url('admin-post.php')),
            'registration-provinces' => canriv_registration_provinces(),
            'account-url' => esc_url(canriv_page_url('account')),
            'home-url' => esc_url(canriv_page_url('home')),
            'review-fields' => canriv_fields('review'),
            'contact-fields' => canriv_fields('contact'),
            'career-fields' => canriv_fields('career'),
            'career-roles', 'career-options' => canriv_career_role_markup($type),
            'photo-limit' => esc_html(size_format(min(15 * MB_IN_BYTES, wp_max_upload_size()))),
            'upload-bytes' => (string) min(15 * MB_IN_BYTES, wp_max_upload_size()),
            'cart-count' => WC()->cart ? (string) WC()->cart->get_cart_contents_count() : '0',
            'reviews' => canriv_reviews(),
            'contact', 'reserve', 'rfq', 'home', 'review' => canriv_form($type),
            'orders', 'users', 'analytics' => canriv_ops_dashboard($type),
            default => '',
        };
    }, $content);
}, 10, 2);

add_filter('woocommerce_return_to_shop_redirect', fn() => canriv_page_url('catalog'));
