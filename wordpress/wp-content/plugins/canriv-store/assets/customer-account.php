<?php
defined('ABSPATH') || exit;
$icon = static fn($name) => '<span class="material-symbols-outlined canriv-customer-icon" aria-hidden="true">' . esc_html($name) . '</span>';
?>
<div class="canriv-customer-portal">
    <div class="canriv-customer-card canriv-customer-banner">
        <div class="canriv-customer-avatar" aria-hidden="true"><?= esc_html(mb_strtoupper($initials)) ?></div>
        <div>
            <div class="canriv-customer-identity"><h2><?= esc_html($user->display_name) ?></h2>
                <span class="canriv-customer-id font-label-code">ID: #<?= (int) $user->ID ?></span>
                <span class="canriv-customer-badge">Customer</span>
            </div>
            <p class="canriv-customer-contact"><?= $icon('mail') ?> <?= esc_html($user->user_email) ?><?php if ($customer->get_billing_phone()) : ?> · <?= $icon('call') ?> <?= esc_html($customer->get_billing_phone()) ?><?php endif; ?></p>
        </div>
        <a class="canriv-customer-outline" href="<?= esc_url(wc_logout_url()) ?>"><?= $icon('logout') ?> Log out</a>
    </div>
    <div class="canriv-customer-columns">
        <section class="canriv-customer-card" id="canriv-profile" aria-labelledby="canriv-profile-title">
            <h2 id="canriv-profile-title">Profile &amp; installation address</h2>
            <p>Manage your contact details and default delivery address.</p>
            <?php
            $profile_messages = [
                'saved' => 'Account changes saved.',
                'invalid' => 'Check your contact details, address, and four-digit postal code.',
                'phone_invalid' => 'Enter a valid Philippine mobile number, such as 0917 123 4567.',
                'email_used' => 'That email address is already in use.',
                'password_current' => 'Enter your correct current password to make a password change.',
                'password_invalid' => 'Use a unique password of 8–128 characters and make sure both new password fields match.',
            ];
            $status = isset($_GET['profile_status']) && is_string($_GET['profile_status']) ? sanitize_key($_GET['profile_status']) : '';
            ?>
            <?php if (isset($profile_messages[$status])) : ?>
                <p class="canriv-notice <?= $status === 'saved' ? 'success' : 'error' ?>" role="status"><?= esc_html($profile_messages[$status]) ?></p>
            <?php endif; ?>
            <form method="post" action="<?= esc_url(admin_url('admin-post.php')) ?>">
                <input type="hidden" name="action" value="canriv_save_customer_profile">
                <?php wp_nonce_field('canriv_save_customer_profile'); ?>
                <fieldset>
                    <legend class="font-label-code"><?= $icon('person') ?> Personal details</legend>
                    <div class="canriv-customer-fields">
                        <?php
                        $personal = [
                            ['first_name', 'First name', $user->first_name, 'text', 'given-name', true],
                            ['last_name', 'Last name', $user->last_name, 'text', 'family-name', true],
                            ['display_name', 'Display name (shown on reviews)', $user->display_name, 'text', 'nickname', true],
                            ['email', 'Email address', $user->user_email, 'email', 'email', true],
                            ['phone', 'Philippine mobile number', $customer->get_billing_phone(), 'tel', 'tel', false],
                        ];
                        foreach ($personal as [$key, $label, $value, $type, $autocomplete, $required]) : ?>
                            <label><?= esc_html($label) ?><input type="<?= esc_attr($type) ?>" name="<?= esc_attr($key) ?>" value="<?= esc_attr($value) ?>" autocomplete="<?= esc_attr($autocomplete) ?>" <?= $key === 'phone' ? 'inputmode="tel" maxlength="16" placeholder="0917 123 4567"' : '' ?> <?= $required ? 'required' : '' ?>></label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <fieldset>
                    <legend class="font-label-code"><?= $icon('pin_drop') ?> Delivery &amp; installation address</legend>
                    <div class="canriv-customer-fields canriv-customer-address">
                        <label class="canriv-customer-wide">Street address<input name="address_1" autocomplete="address-line1" value="<?= esc_attr($customer->get_shipping_address_1()) ?>" required></label>
                        <label class="canriv-customer-wide">Unit, floor, or building (optional)<input name="address_2" autocomplete="address-line2" value="<?= esc_attr($customer->get_meta('canriv_address_unit')) ?>"></label>
                        <label class="canriv-customer-third">Barangay<input name="barangay" autocomplete="address-level3" value="<?= esc_attr($customer->meta_exists('canriv_address_barangay') ? $customer->get_meta('canriv_address_barangay') : $customer->get_shipping_address_2()) ?>" required></label>
                        <label class="canriv-customer-third">City / Municipality<input name="city" autocomplete="address-level2" value="<?= esc_attr($customer->get_shipping_city()) ?>" required></label>
                        <label class="canriv-customer-third">Province / Region<input class="canriv-readonly-field" type="text" value="<?= esc_attr($provinces[$customer->get_shipping_state()] ?? 'No province on file') ?>" readonly aria-readonly="true"><input type="hidden" name="state" value="<?= esc_attr($customer->get_shipping_state()) ?>"></label>
                        <label>Postal / ZIP code<input name="postcode" autocomplete="postal-code" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" value="<?= esc_attr($customer->get_shipping_postcode()) ?>" required></label>
                        <label>Installation site notes (optional)<textarea name="installation_notes" maxlength="500" rows="1"><?= esc_textarea($customer->get_meta('canriv_installation_notes')) ?></textarea></label>
                    </div>
                </fieldset>
                <fieldset id="canriv-password">
                    <legend class="font-label-code"><?= $icon('lock') ?> Change password</legend>
                    <p>Leave these fields blank to keep your current password.</p>
                    <div class="canriv-customer-fields canriv-customer-password-fields">
                        <label>Current password<input type="password" name="password_current" autocomplete="current-password"></label>
                        <label>New password (8+ characters)<input type="password" name="password_new" autocomplete="new-password" minlength="8"></label>
                        <label>Confirm new password<input type="password" name="password_confirm" autocomplete="new-password" minlength="8"></label>
                    </div>
                </fieldset>
                <div class="canriv-customer-save"><span><?= $icon('verified_user') ?> Saved addresses are used at checkout</span><button class="canriv-button" type="submit"><?= $icon('save') ?> Save changes</button></div>
            </form>
        </section>
        <aside class="canriv-customer-aside">
            <section class="canriv-customer-card">
                <div class="canriv-customer-overview"><h2>Account overview</h2><span class="font-label-code">Active</span></div>
                <div class="canriv-customer-metrics">
                    <div class="canriv-customer-metric">Total orders<strong><?= (int) $orders->total ?></strong><a href="<?= esc_url(wc_get_account_endpoint_url('orders')) ?>">Order history</a></div>
                    <div class="canriv-customer-metric">Warranty support<strong><?= $icon('shield') ?></strong><a href="<?= esc_url(canriv_page_url('contact')) ?>">Ask about coverage</a></div>
                </div>
                <dl class="canriv-customer-facts"><div><dt>Registered date</dt><dd><?= esc_html(mysql2date(get_option('date_format'), $user->user_registered)) ?></dd></div></dl>
                <div class="canriv-customer-links">
                    <a href="#canriv-password"><?= $icon('lock') ?> Change password</a>
                    <a href="<?= esc_url(wc_get_account_endpoint_url('orders')) ?>"><?= $icon('receipt_long') ?> Order details</a>
                </div>
            </section>
            <section class="canriv-customer-card canriv-customer-support">
                <div class="canriv-concierge-icon"><?= $icon('support_agent') ?></div><div>
                <h2>Customer engineering concierge</h2>
                <p>Need help with delivery, installation, or your order?</p>
                <a href="tel:+639454481254"><?= $icon('call') ?> 0945 448 1254</a> ·
                <a href="mailto:sales@canriv.com">sales@canriv.com</a></div>
            </section>
        </aside>
    </div>
    <section class="canriv-customer-card canriv-customer-history">
        <div class="canriv-customer-history-heading"><div><h2>My orders &amp; reservations</h2><p>Review purchases and pickup requests.</p></div><a href="<?= esc_url(wc_get_account_endpoint_url('orders')) ?>">All orders →</a></div>
        <?php if (!$orders->orders) : ?><p>No orders yet. <a href="<?= esc_url(canriv_page_url('catalog')) ?>">Browse the shop</a>.</p><?php endif; ?>
        <?php foreach ($orders->orders as $order) : ?>
            <article class="canriv-customer-order">
                <div>
                    <h3>Order #<?= esc_html($order->get_order_number()) ?></h3>
                    <p><?= esc_html(wc_format_datetime($order->get_date_created())) ?> · <span class="canriv-customer-status"><?= esc_html($order->get_meta('canriv_admin_decision') === 'accepted' || $order->has_status('processing') ? 'Accepted' : wc_get_order_status_name($order->get_status())) ?></span></p>
                    <ul><?php foreach ($order->get_items() as $item) : ?><li><?= esc_html($item->get_quantity() . ' × ' . $item->get_name()) ?></li><?php endforeach; ?></ul>
                </div>
                <div class="canriv-customer-order-total"><small>Total amount</small><strong><?= wp_kses_post($order->get_formatted_order_total()) ?></strong>
                    <a class="canriv-customer-outline" href="<?= esc_url($order->get_view_order_url()) ?>"><?= $icon('visibility') ?> View order details</a></div>
            </article>
        <?php endforeach; ?>
        <h3>Pickup reservations</h3>
        <?php if (!$requests) : ?><p>No pickup requests on this account.</p><?php endif; ?>
        <?php foreach ($requests as $request) : ?>
            <article class="canriv-customer-order">
                <div><h3>Pickup request #<?= (int) $request->ID ?></h3><p><?= esc_html(get_the_date('', $request)) ?> · Received</p></div>
                <p class="canriv-customer-request"><?= esc_html($request->post_content) ?></p>
            </article>
        <?php endforeach; ?>
        <a href="<?= esc_url(canriv_page_url('reservation')) ?>">Request pickup →</a>
    </section>
</div>
