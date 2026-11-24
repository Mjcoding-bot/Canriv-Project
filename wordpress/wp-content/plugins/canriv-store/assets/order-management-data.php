<?php defined('ABSPATH') || exit; ?>
<?php if ($name === 'tabs') : ?>
    <nav class="flex flex-wrap gap-space-xs" aria-label="Order management views">
        <?php foreach ([
            'orders' => ['receipt_long', 'All customer orders', $count_orders],
            'requests' => ['inbox', 'All requests & inquiries', $all_requests_query->found_posts],
            'reservations' => ['shelves', 'Warehouse pickup reservations', $request_query->found_posts],
            'quotes' => ['engineering', 'Project inquiries & quotes', $quote_query->found_posts],
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
        <?php $needs_decision = in_array($order->get_status(), ['pending', 'on-hold'], true) || ($order->has_status('processing') && $order->get_meta('canriv_admin_decision') !== 'accepted'); ?>
        <tr class="<?= $selected && $selected->get_id() === $order->get_id() ? 'bg-surface-container-low' : 'hover:bg-surface-container-low' ?>">
            <td class="p-space-md"><strong class="font-label-code text-primary">#<?= esc_html($order->get_order_number()) ?></strong><div class="text-secondary"><?= esc_html(wc_format_datetime($order->get_date_created())) ?></div></td>
            <td class="p-space-md"><strong><?= esc_html($order->get_formatted_billing_full_name() ?: 'Guest') ?></strong><div class="text-secondary"><?= esc_html($order->get_billing_company()) ?></div></td>
            <td class="p-space-md"><?php foreach ($order->get_items() as $item) : ?><div><?= esc_html($item->get_quantity() . ' × ' . $item->get_name()) ?></div><?php endforeach; ?></td>
            <td class="p-space-md"><?= $icon('local_shipping') ?> <?= esc_html($order->get_shipping_method() ?: '—') ?></td>
            <td class="p-space-md"><strong><?= wp_kses_post($order->get_formatted_order_total()) ?></strong><div class="text-secondary"><?= esc_html($order->get_payment_method_title()) ?></div></td>
            <td class="p-space-md"><span class="canriv-directory-badge bg-primary-fixed text-on-primary-fixed" data-status="<?= esc_attr(ucwords(str_replace('wc-', '', $order->get_status()))) ?>"><?= esc_html(wc_get_order_status_name($order->get_status())) ?></span></td>
            <td class="p-space-md"><div class="canriv-order-actions">
                <a class="canriv-directory-control bg-surface-container" href="<?= esc_url($url(['orders_page' => $page, 'inspect_order' => $order->get_id()]) . '#inspector-panel') ?>" aria-label="<?= esc_attr('Inspect order ' . $order->get_order_number()) ?>"><?= $icon('visibility') ?></a>
                <?php if ($needs_decision) : ?>
                    <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>">
                        <input type="hidden" name="action" value="canriv_update_order"><input type="hidden" name="order_id" value="<?= (int) $order->get_id() ?>"><input type="hidden" name="decision" value="accept">
                        <?php wp_nonce_field('canriv_update_order_' . $order->get_id()); ?>
                        <button class="canriv-directory-control bg-primary text-on-primary" type="submit">Accept</button>
                    </form>
                <?php endif; ?>
                <?php if ($needs_decision) : ?>
                    <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>">
                        <input type="hidden" name="action" value="canriv_update_order"><input type="hidden" name="order_id" value="<?= (int) $order->get_id() ?>"><input type="hidden" name="decision" value="decline">
                        <?php wp_nonce_field('canriv_update_order_' . $order->get_id()); ?>
                        <button class="canriv-directory-control bg-surface-container-high" type="submit" <?= $order->is_paid() ? 'title="Cancels the order; a refund must be issued separately." onclick="return confirm(\'This order is paid. Declining cancels it but does not issue a refund. Continue?\')"' : '' ?>>Decline</button>
                    </form>
                <?php endif; ?>
            </div></td>
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
        <div><h2 class="font-headline-md text-headline-md"><?= $icon($view === 'quotes' ? 'request_quote' : 'shelves') ?> <?= $view === 'quotes' ? 'Project inquiries &amp; quote requests' : ($view === 'reservations' ? 'Warehouse pickup reservations' : 'All reservations &amp; inquiries') ?></h2><p class="text-secondary">Review submitted customer details and accept or decline pending requests.</p></div>
        <a class="canriv-request-manage" href="<?= esc_url(admin_url('edit.php?post_type=canriv_request')) ?>">Manage requests</a>
    </div>
    <?php
    $request_update = isset($_GET['request_update']) && is_string($_GET['request_update']) ? sanitize_key(wp_unslash($_GET['request_update'])) : '';
    $request_update_messages = [
        'saved' => 'Request decision saved.',
        'already_saved' => 'This request already has that decision.',
        'already_decided' => 'This request was already decided and was not changed.',
    ];
    ?>
    <?php if (isset($request_update_messages[$request_update])) : ?><p class="canriv-request-feedback" role="status"><?= esc_html($request_update_messages[$request_update]) ?></p><?php endif; ?>
    <?php if (!$requests) : ?><p>No requests to display.</p><?php endif; ?>
    <div class="canriv-requests-list">
        <?php foreach ($requests as $request) : ?>
            <?php $status = get_post_meta($request->ID, 'canriv_request_status', true) ?: 'pending'; ?>
            <article class="canriv-request-row <?= $selected_request && (int) $selected_request->ID === (int) $request->ID ? 'is-selected' : '' ?>" data-status="<?= esc_attr($status) ?>">
                <div class="canriv-request-main">
                    <div class="canriv-request-heading">
                        <span class="canriv-request-id">#<?= (int) $request->ID ?></span>
                        <h3><?= esc_html($request->post_title) ?></h3>
                        <time datetime="<?= esc_attr(get_the_date('c', $request)) ?>"><?= esc_html(get_the_date('', $request)) ?></time>
                        <span class="canriv-request-status is-<?= esc_attr($status) ?>"><?= esc_html(ucfirst($status)) ?></span>
                    </div>
                    <?php
                    $request_details = [];
                    $request_message = '';
                    foreach (preg_split('/\r\n|\r|\n/', $request->post_content) as $line) {
                        $line = trim($line);
                        if ($line === '') continue;
                        $parts = explode(':', $line, 2);
                        $detail_label = preg_replace('/^Saved\s+/i', '', trim($parts[0]));
                        $detail_value = trim($parts[1] ?? '');
                        if ($detail_value === '') continue;
                        if (strcasecmp($detail_label, 'Message') === 0) {
                            $request_message = $detail_value;
                        } else {
                            $request_details[$detail_label] = $detail_value;
                        }
                    }
                    ?>
                    <?php if ($request_details || $request_message !== '') : ?>
                        <dl class="canriv-request-details">
                            <?php foreach ($request_details as $detail_label => $detail_value) : ?>
                                <div><dt><?= esc_html($detail_label) ?></dt><dd><?= esc_html($detail_value) ?></dd></div>
                            <?php endforeach; ?>
                            <?php if ($request_message !== '') : ?>
                                <div class="canriv-request-details-message"><dt>Message</dt><dd><?= esc_html($request_message) ?></dd></div>
                            <?php endif; ?>
                        </dl>
                    <?php endif; ?>
                </div>
                <div class="canriv-request-actions">
                    <?php if ($status === 'pending') : ?>
                    <div class="canriv-request-decisions">
                        <?php foreach (['accepted' => 'Accept', 'declined' => 'Decline'] as $next_status => $label) : ?>
                            <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>">
                                <input type="hidden" name="action" value="canriv_update_request">
                                <input type="hidden" name="request_id" value="<?= (int) $request->ID ?>">
                                <input type="hidden" name="request_status" value="<?= esc_attr($next_status) ?>">
                                <?php wp_nonce_field('canriv_update_request_' . $request->ID); ?>
                                <button class="canriv-request-action <?= $next_status === 'accepted' ? 'is-accept' : 'is-decline' ?>" type="submit"><?= $icon($next_status === 'accepted' ? 'check' : 'close') ?><?= esc_html($label) ?></button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <a class="canriv-request-open" href="<?= esc_url($url(['inspect_request' => $request->ID]) . '#inspector-panel') ?>">View details <span aria-hidden="true">→</span></a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php elseif ($name === 'inspector') : ?>
    <header class="p-space-md bg-inverse-surface text-inverse-on-surface flex items-center gap-space-sm">
        <?= $icon('manage_search') ?><div><span class="font-label-code text-label-sm uppercase"><?= $selected_request ? 'Request details' : 'Detailed record inspector' ?></span><h2 class="font-headline-sm text-headline-sm"><?= $selected_request ? '#' . (int) $selected_request->ID : ($selected ? '#' . esc_html($selected->get_order_number()) : 'Order details') ?></h2></div>
    </header>
    <div class="p-space-lg flex flex-col gap-space-md">
        <?php if ($selected_request) : ?>
            <?php $request_status = get_post_meta($selected_request->ID, 'canriv_request_status', true) ?: 'pending'; ?>
            <section class="canriv-request-inspector-summary">
                <h3><?= esc_html($selected_request->post_title) ?></h3>
                <p><?= esc_html(get_the_date('', $selected_request)) ?> <span class="canriv-request-status is-<?= esc_attr($request_status) ?>"><?= esc_html(ucfirst($request_status)) ?></span></p>
            </section>
            <section>
                <h3 class="font-label-code text-label-sm uppercase text-primary">Submitted details</h3>
                <dl class="canriv-request-inspector-details">
                    <?php foreach (preg_split('/\r\n|\r|\n/', $selected_request->post_content) as $detail) : ?>
                        <?php if ($detail === '') continue; ?>
                        <?php $parts = explode(':', $detail, 2); ?>
                        <div><dt><?= esc_html(preg_replace('/^Saved\s+/i', '', trim($parts[0]))) ?></dt><dd><?= esc_html(trim($parts[1] ?? '')) ?></dd></div>
                    <?php endforeach; ?>
                </dl>
            </section>
        <?php elseif (!$selected) : ?><p><?= $view === 'orders' ? 'No order selected.' : 'Select a request to view its details here.' ?></p><?php else : ?>
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
