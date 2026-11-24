<?php defined('ABSPATH') || exit; ?>
<?php if ($name === 'filters') : ?>
    <nav class="flex flex-wrap gap-space-xs" aria-label="Analytics date range">
        <?php foreach ([1 => 'Today', 7 => 'Last 7 days', 30 => 'Last 30 days'] as $period => $label) : ?>
            <a class="canriv-directory-control <?= $days === $period ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface' ?>" href="<?= esc_url(add_query_arg('audience_days', $period, $base)) ?>" <?= $days === $period ? 'aria-current="true"' : '' ?>><?= esc_html($label) ?></a>
        <?php endforeach; ?>
    </nav>
    <span class="font-label-code text-label-sm text-secondary">Aggregated site activity · <?= esc_html($start->format('M j')) ?>–<?= esc_html(wp_date('M j, Y')) ?></span>
<?php elseif ($name === 'traffic') : ?>
    <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg">
        <h2 class="font-headline-md text-headline-md"><span class="material-symbols-outlined canriv-analytics-icon" aria-hidden="true">trending_up</span>Audience activity · most visited pages</h2>
        <p class="text-secondary text-body-sm">Counts public pages and products currently listed in the shop.</p>
        <?php if (!$pages) : ?><p class="mt-space-md">No page views recorded for this period yet.</p><?php endif; ?>
        <ol class="canriv-traffic-pages">
            <?php foreach ($pages as $row) : $is_product = get_post_type($row->object_id) === 'product'; ?><li><span class="material-symbols-outlined canriv-traffic-icon" aria-hidden="true"><?= $is_product ? 'ac_unit' : 'article' ?></span><a href="<?= esc_url(get_permalink($row->object_id)) ?>"><?= esc_html(get_the_title($row->object_id) ?: 'Untitled page') ?></a><strong class="font-label-code"><?= esc_html(number_format_i18n($row->views)) ?><small>views</small></strong></li><?php endforeach; ?>
        </ol>
    </section>
<?php elseif ($name === 'daily') : ?>
    <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg">
        <h2 class="font-headline-md text-headline-md">Page views over time</h2>
        <p class="text-secondary text-body-sm">Daily totals for the selected period.</p>
        <?php $max_hits = max(1, ...array_values($daily)); ?>
        <div class="canriv-traffic-chart" role="img" aria-label="<?= esc_attr(implode(', ', array_map(static fn($date, $hits) => $date . ': ' . $hits . ' views', array_keys($daily), $daily))) ?>">
            <?php foreach ($daily as $date => $hits) : ?><span title="<?= esc_attr($date . ': ' . $hits . ' views') ?>" style="--canriv-bar:<?= (int) round(100 * $hits / $max_hits) ?>%"></span><?php endforeach; ?>
        </div>
        <div class="flex justify-between text-body-sm text-secondary"><span><?= esc_html($start->format('M j')) ?></span><span><?= esc_html(wp_date('M j')) ?></span></div>
    </section>
<?php elseif ($name === 'product-heading') : ?>
    <div class="canriv-equipment-heading">
        <div><h2 class="font-headline-md text-headline-md"><span class="material-symbols-outlined canriv-analytics-icon" aria-hidden="true">inventory_2</span>Shop Equipment Performance</h2><p class="text-secondary text-body-sm">Product interest, cart activity, and completed order value for the selected period.</p></div>
        <div class="canriv-equipment-tools"><label class="canriv-equipment-search"><span class="material-symbols-outlined" aria-hidden="true">search</span><input type="search" id="canriv-equipment-search" placeholder="Filter model or SKU…" aria-label="Filter equipment by model or SKU"></label><button type="button" class="canriv-equipment-export" id="canriv-equipment-export"><span class="material-symbols-outlined" aria-hidden="true">download</span>Export table</button></div>
    </div>
<?php elseif ($name === 'products') : ?>
    <?php if (!$products) : ?><tr data-equipment-empty><td colspan="7" class="p-space-md">No shop products are available.</td></tr><?php endif; ?>
    <?php foreach ($products as $row) :
        $product = wc_get_product($row->object_id);
        if (!$product) { continue; }
        $cart_rate = $row->views ? $row->carts / $row->views * 100 : 0;
        ?>
        <tr class="canriv-equipment-row" data-equipment-row data-model="<?= esc_attr(strtolower($product->get_name())) ?>" data-sku="<?= esc_attr(strtolower($product->get_sku())) ?>" data-views="<?= (int) $row->views ?>" data-cart-rate="<?= esc_attr(number_format($cart_rate, 2, '.', '')) ?>" data-units="<?= (int) $row->units ?>" data-revenue="<?= esc_attr(number_format($row->revenue, 2, '.', '')) ?>">
            <td class="canriv-equipment-model"><a href="<?= esc_url($product->get_permalink()) ?>"><?= wp_kses_post($product->get_image('woocommerce_thumbnail', ['loading' => 'lazy', 'alt' => $product->get_name()])) ?></a><div><a class="canriv-equipment-name" href="<?= esc_url($product->get_permalink()) ?>"><?= esc_html($product->get_name()) ?></a><span class="canriv-equipment-sku">SKU: <?= esc_html($product->get_sku() ?: '—') ?></span></div></td>
            <td><span class="canriv-equipment-category"><?= esc_html(canriv_shop_category_label($product->get_sku())) ?></span></td>
            <td class="canriv-equipment-number"><?= esc_html(number_format_i18n($row->views)) ?></td>
            <td class="canriv-equipment-number"><strong><?= esc_html(number_format_i18n($cart_rate, 2)) ?>%</strong><small><?= esc_html(number_format_i18n($row->carts)) ?> adds</small></td>
            <td class="canriv-equipment-number"><?= esc_html(number_format_i18n($row->units)) ?><small>units</small></td>
            <td class="canriv-equipment-revenue"><?= wp_kses_post(wc_price($row->revenue)) ?></td>
            <td><a class="canriv-equipment-open" href="<?= esc_url($product->get_permalink()) ?>" aria-label="Open <?= esc_attr($product->get_name()) ?>"><span class="material-symbols-outlined" aria-hidden="true">open_in_new</span></a></td>
        </tr>
    <?php endforeach; ?>
<?php elseif ($name === 'product-footer') : ?>
    <?php $total_revenue = array_sum(array_map(static fn($row) => $row->revenue, $products)); ?>
    <span class="font-label-code text-label-sm text-secondary" id="canriv-equipment-count" data-total="<?= (int) count($products) ?>">Showing <?= esc_html(number_format_i18n(min(1, count($products)))) ?>–<?= esc_html(number_format_i18n(min(6, count($products)))) ?> of <?= esc_html(number_format_i18n(count($products))) ?> shop products</span>
    <span class="canriv-equipment-total"><span class="material-symbols-outlined" aria-hidden="true">payments</span>Combined revenue: <?= wp_kses_post(wc_price($total_revenue)) ?></span>
    <nav class="canriv-equipment-pagination" aria-label="Equipment table pages"><button type="button" id="canriv-equipment-prev" aria-label="Previous page" disabled><span class="material-symbols-outlined" aria-hidden="true">chevron_left</span></button><span id="canriv-equipment-page">1 / <?= esc_html((string) max(1, (int) ceil(count($products) / 6))) ?></span><button type="button" id="canriv-equipment-next" aria-label="Next page"><span class="material-symbols-outlined" aria-hidden="true">chevron_right</span></button></nav>
<?php endif; ?>
