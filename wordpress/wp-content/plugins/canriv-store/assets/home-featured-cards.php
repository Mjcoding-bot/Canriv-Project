<?php
defined('ABSPATH') || exit;

$cards = [
    ['CR-AF-15INV-R32', 'Wall mounted', '1.5 HP inverter', 'canriv-msz-gc35na.webp', 'Mitsubishi Electric wall-mounted unit at the Canriv showroom', 'Wall-mounted split inverter for room cooling.'],
    ['CR-CC-30INV-4W', 'Ceiling cassette', '3.0 HP inverter', 'canriv-ply-sp36ea.webp', 'Mitsubishi Electric ceiling cassette at the Canriv showroom', 'Four-way ceiling cassette for larger indoor spaces.'],
    ['CR-AS-30FM-INV', 'Floor standing', '3.0 HP inverter', 'canriv-psy-sp36ka.webp', 'Mitsubishi Electric floor-standing air conditioner at the Canriv showroom', 'Floor-standing inverter for rooms with limited wall or ceiling space.'],
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
                <img class="w-full h-full object-cover" src="<?= esc_url($assets_url . $photo) ?>" alt="<?= esc_attr($alt) ?>" loading="lazy" decoding="async">
                <span class="absolute top-2 left-2 px-2 py-0.5 rounded bg-surface-container-lowest/90 font-label-code text-label-sm text-primary font-bold"><?= esc_html($badge) ?></span>
            </a>
            <h3 class="font-headline-sm text-headline-sm text-on-surface line-clamp-1"><a href="<?= esc_url($link) ?>" title="<?= esc_attr($title) ?>"><?= esc_html($title) ?></a></h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant line-clamp-2"><?= esc_html($description) ?></p>
            <div class="pt-2 font-label-code text-label-sm text-on-surface-variant flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px] text-primary" aria-hidden="true"><?= $product ? 'photo_camera' : 'elevator' ?></span>
                <span><?= $product ? 'Mitsubishi showroom reference' : 'Mitsubishi Electric equipment' ?></span>
            </div>
        </div>
        <div class="pt-space-md space-y-space-xs">
            <div class="font-label-price text-label-price <?= $product ? 'text-primary' : 'text-tertiary' ?>"><?= $product ? wp_kses_post($product->get_price_html()) : 'Project Quote' ?></div>
            <a class="w-full py-2 px-3 rounded font-title-md text-body-sm font-semibold transition-colors flex items-center justify-center gap-1 <?= $product ? 'bg-primary hover:bg-primary-container text-on-primary' : 'bg-tertiary hover:bg-tertiary-container text-on-tertiary' ?>" href="<?= esc_url($button_url) ?>" aria-label="<?= esc_attr($button_label . ': ' . $title) ?>">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true"><?= $can_buy ? 'shopping_cart' : ($product ? 'visibility' : 'request_quote') ?></span><?= esc_html($button_label) ?>
            </a>
        </div>
    </article>
<?php
endforeach;
echo str_replace('%%cards%%', ob_get_clean(), file_get_contents(__DIR__ . '/home-featured.html'));
