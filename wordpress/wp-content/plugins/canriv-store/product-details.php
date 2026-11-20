<?php
defined('ABSPATH') || exit;

add_filter('wc_get_template_part', function ($template, $slug, $name) {
    if ($slug === 'content' && $name === 'single-product' && is_product()) {
        return __DIR__ . '/assets/product-details-template.php';
    }
    return $template;
}, 10, 3);

add_action('wp', function () {
    if (is_product()) {
        remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
    }
});

add_action('wp_enqueue_scripts', function () {
    if (is_product()) {
        wp_enqueue_style('canriv-product-detail', plugins_url('assets/product-detail.css', __FILE__), ['canriv-store'], filemtime(__DIR__ . '/assets/product-detail.css'));
    }
}, 20);
