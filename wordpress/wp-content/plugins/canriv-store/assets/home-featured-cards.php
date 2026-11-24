<?php
defined('ABSPATH') || exit;

$cards = [
    ['KV27WM-ARF21C2', 'Wall mounted', '3.0 HP inverter', '', '', 'Koppel wall-mounted inverter air conditioner.'],
    ['HITACHI-RAS-24HT-RAC-24HTP', 'Wall mounted', '2.5 HP inverter', '', '', 'Hitachi wall-mounted inverter air conditioner.'],
    ['CS-CU-PU9AKQ', 'Wall mounted', '1.0 HP inverter', '', '', 'Panasonic wall-mounted inverter air conditioner.'],
    ['', 'Passenger elevator', 'Passenger transport', 'mitsubishi-passenger.webp', 'Mitsubishi Electric passenger elevator', 'Explore passenger elevator configurations for your building.'],
    ['', 'Freight elevator', 'Materials & cargo', 'mitsubishi-freight.webp', 'Mitsubishi Electric freight elevator', 'Discuss the cabin capacity, access, and installation requirements for moving cargo.'],
];
$assets_url = plugin_dir_url(__FILE__);
$quote_url = canriv_page_url('contact') . '#canriv-free-quote';
$equipment_url = canriv_page_url('products-services') . '#mitsubishi-vertical-transport';
ob_start();
foreach ($cards as $index => [$sku, $label, $badge, $photo, $alt, $description]) :
    $product = $sku ? wc_get_product(wc_get_product_id_by_sku($sku)) : false;
    if ($sku && (!$product || $product->get_status() !== 'publish' || !$product->is_visible())) {
        continue;
    }
    $title = $product ? $product->get_name() : ($index === 3 ? 'Mitsubishi Electric Passenger Elevators' : 'Mitsubishi Electric Freight Elevators');
    $link = $product ? $product->get_permalink() : $equipment_url;
    $can_buy = $product && $product->is_purchasable() && $product->is_in_stock() && $product->is_type('simple');
    $button_url = $can_buy ? add_query_arg('add-to-cart', $product->get_id(), wc_get_cart_url()) : ($product ? $link : $quote_url);
    $button_label = $can_buy ? 'Add to Cart' : ($product ? 'View Product' : 'Request a Quote');
    ?>
    <article class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between" data-canriv-equipment="<?= $product ? 'aircon' : 'elevator' ?>">
        <div class="space-y-space-xs">
            <div class="flex items-center justify-between gap-space-xs text-label-sm font-label-code">
                <span class="px-2 py-0.5 rounded bg-surface-container-highest text-on-surface-variant font-bold"><?= esc_html($label) ?></span>
                <?php if ($sku) : ?><span class="text-on-surface-variant">SKU: <?= esc_html($sku) ?></span><?php endif; ?>
            </div>
            <a class="h-40 rounded bg-surface-container overflow-hidden flex items-center justify-center relative my-2" href="<?= esc_url($link) ?>">
                <?php if ($product) : ?><?= wp_kses_post($product->get_image('woocommerce_single', ['class' => 'w-full h-full object-contain', 'loading' => 'lazy'])) ?><?php else : ?><img class="w-full h-full object-cover" src="<?= esc_url($assets_url . $photo) ?>" alt="<?= esc_attr($alt) ?>" loading="lazy" decoding="async"><?php endif; ?>
                <span class="absolute top-2 left-2 px-2 py-0.5 rounded bg-surface-container-lowest/90 font-label-code text-label-sm text-primary font-bold"><?= esc_html($badge) ?></span>
            </a>
            <h3 class="font-headline-sm text-headline-sm text-on-surface line-clamp-1"><a href="<?= esc_url($link) ?>" title="<?= esc_attr($title) ?>"><?= esc_html($title) ?></a></h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant line-clamp-2"><?= esc_html($description) ?></p>
            <div class="pt-2 font-label-code text-label-sm text-on-surface-variant flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px] text-primary" aria-hidden="true"><?= $product ? 'storefront' : 'elevator' ?></span>
                <span><?= $product ? 'Available in the shop' : 'Mitsubishi Electric equipment' ?></span>
            </div>
        </div>
        <div class="pt-space-md space-y-space-xs">
            <div class="font-label-price text-label-price <?= $product ? 'text-primary' : 'text-tertiary' ?>"><?= $product ? wp_kses_post($product->get_price_html()) : 'Project Quote' ?></div>
            <a class="<?= $product ? 'canriv-brand-card-add' : 'w-full py-2 px-3 rounded font-title-md text-body-sm font-semibold transition-colors flex items-center justify-center gap-1 bg-tertiary hover:bg-tertiary-container text-on-tertiary' ?>" href="<?= esc_url($button_url) ?>" aria-label="<?= esc_attr($button_label . ': ' . $title) ?>">
                <span class="material-symbols-outlined <?= $product ? 'canriv-card-bag-icon' : 'text-[18px]' ?>" aria-hidden="true"><?= $can_buy ? 'shopping_bag' : ($product ? 'visibility' : 'request_quote') ?></span><?= esc_html($button_label) ?>
            </a>
        </div>
    </article>
<?php
endforeach;
echo str_replace('%%cards%%', ob_get_clean(), file_get_contents(__DIR__ . '/home-featured.html'));
