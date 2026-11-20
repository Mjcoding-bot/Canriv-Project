<?php defined('ABSPATH') || exit; ?>
<?php if ($name === 'tabs') : ?>
    <nav class="flex flex-wrap gap-space-xs" aria-label="Order management views">
        <?php foreach ([
            'orders' => ['receipt_long', 'All customer orders', $count_orders],
            'reservations' => ['shelves', 'Warehouse pickup reservations', $request_query->found_posts],
            'quotes' => ['engineering', 'Quotation requests', $quote_query->found_posts],
        ] as $key => [$symbol, $label, $count]) : ?>
            <a class="canriv-directory-control <?= $view === $key ? 'bg-surface-container-lowest text-on-surface shadow-sm' : 'text-secondary hover:bg-surface-container' ?>" href="<?= esc_url(add_query_arg('order_view', $key, $base)) ?>" <?= $view === $key ? 'aria-current="page"' : '' ?>>
                <?= $icon($symbol) ?> <?= esc_html($label) ?> <span class="canriv-directory-badge bg-surface-container"><?= esc_html(number_format_i18n($count)) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
<?php elseif ($name === 'filters') : ?>
    <form class="flex flex-col sm:flex-row gap-space-sm w-full" method="get" action="<?= esc_url($base) ?>" aria-label="Filter orders">
        <label class="canriv-directory-search bg-surface-container-low"><?= $icon('search') ?><span class="screen-reader-text">Order number</span><input type="search" name="order_ref" placeholder="Search by order number" value="<?= esc_attr($filters['order_ref'] ?? '') ?>"></label>
        <select class="canriv-directory-control bg-surface-container-low" name="order_state" aria-label="Order status">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $key => $label) : ?><option value="<?= esc_attr($key) ?>" <?php selected($filters['order_state'] ?? '', $key); ?>><?= esc_html($label) ?></option><?php endforeach; ?>
        </select>
        <select class="canriv-directory-control bg-surface-container-low" name="order_period" aria-label="Date range">
            <option value="">All dates</option>
            <?php foreach ([7, 30, 90] as $days) : ?><option value="<?= $days ?>" <?php selected($filters['order_period'] ?? '', (string) $days); ?>>Last <?= $days ?> days</option><?php endforeach; ?>
        </select>
        <button class="canriv-directory-control bg-primary text-on-primary" type="submit"><?= $icon('filter_list') ?> Filter</button>
        <?php if ($filters) : ?><a class="canriv-directory-control bg-surface-container-high" href="<?= esc_url($base) ?>">Clear</a><?php endif; ?>
    </form>
<?php elseif ($name === 'rows') : ?>
    <?php if (!$orders) : ?><tr><td colspan="7" class="p-space-md">No orders match these filters.</td></tr><?php endif; ?>
    <?php foreach ($orders as $order) : ?>
        <tr class="<?= $selected && $selected->get_id() === $order->get_id() ? 'bg-surface-container-low' : 'hover:bg-surface-container-low' ?>">
            <td class="p-space-md"><strong class="font-label-code text-primary">#<?= esc_html($order->get_order_number()) ?></strong><div class="text-secondary"><?= esc_html(wc_format_datetime($order->get_date_created())) ?></div></td>
            <td class="p-space-md"><strong><?= esc_html($order->get_formatted_billing_full_name() ?: 'Guest') ?></strong><div class="text-secondary"><?= esc_html($order->get_billing_company()) ?></div></td>
            <td class="p-space-md"><?php foreach ($order->get_items() as $item) : ?><div><?= esc_html($item->get_quantity() . ' × ' . $item->get_name()) ?></div><?php endforeach; ?></td>
            <td class="p-space-md"><?= $icon('local_shipping') ?> <?= esc_html($order->get_shipping_method() ?: '—') ?></td>
            <td class="p-space-md"><strong><?= wp_kses_post($order->get_formatted_order_total()) ?></strong><div class="text-secondary"><?= esc_html($order->get_payment_method_title()) ?></div></td>
            <td class="p-space-md"><span class="canriv-directory-badge bg-primary-fixed text-on-primary-fixed"><?= esc_html(wc_get_order_status_name($order->get_status())) ?></span></td>
            <td class="p-space-md"><a class="canriv-directory-control bg-surface-container" href="<?= esc_url($url(['orders_page' => $page, 'inspect_order' => $order->get_id()]) . '#inspector-panel') ?>" aria-label="<?= esc_attr('Inspect order ' . $order->get_order_number()) ?>"><?= $icon('visibility') ?></a></td>
        </tr>
    <?php endforeach; ?>
<?php elseif ($name === 'pagination') : ?>
    <span class="font-label-code text-label-sm text-secondary">Page <?= $page ?> of <?= $pages ?></span>
    <nav class="flex gap-space-xs" aria-label="Order pagination">
        <?php if ($page > 1) : ?><a class="canriv-directory-control bg-surface-container" href="<?= esc_url($url(['orders_page' => $page - 1])) ?>">Previous</a><?php endif; ?>
        <?php if ($page < $pages) : ?><a class="canriv-directory-control bg-surface-container" href="<?= esc_url($url(['orders_page' => $page + 1])) ?>">Next</a><?php endif; ?>
    </nav>
<?php elseif ($name === 'requests') : ?>
    <div class="flex flex-wrap items-center justify-between gap-space-sm">
        <div><h2 class="font-headline-md text-headline-md"><?= $icon($view === 'quotes' ? 'request_quote' : 'shelves') ?> <?= $view === 'quotes' ? 'Quotation requests' : 'Warehouse pickup reservations' ?></h2><p class="text-secondary">Recent requests submitted through the Canriv website.</p></div>
        <a class="canriv-directory-control bg-surface-container" href="<?= esc_url(admin_url('edit.php?post_type=canriv_request')) ?>">Manage requests</a>
    </div>
    <?php if (!$requests) : ?><p>No requests to display.</p><?php endif; ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-space-md">
        <?php foreach ($requests as $request) : ?>
            <article class="p-space-md bg-surface-container-low rounded-lg">
                <strong class="font-label-code text-primary">#<?= (int) $request->ID ?></strong>
                <h3 class="font-headline-sm text-headline-sm"><?= esc_html($request->post_title) ?></h3>
                <p class="text-secondary"><?= esc_html(get_the_date('', $request)) ?></p>
                <p class="canriv-order-request text-body-sm"><?= esc_html($request->post_content) ?></p>
                <?php if ($link = get_edit_post_link($request->ID)) : ?><a class="text-primary font-semibold" href="<?= esc_url($link) ?>">Open request →</a><?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php elseif ($name === 'inspector') : ?>
    <header class="p-space-md bg-inverse-surface text-inverse-on-surface flex items-center gap-space-sm">
        <?= $icon('manage_search') ?><div><span class="font-label-code text-label-sm uppercase">Detailed record inspector</span><h2 class="font-headline-sm text-headline-sm"><?= $selected ? '#' . esc_html($selected->get_order_number()) : 'Order details' ?></h2></div>
    </header>
    <div class="p-space-lg flex flex-col gap-space-md">
        <?php if (!$selected) : ?><p>No order selected.</p><?php else : ?>
            <section>
                <h3 class="font-label-code text-label-sm uppercase text-primary">Client &amp; delivery details</h3>
                <strong><?= esc_html($selected->get_formatted_billing_full_name() ?: 'Guest') ?></strong>
                <p><?= esc_html($selected->get_billing_company()) ?></p>
                <p><?= wp_kses_post($selected->get_formatted_shipping_address() ?: $selected->get_formatted_billing_address()) ?></p>
            </section>
            <section>
                <h3 class="font-label-code text-label-sm uppercase text-primary">Order line items</h3>
                <?php foreach ($selected->get_items() as $item) : ?><div class="canriv-order-line bg-surface-container-low"><span><?= esc_html($item->get_quantity() . ' × ' . $item->get_name()) ?></span><strong><?= wp_kses_post($selected->get_formatted_line_subtotal($item)) ?></strong></div><?php endforeach; ?>
            </section>
            <section class="bg-surface-container-low p-space-md rounded-lg">
                <h3 class="font-label-code text-label-sm uppercase text-primary">Current status</h3>
                <p><?= esc_html(wc_get_order_status_name($selected->get_status())) ?> · <?= esc_html($selected->is_paid() ? 'Paid' : 'Payment not recorded') ?></p>
                <strong><?= wp_kses_post($selected->get_formatted_order_total()) ?></strong>
            </section>
            <section>
                <h3 class="font-label-code text-label-sm uppercase text-primary">Recent order notes</h3>
                <?php $notes = wc_get_order_notes(['order_id' => $selected->get_id(), 'limit' => 4]); ?>
                <?php if (!$notes) : ?><p>No notes yet.</p><?php endif; ?>
                <?php foreach ($notes as $note) : ?><p class="canriv-order-note text-body-sm"><strong><?= esc_html(wc_format_datetime($note->date_created)) ?></strong><br><?= esc_html(wp_strip_all_tags($note->content)) ?></p><?php endforeach; ?>
            </section>
            <a class="canriv-directory-control bg-primary text-on-primary" href="<?= esc_url($selected->get_edit_order_url()) ?>"><?= $icon('edit_note') ?> Manage order in WooCommerce</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
