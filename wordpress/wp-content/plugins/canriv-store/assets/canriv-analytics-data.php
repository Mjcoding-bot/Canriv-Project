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
        <h2 class="font-headline-md text-headline-md">Audience activity · most visited pages</h2>
        <p class="text-secondary text-body-sm">Counts public page and product views as they happen.</p>
        <?php if (!$pages) : ?><p class="mt-space-md">No page views recorded for this period yet.</p><?php endif; ?>
        <ol class="canriv-traffic-pages">
            <?php foreach ($pages as $row) : ?><li><a href="<?= esc_url(get_permalink($row->object_id)) ?>"><?= esc_html(get_the_title($row->object_id) ?: 'Untitled page') ?></a><strong class="font-label-code"><?= esc_html(number_format_i18n($row->views)) ?> views</strong></li><?php endforeach; ?>
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
    <div><h2 class="font-headline-md text-headline-md">Most-viewed equipment</h2><p class="text-secondary text-body-sm">Products ranked by detail page views, with successful cart additions alongside.</p></div>
<?php elseif ($name === 'products') : ?>
    <?php if (!$products) : ?><tr><td colspan="5" class="p-space-md">No product activity recorded for this period yet.</td></tr><?php endif; ?>
    <?php foreach ($products as $row) :
        $product = wc_get_product($row->object_id);
        if (!$product) { continue; }
        ?>
        <tr class="hover:bg-surface-container-low">
            <td class="p-space-md"><strong><?= esc_html($product->get_name()) ?></strong><?php if ($product->get_sku()) : ?><div class="font-label-code text-label-sm text-secondary">SKU: <?= esc_html($product->get_sku()) ?></div><?php endif; ?></td>
            <td class="p-space-md"><?= esc_html(implode(', ', wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names']))) ?></td>
            <td class="p-space-md font-label-code"><?= esc_html(number_format_i18n($row->views)) ?></td>
            <td class="p-space-md font-label-code"><?= esc_html(number_format_i18n($row->carts)) ?></td>
            <td class="p-space-md"><a class="canriv-directory-control bg-surface-container" href="<?= esc_url($product->get_permalink()) ?>">Open product <span class="material-symbols-outlined canriv-directory-icon" aria-hidden="true">open_in_new</span></a></td>
        </tr>
    <?php endforeach; ?>
<?php elseif ($name === 'product-footer') : ?>
    <span class="font-label-code text-label-sm text-secondary"><?= (int) count($products) ?> products shown</span>
    <span class="text-body-sm text-secondary">Activity starts when local tracking is installed.</span>
<?php endif; ?>
