<?php defined('ABSPATH') || exit; ?>
<?php if ($name === 'filters') : ?>
    <form class="flex flex-col gap-space-md" method="get" action="<?= esc_url($base) ?>" aria-label="Filter the account directory">
        <div class="flex flex-col sm:flex-row gap-space-sm">
            <label class="canriv-directory-search bg-surface-container-low">
                <?= $icon('search') ?>
                <span class="screen-reader-text">Search accounts</span>
                <input type="search" name="account_search" placeholder="Search name, email, or username" value="<?= esc_attr($filters['account_search'] ?? '') ?>">
            </label>
            <label class="screen-reader-text" for="canriv-directory-role">Account role</label>
            <select id="canriv-directory-role" class="canriv-directory-control bg-surface-container-low" name="account_role">
                <option value="">All account roles</option>
                <?php foreach (wp_roles()->get_names() as $role => $label) : if ($role === 'subscriber') continue; ?>
                    <option value="<?= esc_attr($role) ?>" <?php selected($filters['account_role'] ?? '', $role); ?>><?= esc_html(translate_user_role($label)) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="canriv-directory-control bg-primary text-on-primary" type="submit"><?= $icon('filter_list') ?> Filter</button>
            <?php if ($filters) : ?><a class="canriv-directory-control bg-surface-container-high" href="<?= esc_url($base) ?>">Clear</a><?php endif; ?>
        </div>
        <div class="flex flex-wrap gap-space-xs" aria-label="Account segments">
            <?php foreach (['' => 'All users', 'customer' => 'Customers', 'administrator' => 'Administrators', 'shop_manager' => 'Shop managers'] as $role => $label) :
                $active = ($filters['account_role'] ?? '') === $role;
                $segment = add_query_arg(array_filter(['account_search' => $filters['account_search'] ?? '', 'account_role' => $role]), $base);
                ?>
                <a class="canriv-directory-control <?= $active ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface' ?>" href="<?= esc_url($segment) ?>" <?= $active ? 'aria-current="true"' : '' ?>><?= esc_html($label) ?> (<?= esc_html(number_format_i18n($role === '' ? $counts['total_users'] : ($roles[$role] ?? 0))) ?>)</a>
            <?php endforeach; ?>
        </div>
    </form>
<?php elseif ($name === 'rows') : ?>
    <?php if (!$users) : ?><tr><td class="px-space-md py-space-md" colspan="7">No accounts match these filters.</td></tr><?php endif; ?>
    <?php foreach ($users as $user) :
        $customer = new WC_Customer($user->ID);
        $state = $customer->get_billing_state();
        $location = implode(', ', array_filter([$customer->get_billing_city(), $regions[$state] ?? $state]));
        $is_selected = $selected && $selected->ID === $user->ID;
        ?>
        <tr class="<?= $is_selected ? 'bg-primary/5' : 'hover:bg-surface-container-low' ?>">
            <td class="py-space-md px-space-md"><div class="flex items-center gap-space-sm">
                <span class="canriv-directory-avatar bg-primary-container text-on-primary-container"><?= $initial($user) ?></span>
                <div><strong class="font-title-md text-title-md"><?= esc_html($user->display_name) ?></strong><div class="text-secondary"><?= esc_html($user->user_login) ?></div><div class="font-label-code text-label-sm text-primary">ID: #<?= (int) $user->ID ?></div></div>
            </div></td>
            <td class="py-space-md px-space-md"><span class="canriv-directory-badge bg-primary-fixed text-on-primary-fixed"><?= esc_html(canriv_directory_role($user)) ?></span></td>
            <td class="py-space-md px-space-md"><div><?= esc_html($customer->get_billing_phone() ?: '—') ?></div><div class="text-secondary"><?= esc_html($user->user_email) ?></div><?php if ($location) : ?><div class="font-label-code text-label-sm text-secondary"><?= esc_html($location) ?></div><?php endif; ?></td>
            <td class="py-space-md px-space-md"><?= esc_html(mysql2date(get_option('date_format'), $user->user_registered)) ?></td>
            <td class="py-space-md px-space-md text-right"><div class="font-label-price text-label-price"><?= wp_kses_post(wc_price($customer->get_total_spent())) ?></div><div class="text-secondary"><?= (int) $customer->get_order_count() ?> orders</div></td>
            <td class="py-space-md px-space-md text-center"><span class="canriv-directory-badge <?= $user->roles ? 'bg-primary-container text-on-primary-container' : 'bg-surface-container text-secondary' ?>"><?= $user->roles ? 'Assigned' : 'Unassigned' ?></span></td>
            <td class="py-space-md px-space-md text-right"><div class="flex justify-end gap-space-xs">
                <a class="canriv-directory-control bg-surface-container" href="<?= esc_url($url(['account_page' => $page, 'inspect_account' => $user->ID]) . '#canriv-account-inspector') ?>" aria-label="<?= esc_attr('Inspect account for ' . $user->display_name) ?>"><?= $icon('account_box') ?></a>
                <?php if ($edit_url = get_edit_user_link($user->ID)) : ?><a class="canriv-directory-control bg-surface-container" href="<?= esc_url($edit_url) ?>" aria-label="<?= esc_attr('Edit account for ' . $user->display_name) ?>"><?= $icon('open_in_new') ?></a><?php endif; ?>
            </div></td>
        </tr>
    <?php endforeach; ?>
<?php elseif ($name === 'pagination') : ?>
    <span class="font-label-code text-label-sm text-secondary">Page <?= (int) $page ?> of <?= (int) $pages ?></span>
    <nav class="flex gap-space-xs" aria-label="Directory pagination">
        <?php if ($page > 1) : ?><a class="canriv-directory-control bg-surface-container" href="<?= esc_url($url(['account_page' => $page - 1])) ?>">Previous</a><?php endif; ?>
        <?php if ($page < $pages) : ?><a class="canriv-directory-control bg-surface-container" href="<?= esc_url($url(['account_page' => $page + 1])) ?>">Next</a><?php endif; ?>
    </nav>
<?php elseif ($name === 'inspector' && $selected) :
    $customer = new WC_Customer($selected->ID);
    ?>
    <section id="canriv-account-inspector" class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm flex flex-col gap-space-md" aria-label="Account inspector">
        <div class="flex items-center gap-space-sm">
            <span class="canriv-directory-avatar canriv-directory-avatar-large bg-primary text-on-primary"><?= $initial($selected) ?></span>
            <div><h2 class="font-headline-sm text-headline-sm"><?= esc_html($selected->display_name) ?></h2><p class="text-body-sm text-secondary"><?= esc_html($selected->user_login) ?></p><span class="canriv-directory-badge bg-primary-fixed text-on-primary-fixed"><?= esc_html(canriv_directory_role($selected)) ?></span></div>
        </div>
        <div class="bg-surface-container-low rounded-xl p-space-md">
            <h3 class="font-label-code text-label-sm uppercase text-primary">Account details</h3>
            <dl class="canriv-directory-details text-body-sm">
                <div><dt>Email</dt><dd><?= esc_html($selected->user_email) ?></dd></div>
                <div><dt>Phone</dt><dd><?= esc_html($customer->get_billing_phone() ?: '—') ?></dd></div>
                <div><dt>User ID</dt><dd>#<?= (int) $selected->ID ?></dd></div>
                <div><dt>Registered</dt><dd><?= esc_html(mysql2date(get_option('date_format'), $selected->user_registered)) ?></dd></div>
            </dl>
        </div>
        <div class="flex flex-wrap gap-space-sm">
            <div class="bg-surface-container-low rounded-xl p-space-md"><span class="font-label-code text-label-sm text-secondary">Lifetime spend</span><div class="font-label-price text-label-price"><?= wp_kses_post(wc_price($customer->get_total_spent())) ?></div></div>
            <div class="bg-surface-container-low rounded-xl p-space-md"><span class="font-label-code text-label-sm text-secondary">Orders</span><div class="font-label-price text-label-price"><?= (int) $customer->get_order_count() ?></div></div>
        </div>
        <?php if ($edit_url = get_edit_user_link($selected->ID)) : ?><a class="canriv-directory-control bg-primary text-on-primary" href="<?= esc_url($edit_url) ?>"><?= $icon('edit_note') ?> Edit account</a><?php endif; ?>
    </section>
    <section class="bg-surface-container-lowest rounded-xl p-space-md shadow-sm">
        <h3 class="font-label-code text-label-sm text-secondary uppercase"><?= $icon('local_shipping') ?> Saved delivery address</h3>
        <p><?= wp_kses_post($customer->get_shipping_address_1() ? WC()->countries->get_formatted_address($customer->get_shipping()) : 'No delivery address saved.') ?></p>
    </section>
<?php endif; ?>
