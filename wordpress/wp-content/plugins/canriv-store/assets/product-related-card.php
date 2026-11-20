<?php
defined('ABSPATH') || exit;
global $product;
if (!$product instanceof WC_Product || !$product->is_visible()) {
    return;
}
$url = $product->get_permalink();
$sku = $product->get_sku();
$description = $product->get_short_description() ?: $product->get_description();
?>
<li <?php wc_product_class('canriv-related-card', $product); ?>>
    <a class="canriv-related-image" href="<?= esc_url($url) ?>" aria-label="<?= esc_attr($product->get_name()) ?>">
        <?= wp_kses_post($product->get_image('woocommerce_thumbnail', ['loading' => 'lazy'])) ?>
    </a>
    <?php if ($sku) : ?><span class="canriv-related-sku">SKU: <?= esc_html($sku) ?></span><?php endif; ?>
    <h3><a href="<?= esc_url($url) ?>"><?= esc_html($product->get_name()) ?></a></h3>
    <?php if ($description) : ?><p class="canriv-related-description"><?= esc_html(wp_trim_words(wp_strip_all_tags($description), 23)) ?></p><?php endif; ?>
    <div class="canriv-related-actions">
        <span class="canriv-related-price"><?= wp_kses_post($product->get_price_html()) ?></span>
        <?php woocommerce_template_loop_add_to_cart(); ?>
    </div>
</li>
