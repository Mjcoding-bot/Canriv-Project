<?php
/**
 * Canriv enterprise login form.
 * Overrides woocommerce/myaccount/form-login.php.
 */

defined('ABSPATH') || exit;

$icon = static fn($name) => '<span class="material-symbols-outlined" aria-hidden="true">' . esc_html($name) . '</span>';
$username = (!empty($_POST['username']) && is_string($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : '';
?>

<?php do_action('woocommerce_before_customer_login_form'); ?>

<div class="canriv-signin">
    <div class="canriv-signin-topbar">
        <div class="canriv-signin-meta">
            <span class="mt"><?php echo $icon('shield'); ?> PCAAB AAA LIC. <b>#48919-HVAC</b></span>
            <span class="sep">·</span>
            <span>SEC REG. CS201219842</span>
            <span class="sep">·</span>
            <span class="mt"><?php echo $icon('business_center'); ?> DOLE <b>DO-174 Accredited Engineering Contractor</b></span>
        </div>
        <span class="canriv-signin-status">NORTH LUZON HUB ONLINE</span>
    </div>

    <div class="canriv-signin-main">
        <aside class="canriv-signin-showcase">
            <h2>Integrated Climate<br><span>&amp; Vertical Transit</span></h2>
            <p class="canriv-signin-lede">Shop air conditioners, manage your orders, and track warranty and maintenance in one place.</p>

            <div class="canriv-signin-hotline">
                <p class="hotline-title">Need help?</p>
                <p class="hotline-contact">
                    <a href="tel:+639286439559"><?php echo $icon('call'); ?>(92) 8643-9559</a>
                    <a href="tel:+639454481254"><?php echo $icon('smartphone'); ?>0945 448 1254</a>
                </p>
                <p class="hotline-mail"><?php echo $icon('mail'); ?><a href="mailto:canrivj@pasil.com">canrivj@pasil.com</a></p>
            </div>
        </aside>

        <section class="canriv-signin-card" aria-labelledby="canriv-signin-title">
            <div class="card-top">
                <span class="tls"><?php echo $icon('lock'); ?> Secure Connection</span>
            </div>
            <h1 id="canriv-signin-title">Sign in to your Canriv account</h1>
            <p class="card-lede">Access your commercial HVAC maintenance logs, residential aircon orders, elevator maintenance schedules, and warranty registrations.</p>

            <form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>
                <?php do_action('woocommerce_login_form_start'); ?>

                <div class="canriv-signin-field">
                    <div class="field-head">
                        <label for="username">Email Address</label>
                    </div>
                    <div class="field-wrap">
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo $username; ?>" placeholder="you@email.com" required aria-required="true" />
                    </div>
                </div>

                <div class="canriv-signin-field">
                    <div class="field-head">
                        <label for="password">Password</label>
                        <a class="forgot" href="<?php echo esc_url(wp_lostpassword_url()); ?>">Forgot Password?</a>
                    </div>
                    <div class="field-wrap">
                        <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" placeholder="••••••••••" required aria-required="true" />
                        <button type="button" class="canriv-pw-toggle" aria-label="Show password" data-canriv-pw-toggle>
                            <span class="material-symbols-outlined" aria-hidden="true">visibility_off</span>
                        </button>
                    </div>
                </div>

                <?php do_action('woocommerce_login_form'); ?>

                <div class="canriv-signin-remember">
                    <label>
                        <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" checked />
                        <span>Remember me for 30 days</span>
                    </label>
                </div>

                <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>

                <button type="submit" class="woocommerce-button button woocommerce-form-login__submit" name="login" value="Sign in to portal">Sign in to portal <span class="material-symbols-outlined submit-arrow" aria-hidden="true">arrow_forward</span></button>

                <?php do_action('woocommerce_login_form_end'); ?>
            </form>

            <div class="canriv-signin-foot">
                <span>Don't have an enterprise or client login?</span>
                <a href="<?php echo esc_url(canriv_page_url('registration')); ?>">Register New Account <span class="material-symbols-outlined" aria-hidden="true">open_in_new</span></a>
            </div>
        </section>
    </div>

    <div class="canriv-signin-features">
        <div>
            <span class="ico"><?php echo $icon('verified_user'); ?></span>
            <div><strong>AUTHORIZED MANUFACTURER DISTRO</strong><p>Direct OEM warranties for Daikin, Carrier, Midea, Mitsubishi Electric, and Canriv traction elevators.</p></div>
        </div>
        <div>
            <span class="ico"><?php echo $icon('workspace_premium'); ?></span>
            <div><strong>DOLE SAFETY CERTIFIED</strong><p>OSH-compliant HVAC riggers and vertical transit certified engineers dispatching daily across Luzon.</p></div>
        </div>
        <div>
            <span class="ico"><?php echo $icon('groups'); ?></span>
            <div><strong>LIVE CENTRAL TELEMETRY</strong><p>Commercial accounts receive automated chiller efficiency alerts, load checks, and predictive PMS tracking.</p></div>
        </div>
    </div>

    <div class="canriv-signin-footer">
        <div class="items">
            <span><?php echo $icon('lock'); ?>256-BIT SSL Encrypted Session</span>
            <span><?php echo $icon('verified'); ?>SEC &amp; DOLE Certified Contractor</span>
            <span><?php echo $icon('local_shipping'); ?>North Luzon Hub &amp; Nationwide Logistics</span>
        </div>
        <span>&copy; 2024 Canriv Corporation. All engineering rights reserved.</span>
    </div>
</div>

<?php do_action('woocommerce_after_customer_login_form'); ?>
