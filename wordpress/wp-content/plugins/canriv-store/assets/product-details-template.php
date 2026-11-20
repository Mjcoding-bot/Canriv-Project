<?php
defined('ABSPATH') || exit;

global $product;
do_action('woocommerce_before_single_product');
if (post_password_required()) {
    echo get_the_password_form();
    return;
}
if (!$product instanceof WC_Product) {
    return;
}

$capture = static function ($render) {
    ob_start();
    $render();
    return ob_get_clean();
};
$icon = static fn($name) => '<span class="material-symbols-outlined canriv-pdp-icon" aria-hidden="true">' . esc_html($name) . '</span>';
$sku = $product->get_sku();
$shop_url = canriv_page_url('catalog');
$reserve_url = $sku ? add_query_arg('sku', $sku, canriv_page_url('reservation')) : canriv_page_url('reservation');
$categories = wc_get_product_category_list($product->get_id());
$breadcrumbs = '<nav class="canriv-pdp-breadcrumbs" aria-label="Breadcrumb">' . $icon('home') .
    '<a href="' . esc_url(home_url('/')) . '">Home</a><span aria-hidden="true">/</span><a href="' .
    esc_url($shop_url) . '">Shop</a><span aria-hidden="true">/</span><span aria-current="page">' . esc_html($product->get_name()) . '</span></nav>';
$snapshot = '<h2 class="font-headline-sm text-headline-sm uppercase">Product overview</h2><dl class="canriv-pdp-facts">' .
    '<div><dt>Model / SKU</dt><dd>' . esc_html($sku ?: '—') . '</dd></div>' .
    '<div><dt>Category</dt><dd>' . ($categories ? wp_kses_post($categories) : '—') . '</dd></div>' .
    '<div><dt>Availability</dt><dd>' . esc_html($product->is_on_backorder() ? 'Available on backorder' : ($product->is_in_stock() ? 'In stock' : 'Out of stock')) . '</dd></div></dl>';
$rating = '';
if (comments_open($product->get_id()) && get_option('woocommerce_enable_reviews') === 'yes') {
    $count = $product->get_review_count();
    $rating = '<a class="canriv-pdp-rating" href="' . esc_url(add_query_arg('sku', $sku, canriv_page_url('reviews'))) . '">' .
        ($count && wc_review_ratings_enabled() ? wc_get_rating_html($product->get_average_rating(), $count) : '') .
        esc_html(sprintf(_n('%s review', '%s reviews', $count, 'canriv-store'), number_format_i18n($count))) . '</a>';
}
$related = $capture(static function () use ($product) {
    $card = static function ($template, $slug, $name) {
        return $slug === 'content' && $name === 'product' ? __DIR__ . '/product-related-card.php' : $template;
    };
    add_filter('wc_get_template_part', $card, 10, 3);
    try {
        if ($product->get_upsell_ids()) {
            woocommerce_upsell_display(3, 3);
        } else {
            woocommerce_related_products(['posts_per_page' => 3, 'columns' => 3]);
        }
    } finally {
        remove_filter('wc_get_template_part', $card, 10);
    }
});
$stock = $product->get_availability();
$availability = $stock['availability'] ?: ($product->is_in_stock() ? 'In stock' : 'Out of stock');
$actions = '<a class="canriv-pdp-secondary" href="' . esc_url($reserve_url) . '">' . $icon('store') . ' Reserve for in-store pickup</a>' .
    '<a class="canriv-pdp-secondary" href="' . esc_url(canriv_page_url('contact')) . '">' . $icon('support_agent') . ' Ask about installation</a>';
$subtitle = $product->get_short_description() ?: wp_trim_words(wp_strip_all_tags($product->get_description()), 35);
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class('canriv-product-page', $product); ?>>
    <?= strtr(file_get_contents(__DIR__ . '/product-detail.html'), [
        '%%breadcrumbs%%' => $breadcrumbs,
        '%%gallery%%' => $capture('woocommerce_show_product_images'),
        '%%snapshot%%' => $snapshot,
        '%%sku%%' => '<span class="font-label-code text-label-sm text-primary">' . esc_html($sku ? 'SKU: ' . $sku : 'Product details') . '</span>',
        '%%title%%' => esc_html($product->get_name()),
        '%%subtitle%%' => esc_html(wp_strip_all_tags($subtitle)),
        '%%rating%%' => $rating,
        '%%price%%' => '<div class="canriv-pdp-price">' . wp_kses_post($product->get_price_html()) . '</div>',
        '%%stock%%' => $icon('local_shipping') . '<strong>' . esc_html($availability) . '</strong>',
        '%%add-to-cart%%' => $capture('woocommerce_template_single_add_to_cart'),
        '%%actions%%' => $actions,
        '%%related%%' => $related,
    ]) ?>
</div>
<?php
WC()->structured_data->generate_product_data($product);
do_action('woocommerce_after_single_product');
