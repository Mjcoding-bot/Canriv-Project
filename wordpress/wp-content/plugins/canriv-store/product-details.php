<?php
defined('ABSPATH') || exit;

function canriv_shop_products() {
    static $products;
    if (isset($products)) return $products;
    $products = [];
    foreach (['KV27WM-ARF21C2', 'KY60FM-ARF21C', 'KY60CM-ARF21C', 'KV60CC-ARF21C', 'FP-53CXV030308-1', 'HITACHI-RAS-24HT-RAC-24HTP', 'KV18WM-ARF31', 'WCARH008EEVC2', 'FP-53CLV030308', '53CEP018308-1', 'FP-53CAV055308-3', 'WCARK008EE', 'FP-53CUV055308-3', 'CS-CU-QU24AKQ', 'CWN620JPH', 'CW-XU1825EPH', 'CW-U921JPH', 'CS-CU-PU9AKQ', 'CWN1220VPH', 'CWMN1220VPH', 'RPS-140AN-RAM140FPS', 'RA-08HV', 'RPS-NP3A-RAC-NP3AB', 'RA-08HVQ', 'RA-20SR', 'MSCH-25CRFN8', 'MFS-30CRFN8', 'MSAS-13CRFN8', 'MSAI-25CRFN8', 'MWMDP-09CN8MC2', 'FP-51ARA005HMNV-N5', 'FP-53AFC060KENV-Y5', 'MFSI-O-52CDN8-MD1', 'FP51ARA020HEIVN42', 'SRK18YYPW7', 'SRK18YYSW7', 'SRK17ZRW', 'SRK10YNS7', 'SRK10YLS'] as $sku) {
        $product = wc_get_product(wc_get_product_id_by_sku($sku));
        if ($product && $product->is_visible()) $products[$sku] = $product;
    }
    return $products;
}

function canriv_shop_category_label($sku) {
    return match ($sku) {
        'MFS-30CRFN8', 'MFSI-O-52CDN8-MD1', 'CR-AS-30FM-INV', 'CR-PP-50TWR-INV', 'KY60FM-ARF21C' => 'FLOOR-STANDING INVERTER',
        'FP-53AFC060KENV-Y5' => 'CEILING CONCEALED',
        'CR-CC-30INV-4W', 'KV60CC-ARF21C' => 'CEILING CASSETTE',
        'KY60CM-ARF21C' => 'CEILING-SUSPENDED INVERTER',
        'CR-IB-2200-CENT' => 'INDUSTRIAL BLOWER',
        'CR-QF-08MIX-DUCT' => 'IN-LINE DUCT FAN',
        'CR-VRF-100HP-ODU' => 'COMMERCIAL VRF',
        'CR-FW-MS-35D' => 'MULTI-SPLIT INVERTER',
        'MWMDP-09CN8MC2', 'FP-51ARA005HMNV-N5', 'FP51ARA020HEIVN42' => 'WINDOW TYPE',
        default => 'WALL-MOUNTED INVERTER',
    };
}

function canriv_brand_aircon_cards() {
    ob_start();
    foreach (canriv_shop_products() as $sku => $unit) {
        $brand = preg_match('/^(Koppel|Carrier|Hitachi|Panasonic|Midea|Mitsubishi|Canriv)\b/i', $unit->get_name(), $brand_match) ? ucfirst(strtolower($brand_match[1])) : 'Other';
        $price_value = $unit->get_price();
        $link = esc_url($unit->get_permalink());
        $can_buy = $unit->is_purchasable() && $unit->is_in_stock() && $unit->is_type('simple');
        $button_url = $can_buy ? $unit->add_to_cart_url() : $link;
        $button_label = $can_buy ? 'Add to Cart' : 'View Product';
        ?>
        <?php $hp = match ($sku) { 'MSCH-25CRFN8', 'MSAI-25CRFN8', 'CR-CC-30INV-4W', 'CR-AS-30FM-INV', 'SRK17ZRW' => '3', 'MSAS-13CRFN8' => '1.5', 'MWMDP-09CN8MC2', 'SRK10YNS7', 'SRK10YLS' => '1', 'MFSI-O-52CDN8-MD1', 'FP-53AFC060KENV-Y5', 'CR-PP-50TWR-INV', 'KY60FM-ARF21C', 'KY60CM-ARF21C', 'KV60CC-ARF21C' => '5', 'FP51ARA020HEIVN42', 'SRK18YYPW7', 'SRK18YYSW7' => '2', 'FP-51ARA005HMNV-N5' => '0.6', 'CR-VRF-100HP-ODU' => '10', 'CR-FW-MS-35D' => '3.5', 'KV27WM-ARF21C2' => '3', default => '' }; ?>
        <?php $category_label = canriv_shop_category_label($sku); ?>
        <article class="bg-surface-container-lowest rounded-xl shadow-sm flex flex-col overflow-hidden group" data-canriv-product="<?= esc_attr($sku) ?>" data-brand="<?= esc_attr(strtolower($brand)) ?>" <?= $price_value !== '' ? 'data-price="' . esc_attr($price_value) . '"' : '' ?> data-category="<?= esc_attr($category_label) ?>" data-tech="inverter" data-capacity="<?= esc_attr($hp) ?>">
            <div class="p-space-md flex flex-col gap-2 flex-grow">
                <span class="font-label-code text-label-sm text-primary"><?= esc_html($category_label) ?></span>
                <a class="relative w-full h-48 bg-white rounded-lg overflow-hidden flex items-center justify-center" href="<?= $link ?>" aria-label="<?= esc_attr($unit->get_name()) ?>"><?= wp_kses_post($unit->get_image('woocommerce_single', ['class' => 'w-full h-full object-contain', 'loading' => 'lazy'])) ?></a>
                <h3 class="font-headline-sm text-headline-sm"><a href="<?= $link ?>"><?= esc_html($unit->get_name()) ?></a></h3>
                <p class="text-body-sm text-on-surface-variant"><?= esc_html($unit->get_short_description()) ?></p>
                <?php if ($unit->get_price_html()) : ?>
                    <div class="canriv-brand-aircon-price"><?= wp_kses_post($unit->get_price_html()) ?></div>
                <?php endif; ?>
                <a class="canriv-brand-card-add" href="<?= esc_url($button_url) ?>" aria-label="<?= esc_attr($button_label . ': ' . $unit->get_name()) ?>">
                    <span class="material-symbols-outlined canriv-card-bag-icon" aria-hidden="true">shopping_bag</span>
                    <?= esc_html($button_label) ?>
                </a>
            </div>
        </article>
        <?php
    }
    return ob_get_clean();
}

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
