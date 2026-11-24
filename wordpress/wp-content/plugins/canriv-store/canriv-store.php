<?php
/**
 * Plugin Name: Canriv Store
 * Description: Canriv's Elementor storefront forms and staff pages.
 * Requires Plugins: elementor, woocommerce
 */

defined('ABSPATH') || exit;

require_once __DIR__ . '/assets/equipment-catalog.php';

function canriv_page_url($slug) {
    $pages = get_option('canriv_pages', []);
    if (!empty($pages[$slug])) {
        return get_permalink($pages[$slug]);
    }
    return home_url('/?pagename=' . rawurlencode($slug));
}

function canriv_equipment_page_id() {
    $page = get_page_by_path('equipment', OBJECT, 'page');
    return $page ? (int) $page->ID : 0;
}

function canriv_equipment_url($slug) {
    $page = get_page_by_path('equipment/' . sanitize_title($slug), OBJECT, 'page');
    return $page ? get_permalink($page) : home_url(user_trailingslashit('equipment/' . sanitize_title($slug)));
}

function canriv_equipment_find($slug) {
    foreach (canriv_equipment_catalog() as $group => $items) {
        foreach ($items as $name => $item) {
            if ($item['slug'] === $slug) return $item + ['name' => $name, 'group' => $group];
        }
    }
    return false;
}

function canriv_equipment_render_detail($slug) {
    $item = canriv_equipment_find($slug);
    if (!$item) return '';
    $related = [];
    foreach (canriv_equipment_catalog() as $group => $items) {
        foreach ($items as $name => $candidate) {
            if ($candidate['slug'] !== $slug && $group === $item['group'] && count($related) < 3) {
                $related[] = $candidate + ['name' => $name, 'group' => $group];
            }
        }
    }
    if (count($related) < 3) {
        foreach (canriv_equipment_catalog() as $group => $items) {
            foreach ($items as $name => $candidate) {
                if ($candidate['slug'] !== $slug && !array_filter($related, static fn($related_item) => $related_item['slug'] === $candidate['slug'])) {
                    $related[] = $candidate + ['name' => $name, 'group' => $group];
                    if (count($related) === 3) break 2;
                }
            }
        }
    }
    ob_start();
    require __DIR__ . '/assets/equipment-detail-content.php';
    return ob_get_clean();
}

function canriv_sync_equipment_pages() {
    if (get_option('canriv_equipment_pages_version') === '1') return;
    $parent = get_page_by_path('equipment', OBJECT, 'page');
    if (!$parent) {
        $parent_id = wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Equipment Details', 'post_name' => 'equipment', 'post_content' => '']);
        if (is_wp_error($parent_id) || !$parent_id) return;
        $parent = get_post($parent_id);
    }
    foreach (canriv_equipment_catalog() as $items) {
        foreach ($items as $name => $item) {
            if (get_page_by_path('equipment/' . $item['slug'], OBJECT, 'page')) continue;
            $created = wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Mitsubishi Electric ' . $name, 'post_name' => $item['slug'], 'post_parent' => $parent->ID, 'post_content' => '']);
            if (is_wp_error($created) || !$created) return;
        }
    }
    update_option('canriv_equipment_pages_version', '1');
}
add_action('init', 'canriv_sync_equipment_pages', 30);

function canriv_is_equipment_detail_page() {
    $page_id = get_queried_object_id();
    return is_page() && $page_id && str_starts_with((string) get_page_uri($page_id), 'equipment/');
}

add_action('wp_enqueue_scripts', function () {
    if (canriv_is_equipment_detail_page()) {
        wp_enqueue_style('canriv-equipment-detail', plugins_url('assets/equipment-detail.css', __FILE__), ['canriv-store'], filemtime(__DIR__ . '/assets/equipment-detail.css'));
    }
}, 30);

add_filter('template_include', function ($template) {
    return canriv_is_equipment_detail_page() ? __DIR__ . '/assets/equipment-detail-page.php' : $template;
}, 99);

add_action('template_redirect', function () {
    if (is_page('equipment')) {
        wp_safe_redirect(canriv_page_url('products-services') . '#mitsubishi-vertical-transport', 302);
        exit;
    }
});

function canriv_completed_projects() {
    $html = file_get_contents(__DIR__ . '/assets/completed-projects.html');
    if ($html === false) {
        return '';
    }
    $routes = [
        'home' => 'home', 'company-story' => 'company-story', 'product-&-services' => 'products-services',
        'completed-projects' => 'completed-projects', 'reviews-&-comments' => 'reviews', 'careers' => 'careers',
        'contact-us' => 'contact', 'cart-&-checkout' => 'cart', 'login' => 'account',
    ];
    foreach ($routes as $path => $page) {
        $html = str_replace('__CANRIV_URL_' . $path . '__', esc_url(canriv_page_url($page)), $html);
    }
    return canriv_header() . str_replace('src="assets/', 'src="' . esc_url(plugins_url('assets/', __FILE__)), $html) . canriv_footer();
}

function canriv_header() {
    $links = ['home' => 'Home', 'company-story' => 'Company Story', 'products-services' => 'Products & Services', 'catalog' => 'Shop', 'reviews' => 'Reviews', 'careers' => 'Careers', 'contact' => 'Contact'];
    if (!empty(get_option('canriv_pages', [])['completed-projects'])) {
        $links = array_slice($links, 0, 3, true) + ['completed-projects' => 'Completed Projects'] + $links;
    }
    $navigation = '';
    foreach ($links as $slug => $label) {
        $current = ($slug === 'home' && is_front_page()) || is_page((int) (get_option('canriv_pages', [])[$slug] ?? 0)) || ($slug === 'products-services' && canriv_is_equipment_detail_page());
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

function canriv_homepage_video() {
    $video_url = 'https://www.facebook.com/reel/666531848509138';
    $embed_url = 'https://www.facebook.com/plugins/video.php?href=' . rawurlencode($video_url) . '&show_text=false&width=960';
    return '<section class="canriv-home-video" aria-labelledby="canriv-home-video-title"><div class="canriv-home-video-inner">' .
        '<div class="canriv-home-video-copy"><span class="canriv-home-video-eyebrow"><span class="material-symbols-outlined" aria-hidden="true">play_circle</span> FROM CANRIV</span>' .
        '<h2 id="canriv-home-video-title">See our work in action</h2><p>Watch a closer look at Canriv’s air conditioning and project work.</p>' .
        '<a href="' . esc_url($video_url) . '" target="_blank" rel="noopener noreferrer">Watch on Facebook <span aria-hidden="true">↗</span></a></div>' .
        '<div class="canriv-home-video-frame"><iframe src="' . esc_url($embed_url) . '" title="Canriv project video on Facebook" loading="lazy" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowfullscreen></iframe></div>' .
        '</div></section>';
}

function canriv_career_roles() {
    return [
        'hvac-technician' => [
            'title' => 'Lead RAC Aircon Installation Technician',
            'group' => 'technical',
            'description' => 'Installs and commissions split, cassette, and commercial air conditioning systems; checks piping, drainage, wiring, and performance before handover.',
            'department' => 'HVAC FIELD OPERATIONS',
            'location' => 'Pampanga HQ / Central Luzon Field',
            'highlights' => ['TESDA NC II or NC III in RAC Servicing / PACU required', '4+ years copper brazing, pressure testing & vacuuming', 'Valid Driver\'s License Code B/B1 an advantage'],
        ],
        'elevator-specialist' => [
            'title' => 'Elevator Installation & Rigging Specialist',
            'group' => 'technical',
            'description' => 'Supports elevator equipment installation, rigging, alignment, and site coordination under the project engineer.',
            'department' => 'VERTICAL TRANSPORT',
            'location' => 'Baguio Hub & Central Luzon',
            'highlights' => ['3+ years hoistway guide rail erection & car cab rigging', 'DOLE BOSH / Working at Heights certification', 'Proficient in traction machine alignment & brake tuning'],
        ],
        'project-engineer' => [
            'title' => 'HVAC Commercial VRF Project Engineer',
            'group' => 'technical',
            'description' => 'Reviews project requirements, coordinates technical submittals, and monitors installation quality and commissioning.',
            'department' => 'ENGINEERING MANAGEMENT',
            'location' => 'San Fernando, Pampanga (HQ)',
            'highlights' => ['Licensed Mechanical Engineer (PRC Board passer)', 'Proficient in VRF/VRF sizing, duct sizing & heat calculations', 'AutoCAD / Revit MEP shop drawing layout skills'],
        ],
        'project-coordinator' => [
            'title' => 'Elevator Preventive Maintenance Mechanic',
            'group' => 'operations',
            'description' => 'Coordinates schedules, materials, site access, and customer updates for installation and service teams.',
            'department' => 'VERTICAL TRANSPORT',
            'location' => 'North Luzon Corridors / Baguio',
            'highlights' => ['Experience in monthly PMS of passenger & cargo lifts', 'Door operator mechanism tuning & governor safety tests', 'Emergency call-out response preparedness'],
        ],
        'hvac-maintenance' => [
            'title' => 'AC Preventive Maintenance & Coil Cleaning Specialist',
            'group' => 'technical',
            'description' => 'Cleans and inspects air conditioning systems, records service findings, and recommends repairs or parts when needed.',
            'department' => 'HVAC FIELD OPERATIONS',
            'location' => 'Pampanga & Tarlac',
            'highlights' => ['High-pressure washer coil cleaning & chemical sanitation', 'Inverter PCB diagnostic check & refrigerant top-up', 'TESDA RAC NC I or NC II certificate preferred'],
        ],
        'sales-executive' => [
            'title' => 'Technical Sales Executive (HVAC & Lifts)',
            'group' => 'office',
            'description' => 'Helps customers select HVAC equipment, prepares quotations, and follows up on project and service inquiries.',
            'department' => 'TECHNICAL SALES & B2B',
            'location' => 'Clark / Angeles / Subic / NCR',
            'highlights' => ['Handling commercial tenders, general contractor RFQs', 'Knowledge in Carrier, Daikin, Koppel VRF product matrices', 'Strong client negotiation and relationship retention'],
        ],
    ];
}

function canriv_career_role_markup($format) {
    $html = '';
    foreach (canriv_career_roles() as $slug => $role) {
        if ($format === 'career-options') {
            $html .= '<option value="' . esc_attr($slug) . '">' . esc_html($role['title']) . '</option>';
            continue;
        }
        $html .= '<li class="canriv-career-role" data-job-role="' . esc_attr($slug) . '" data-job-category="' . esc_attr($role['group']) . '">' .
            '<div class="canriv-career-card-top"><span class="canriv-career-department">' . esc_html($role['department']) . '</span><span class="canriv-career-type">Full-Time</span></div>' .
            '<div class="canriv-career-copy"><h3>' . esc_html($role['title']) . '</h3>' .
            '<p class="canriv-career-location"><span class="material-symbols-outlined" aria-hidden="true">location_on</span>' . esc_html($role['location']) . '</p>' .
            '<ul class="canriv-career-highlights">';
        foreach ($role['highlights'] as $highlight) $html .= '<li><span class="material-symbols-outlined" aria-hidden="true">check_circle</span>' . esc_html($highlight) . '</li>';
        $html .= '</ul></div>' .
            '<div class="canriv-career-contact"><a href="' . esc_url(canriv_page_url('contact') . '#canriv-free-quote') . '" aria-label="' . esc_attr('Apply for ' . $role['title']) . '">Apply Now</a></div></li>';
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
        wp_enqueue_script('canriv-store', plugins_url('assets/store.js', __FILE__), [], filemtime(__DIR__ . '/assets/store.js'), true);
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
    $products = [];
    foreach (wc_get_products(['status' => 'publish', 'limit' => -1, 'orderby' => 'menu_order', 'order' => 'ASC']) as $product) {
        if ($sku = $product->get_sku()) {
            $products[$sku] = $product;
        }
    }
    return $products;
}

function canriv_product($sku) {
    $id = wc_get_product_id_by_sku($sku);
    $product = $id ? wc_get_product($id) : false;
    return $product && $product->get_status() === 'publish' ? $product : false;
}

function canriv_installed_equipment_options() {
    $options = '';
    foreach (canriv_shop_products() as $sku => $product) {
        $options .= '<option value="' . esc_attr($sku) . '">' . esc_html($product->get_name()) . '</option>';
    }
    return $options;
}

require_once __DIR__ . '/customer-account.php';

function canriv_purchase_layout($checkout) {
    $content = do_shortcode($checkout ? '[woocommerce_checkout]' : '[woocommerce_cart]');
    if ($checkout && is_wc_endpoint_url()) {
        if (!is_wc_endpoint_url('order-received')) {
            return $content;
        }
        return '<div class="canriv-purchase canriv-order-confirmation">' .
            '<nav class="canriv-purchase-breadcrumb" aria-label="Breadcrumb"><div><a href="' . esc_url(canriv_page_url('home')) . '">Home</a><span aria-hidden="true">›</span><a href="' . esc_url(canriv_page_url('catalog')) . '">Shop</a><span aria-hidden="true">›</span><strong>Order received</strong></div><p><span class="material-symbols-outlined" aria-hidden="true">verified_user</span>Secure order confirmation</p></nav>' .
            '<main class="canriv-order-confirmation-content"><section class="canriv-order-confirmation-banner"><span class="material-symbols-outlined" aria-hidden="true">task_alt</span><div><p>CANRIV PROCUREMENT &middot; ORDER RECEIVED</p><h1>Thank you for your order</h1><span>We’ll confirm your delivery or pickup arrangements and keep you updated.</span></div></section><div class="canriv-order-confirmation-body">' . $content . '</div></main></div>';
    }
    if ($checkout && !is_user_logged_in() && WC()->checkout()->is_registration_required() &&
        !WC()->checkout()->is_registration_enabled()) {
        $content = '<section class="canriv-purchase-summary"><h2>Sign in to check out</h2>' .
            '<p>Your items will be here when you return.</p><a class="canriv-button" href="' .
            esc_url(canriv_page_url('account')) . '">Sign in</a> <a href="' .
            esc_url(canriv_page_url('registration')) . '">Create an account</a></section>';
    }
    $html = '<div class="canriv-purchase ' . ($checkout ? 'canriv-purchase-checkout' : 'canriv-purchase-cart') . '">';
    $html .= '<nav class="canriv-purchase-breadcrumb" aria-label="Breadcrumb"><div><a href="' . esc_url(canriv_page_url('home')) . '">Home</a><span aria-hidden="true">›</span>';
    if ($checkout) {
        $html .= '<a href="' . esc_url(wc_get_cart_url()) . '">Shopping Cart</a><span aria-hidden="true">›</span><strong>Checkout</strong>';
    } else {
        $html .= '<a href="' . esc_url(canriv_page_url('catalog')) . '">Shop Catalog</a><span aria-hidden="true">›</span><strong>Shopping Cart (' . esc_html(WC()->cart ? WC()->cart->get_cart_contents_count() : 0) . ' Items)</strong>';
    }
    $html .= '</div><p><span class="material-symbols-outlined" aria-hidden="true">' . ($checkout ? 'lock' : 'verified_user') . '</span>' . ($checkout ? 'Order checkout' : 'CANRIV Procurement Portal') . '</p></nav>';
    if ($checkout) {
        $steps = ['HVAC & Lift Cart Confirmed', 'Site Engineering & Specs', 'Payment Gateway / PH QR', 'BIR Official Receipt Dispatch'];
        $html .= '<ol class="canriv-purchase-steps" aria-label="Order progress">';
        foreach ($steps as $index => $label) {
            $state = $index === 1 ? ' aria-current="step"' : '';
            $html .= '<li' . $state . '><span aria-hidden="true">' . ($index === 0 ? '✓' : sprintf('%02d', $index + 1)) .
                '</span><div><small>Step ' . sprintf('%02d', $index + 1) . ($index === 1 ? ' / In Progress' : '') . '</small><strong>' . esc_html($label) . '</strong></div></li>';
        }
        $html .= '</ol>';
    }
    return $html . '<div class="canriv-purchase-content">' . $content . '</div></div>';
}

// Keep the catalog/cart flow quiet after an item is added. The cart count and
// order summary already show that the item was added.
add_filter('wc_add_to_cart_message_html', '__return_empty_string', 99);

add_action('woocommerce_before_cart', function () {
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    echo '<section class="canriv-cart-lead"><div><p>Procurement Queue <span aria-hidden="true">•</span> Direct Hub Dispatch</p><h1>Shopping Cart</h1><span>Review your selected air-conditioning systems and equipment before checkout.</span></div></section>';
});

add_action('woocommerce_before_cart_table', function () {
    $clear_url = wp_nonce_url(add_query_arg('canriv_empty_cart', '1', wc_get_cart_url()), 'canriv-empty-cart');
    echo '<input type="hidden" name="update_cart" value="Update cart"><div class="canriv-cart-tools"><strong><span class="material-symbols-outlined" aria-hidden="true">shopping_bag</span> Cart Line Items (' . esc_html(WC()->cart->get_cart_contents_count()) . ')</strong><a href="' . esc_url($clear_url) . '"><span class="material-symbols-outlined" aria-hidden="true">delete</span> Clear Cart</a></div>';
});

add_filter('woocommerce_coupons_enabled', function ($enabled) {
    return is_cart() ? false : $enabled;
});

add_action('template_redirect', function () {
    if (is_cart() && isset($_GET['canriv_empty_cart']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'] ?? '')), 'canriv-empty-cart')) {
        WC()->cart->empty_cart();
        wp_safe_redirect(wc_get_cart_url());
        exit;
    }
});

add_filter('gettext', function ($translated, $text, $domain) {
    if ($domain === 'woocommerce' && is_cart() && $text === 'Cart totals') {
        return 'Order Summary';
    }
    if ($domain === 'woocommerce' && is_checkout() && $text === 'Your order') {
        return 'Order Summary';
    }
    return $translated;
}, 10, 3);

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
        echo '<a class="canriv-summary-edit" href="' . esc_url(wc_get_cart_url()) . '">Edit Items</a>';
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
        'reviewed' => ['Your review has been published.', 'success'],
        'review_duplicate' => ['A matching review has already been submitted.', 'error'],
        'applied' => ['Your application was saved for the Canriv team to review.', 'success'],
        'invalid' => ['Please check the required fields and submit again.', 'error'],
        'invalid_phone' => ['Enter a valid Philippine mobile number, such as 0917 123 4567.', 'error'],
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

function canriv_customer_address_details($user_id) {
    if (!$user_id || !class_exists('WC_Customer')) return [];
    $customer = new WC_Customer($user_id);
    $country = $customer->get_shipping_country() ?: ($customer->get_billing_country() ?: 'PH');
    $state = $customer->get_shipping_state() ?: $customer->get_billing_state();
    $states = WC()->countries->get_states($country);
    return array_filter([
        'Street address' => $customer->get_shipping_address_1() ?: $customer->get_billing_address_1(),
        'Unit / floor / building' => $customer->get_meta('canriv_address_unit') ?: '',
        'Barangay / district' => $customer->get_meta('canriv_address_barangay') ?: '',
        'City / municipality' => $customer->get_shipping_city() ?: $customer->get_billing_city(),
        'Province / region' => $states[$state] ?? $state,
        'Postal / ZIP code' => $customer->get_shipping_postcode() ?: $customer->get_billing_postcode(),
    ], static fn($value) => (string) $value !== '');
}

function canriv_normalize_ph_mobile($phone) {
    $phone = preg_replace('/[\s().-]+/u', '', trim((string) $phone));
    if ($phone === '') return '';
    if (!preg_match('/^(?:\+?63|0)?(9\d{9})$/', $phone, $matches)) return false;
    return '+63' . $matches[1];
}

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
    $phone = canriv_normalize_ph_mobile($text('phone', 32));
    if (!$name || !is_email($email)) {
        $finish('invalid');
    }
    if ($kind === 'register') {
        if ($phone === false) $finish('registration_phone');
        $password = (string) wp_unslash(is_string($_POST['password'] ?? null) ? $_POST['password'] : '');
        $confirm = (string) wp_unslash(is_string($_POST['confirm'] ?? null) ? $_POST['confirm'] : '');
        if (is_user_logged_in()) {
            $finish('registration_signed_in');
        }
        if ($password !== $confirm || !canriv_password_is_allowed($password, $email)) {
            $finish('registration_password');
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
        // WordPress discussion settings can override comment_approved and hold
        // customer reviews for moderation. Scope this override to this review.
        $auto_approve = static fn($approval) => is_wp_error($approval) ? $approval : 1;
        add_filter('pre_comment_approved', $auto_approve, PHP_INT_MAX);
        try {
            $review = wp_new_comment([
                'comment_post_ID' => $product->get_id(),
                'comment_author' => $user->exists() ? $user->display_name : $name,
                'comment_author_email' => $user->exists() ? $user->user_email : $email,
                'comment_author_url' => '',
                'comment_content' => $message,
                'comment_type' => 'review',
                'comment_approved' => 1,
                'user_id' => $user->ID,
                'comment_meta' => ['rating' => $rating],
            ], true);
        } finally {
            remove_filter('pre_comment_approved', $auto_approve, PHP_INT_MAX);
        }
        if (is_wp_error($review)) {
            $finish($review->get_error_code() === 'comment_duplicate' ? 'review_duplicate' : 'failed');
        }
        if (!$review) {
            $finish('failed');
        }
        $saved_review = get_comment($review);
        if (!$saved_review || $saved_review->comment_approved !== '1') {
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

    if ($phone === false) $finish('invalid_phone');
    $technical_rfq = $kind === 'contact' && $text('canriv_form_variant') === 'technical_rfq';
    $department = $text('department');
    $subject = $text('subject');
    if (($technical_rfq || $kind === 'rfq') && !$phone) $finish('invalid_phone');
    if ($technical_rfq && (!$subject || !in_array($department, [
        'aircon-supply-install', 'aircon-purchase', 'mitsubishi-elevators', 'mitsubishi-escalators',
        'mitsubishi-controls', 'mitsubishi-modernization', 'aircon-maintenance',
    ], true))) {
        $finish('invalid');
    }

    $message = $text('message', 2000);
    $record = ['Name' => $name, 'Email' => $email, 'Phone' => $phone];
    if ($kind === 'career') {
        $role = canriv_career_roles()[$text('position')] ?? null;
        if (!$phone || !$text('consent') ||
            !$role ||
            !in_array($text('experience'), ['fresh', '1-2', '3-5', '5+'], true) ||
            !in_array($text('hub'), ['nueva-ecija', 'pampanga', 'baguio', 'pangasinan', 'ncr'], true)) $finish('invalid');
        $record += ['Position' => $role['title'], 'Experience' => $text('experience'), 'Preferred work area' => $text('hub')];
    } elseif ($kind === 'reserve') {
        $product = canriv_product($text('sku'));
        $date = $text('date');
        if (!$product || !$product->is_purchasable() || !$phone || !$text('terms') ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) $date) ||
            $date < wp_date('Y-m-d') || !in_array($text('time'), ['Morning', 'Afternoon'], true)) {
            $finish('invalid');
        }
        $record += ['Product' => $product->get_name(), 'Pickup date' => $date, 'Time preference' => $text('time')];
    } elseif ($kind === 'rfq') {
        $selected = $text('equipment', 100);
        $equipment = canriv_equipment_find($selected);
        $product = $equipment ? null : canriv_product($selected);
        if ((!$equipment && !$product) || !$message) $finish('invalid');
        $record += [
            'Equipment or service' => $equipment ? 'Mitsubishi Electric ' . $equipment['name'] . ' (' . $equipment['group'] . ')' : $product->get_name(),
            'Organization' => $text('company', 150),
            'Project location' => $text('project_location', 200),
        ];
    } else {
        if (!$message) {
            $finish('invalid');
        }
        $record['Subject'] = $subject;
        $record['Department'] = $department;
    }
    if ($kind !== 'career' && is_user_logged_in()) {
        $record += canriv_customer_address_details(get_current_user_id());
    }
    $record['Message'] = $message;
    $rows = [];
    foreach (array_filter($record) as $label => $value) {
        $rows[] = $label . ': ' . $value;
    }
    $id = wp_insert_post([
        'post_type' => 'canriv_request',
        'post_status' => 'private',
        'post_title' => $kind === 'career' ? 'Career application' : ($kind === 'reserve' ? 'Pickup request' : ($kind === 'rfq' ? 'RFQ: ' . ($equipment ? $equipment['name'] : $product->get_name()) : 'Canriv inquiry')),
        'post_content' => implode("\n", $rows),
        'post_author' => get_current_user_id(),
        'meta_input' => ['canriv_request_type' => $kind, 'canriv_request_status' => 'pending'],
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
        $form .= '<label>Phone<input type="tel" name="phone" autocomplete="tel-national" inputmode="tel" maxlength="16" placeholder="0917 123 4567"' . (in_array($kind, ['reserve', 'rfq'], true) ? ' required' : '') . '></label>';
    }
    if ($kind === 'reserve') {
        $form .= canriv_product_select(true) . '<label>Preferred pickup date<input type="date" name="date" min="' . esc_attr(wp_date('Y-m-d')) . '" required></label>' .
            '<label>Preferred time<select name="time" required><option>Morning</option><option>Afternoon</option></select></label>';
    } elseif ($kind === 'rfq') {
        $requested = sanitize_text_field(wp_unslash(is_string($_GET['equipment'] ?? null) ? $_GET['equipment'] : ''));
        $form .= '<label>Product or service<select name="equipment" required><option value="">Choose a product or service</option>';
        foreach (canriv_equipment_catalog() as $group => $items) {
            $form .= '<optgroup label="' . esc_attr($group) . '">';
            foreach ($items as $item) $form .= '<option value="' . esc_attr($item['slug']) . '"' . selected($requested, $item['slug'], false) . '>Mitsubishi Electric ' . esc_html($item['name']) . '</option>';
            $form .= '</optgroup>';
        }
        $form .= '<optgroup label="Air conditioning products">';
        foreach (canriv_products() as $sku => $product) $form .= '<option value="' . esc_attr($sku) . '"' . selected($requested, $sku, false) . '>' . esc_html($product->get_name()) . '</option>';
        $form .= '</optgroup></select></label>' .
            '<label>Organization or company<input name="company" maxlength="150"></label>' .
            '<label>Project location<input name="project_location" maxlength="200" placeholder="City or site address"></label>';
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
    $products = array_map(static fn($product) => $product->get_id(), canriv_products());
    $reviews = $products ? get_comments(['post__in' => $products, 'status' => 'approve', 'type' => 'review', 'number' => 20, 'orderby' => 'comment_date_gmt', 'order' => 'DESC']) : [];
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

add_action('plugins_loaded', function () {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class Canriv_Gateway_GCash extends WC_Payment_Gateway {
        public function __construct() {
            $this->id = 'canriv_gcash';
            $this->method_title = 'GCash (demo)';
            $this->method_description = 'Manual GCash payment placeholder. Replace the demo QR before accepting real orders.';
            $this->has_fields = false;
            $this->supports = ['products'];
            $this->init_form_fields();
            $this->init_settings();
            $this->enabled = $this->get_option('enabled', 'yes');
            $this->title = $this->get_option('title', 'GCash');
            $this->description = '<p>Demo QR only; it does not accept payment.</p><img src="' . esc_url(plugins_url('assets/gcash-qr-demo.svg', __FILE__)) . '" width="180" height="180" alt="Non-functional demo GCash QR code">';
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }

        public function init_form_fields() {
            $this->form_fields = [
                'enabled' => ['title' => 'Enable/Disable', 'type' => 'checkbox', 'label' => 'Enable demo GCash option', 'default' => 'yes'],
                'title' => ['title' => 'Title', 'type' => 'text', 'default' => 'GCash'],
            ];
        }

        public function process_payment($order_id) {
            $order = wc_get_order($order_id);
            if (!$order) {
                return ['result' => 'failure'];
            }
            if ($order->get_total() > 0) {
                $order->update_status('on-hold', 'Demo GCash selected; payment must be verified manually.');
            } else {
                $order->payment_complete();
            }
            if (WC()->cart && $order->has_cart_hash(WC()->cart->get_cart_hash())) {
                WC()->cart->empty_cart();
            }
            return ['result' => 'success', 'redirect' => $this->get_return_url($order)];
        }
    }

    add_filter('woocommerce_payment_gateways', function ($gateways) {
        $gateways[] = 'Canriv_Gateway_GCash';
        return $gateways;
    });
}, 20);

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
    if ($widget->get_name() !== 'html') {
        return $content;
    }
    if (str_contains($content, '%%CANRIV:')) {
        $content = preg_replace_callback('/%%CANRIV:([a-z-]+)%%/', function ($match) {
        $type = $match[1];
        return match ($type) {
            'header' => is_page(['order-management', 'registered-users', 'canriv-analytics']) ? canriv_ops_header() : canriv_header(),
            'company-brands', 'company-team', 'company-quote', 'company-project-story', 'aircon-shop', 'aircon-shop-photos' => canriv_company_section($type),
            'vertical-transport', 'vertical-transport-summary' => canriv_company_section($type),
            'home-featured' => canriv_company_section($type),
            'homepage-video' => canriv_homepage_video(),
            'brand-aircon-cards' => canriv_brand_aircon_cards(),
            'completed-projects' => canriv_completed_projects(),
            'installed-equipment-options' => canriv_installed_equipment_options(),
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
            'contact-fields' => canriv_fields('contact') . '<input type="hidden" name="canriv_form_variant" value="technical_rfq">',
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
    }
    return preg_replace_callback('/<input\b(?=[^>]*\bid=["\']contactNumber["\'])[^>]*>/i', static function ($match) {
        $input = preg_replace('/\s+(?:type|maxlength|minlength|pattern|inputmode|title|autocomplete)=(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $match[0]);
        $input = preg_replace('/\s*\/?\s*>$/', '', $input);
        return $input . ' type="text" maxlength="10" minlength="10" inputmode="numeric" pattern="9[0-9]{9}" autocomplete="tel-national" title="Enter 10 digits starting with 9, for example 9171234567.">';
    }, $content);
}, 10, 2);

add_filter('woocommerce_return_to_shop_redirect', fn() => canriv_page_url('catalog'));
