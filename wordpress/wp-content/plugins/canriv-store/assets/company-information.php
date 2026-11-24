<?php
defined('ABSPATH') || exit;

if ($section === 'company-brands') :
    $brands = [
        ['name' => 'Mitsubishi Electric', 'slug' => 'mitsubishi-electric', 'badge' => 'Mr. Slim & Inverters'],
        ['name' => 'Daikin',               'slug' => 'daikin',              'badge' => 'VRV & Inverters'],
        ['name' => 'Carrier',              'slug' => 'carrier',             'badge' => 'XPower Inverters'],
        ['name' => 'Panasonic',            'slug' => 'panasonic',           'badge' => 'nanoe™ X Series'],
        ['name' => 'Trane',                'slug' => 'trane',               'badge' => 'Applied Systems'],
        ['name' => 'York',                 'slug' => 'york',                'badge' => 'Central Chillers'],
        ['name' => 'Hitachi',              'slug' => 'hitachi',             'badge' => 'Commercial VRF'],
        ['name' => 'LG',                   'slug' => 'lg',                  'badge' => 'Multi V VRF'],
        ['name' => 'Gree',                 'slug' => 'gree',                'badge' => 'GMV Commercial'],
        ['name' => 'Midea',                'slug' => 'midea',               'badge' => 'Commercial & Split'],
        ['name' => 'Samsung',              'slug' => 'samsung',             'badge' => 'WindFree™ Tech'],
        ['name' => 'Koppel',               'slug' => 'koppel',              'badge' => 'Tropicalized Split'],
    ];
    $brands_dir = plugins_url('assets/brands/', dirname(__DIR__) . '/canriv-store.php');
    ?>
    <section class="canriv-company-info canriv-brands-premium" aria-labelledby="canriv-brand-heading">
        <div class="canriv-company-inner">
            <div class="canriv-brands-header">
                <span class="canriv-brands-pill">
                    <svg class="canriv-inline-svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="m9.55 18-5.7-5.7 1.425-1.425L9.55 15.15l9.175-9.175L20.15 7.4Z"/></svg>
                    OFFICIAL ACCREDITATION &amp; DIRECT SUPPLY
                </span>
                <h2 id="canriv-brand-heading">Engineered Partnerships with World-Leading HVAC Brands</h2>
                <p>Canriv is an officially accredited sales, installation, and warranty partner for premier residential inverter and commercial VRF systems across North Luzon and the Philippines. Benefit from factory-direct pricing, guaranteed manufacturer warranties, and certified engineering installation.</p>
            </div>

            <div class="canriv-brand-grid-modern" role="list">
                <?php foreach ($brands as $brand) : ?>
                    <div class="canriv-brand-card" role="listitem">
                        <div class="canriv-brand-card-inner">
                            <span class="canriv-brand-badge"><?= esc_html($brand['badge']) ?></span>
                            <div class="canriv-brand-img-wrap">
                                <img src="<?= esc_url($brands_dir . $brand['slug'] . '.png') ?>" alt="<?= esc_attr($brand['name']) ?>" loading="lazy" decoding="async">
                            </div>
                            <span class="canriv-brand-name"><?= esc_html($brand['name']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="canriv-brands-trust-row">
                <div class="canriv-trust-item">
                    <div class="canriv-trust-icon-box">
                        <svg class="canriv-inline-svg" viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M12 22q-3.475-.875-5.738-3.987Q4 14.9 4 11.1V5l8-3 8 3v6.1q0 3.8-2.262 6.913Q15.475 21.125 12 22Zm0-2.1q2.6-.825 4.3-3.3 1.7-2.475 1.7-5.5V6.375l-6-2.25-6 2.25V11.1q0 3.025 1.7 5.5 1.7 2.475 4.3 3.3Z"/></svg>
                    </div>
                    <div class="canriv-trust-text">
                        <strong>100% Genuine OEM Warranty</strong>
                        <span>Direct parts and compressor coverage backed by brand partners</span>
                    </div>
                </div>
                <div class="canriv-trust-item">
                    <div class="canriv-trust-icon-box">
                        <svg class="canriv-inline-svg" viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="m21.7 20.3-4.725-4.725q.7-.9 1.063-2.062Q18.4 12.35 18.4 11.15q0-3.025-2.138-5.162Q14.125 3.85 11.1 3.85q-.3 0-.575.025-.275.025-.575.075l3.225 3.225q.275.275.275.688t-.275.687q-.275.275-.7.275t-.7-.275L9 4.975q-.9.55-1.575 1.4Q6.75 7.225 6.75 8.35q0 .3.025.575.025.275.075.575l3.225-3.225q.275-.275.7-.275t.7.275q.275.275.275.7t-.275.7L8.175 10.55q.55.9 1.4 1.575.85.675 1.975.675 1.2 0 2.363-.362t2.062-1.063l4.725 4.725q.275.275.7.275t.7-.275q.3-.3.3-.712 0-.413-.3-.713Z"/></svg>
                    </div>
                    <div class="canriv-trust-text">
                        <strong>Certified Master Installers</strong>
                        <span>Factory-trained technicians adhering to precise manufacturer specs</span>
                    </div>
                </div>
                <div class="canriv-trust-item">
                    <div class="canriv-trust-icon-box">
                        <svg class="canriv-inline-svg" viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M4 17h1.15q.425-1.175 1.438-1.925T8.85 14.325q1.25 0 2.262.75T12.55 17h4.9q.425-1.175 1.438-1.925T21.15 14.325q1.25 0 2.263.75T24.85 17H26v-6l-3-4h-5V3H4v14Zm4.85 1.5q.625 0 1.062-.438.438-.437.438-1.062t-.438-1.062Q9.475 15.5 8.85 15.5t-1.063.438q-.437.437-.437 1.062t.437 1.062q.438.438 1.063.438Zm12.3 0q.625 0 1.063-.438.437-.437.437-1.062t-.437-1.062q-.438-.438-1.063-.438t-1.062.438q-.438.437-.438 1.062t.438 1.062q.437.438 1.062.438ZM6 12V5h10v7H6Zm12 0V9h3.7l2.25 3H18Z"/></svg>
                    </div>
                    <div class="canriv-trust-text">
                        <strong>Direct Warehouse Sourcing</strong>
                        <span>In-stock inventory with swift regional dispatch and installation</span>
                    </div>
                </div>
            </div>

            <div class="canriv-brands-actions">
                <a class="canriv-btn-primary" href="<?= esc_url(canriv_page_url('contact') . '#canriv-free-quote') ?>">
                    <span>Request Custom Brand Quote</span>
                    <svg class="canriv-inline-svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="m14 18-1.4-1.45 4.15-4.15H5v-2h11.75L12.6 6.25 14 4.8l6.6 6.6Z"/></svg>
                </a>
                <a class="canriv-btn-secondary" href="<?= esc_url(canriv_page_url('catalog')) ?>">
                    <svg class="canriv-inline-svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M4 19h16V5H4v14Zm0 2q-.825 0-1.412-.587Q2 19.825 2 19V5q0-.825.588-1.413Q3.175 3 4 3h16q.825 0 1.413.587Q22 4.175 22 5v14q0 .825-.587 1.413Q20.825 21 20 21H4Zm2-4h12v-2H6v2Zm0-4h12v-2H6v2Zm0-4h12V7H6v2Z"/></svg>
                    <span>Browse Equipment Catalog</span>
                </a>
            </div>
        </div>
    </section>
<?php elseif ($section === 'aircon-shop') :
    $aircons = [];
    $quote_url = canriv_page_url('contact') . '#canriv-free-quote';
    ?>
        <?php
        $shop_categories = ['MSZ-GC35NA' => 'split', 'MSZ-GC50NA' => 'split', 'PLY-SP36EA' => 'cassette', 'PSY-SP36KA' => 'floor'];
        foreach ($aircons as [$model, $type, $file, $width, $height, $description]) :
            ?>
            <article class="bg-surface-container-lowest rounded-xl shadow-sm flex flex-col overflow-hidden group"
                <?= $model === 'MSZ-GC35NA' ? 'id="mitsubishi-aircon"' : '' ?>
                data-canriv-product="<?= esc_attr($model) ?>"
                data-category="<?= esc_attr($shop_categories[$model]) ?>"
                data-tech="<?= $shop_categories[$model] === 'split' ? 'inverter' : '' ?>">
                <div class="p-space-md flex flex-col gap-2 flex-grow">
                    <span class="font-label-code text-label-sm text-primary uppercase"><?= esc_html($type) ?></span>
                    <div class="w-full h-48 rounded-lg overflow-hidden my-2">
                        <img class="w-full h-full object-cover" src="<?= esc_url(plugins_url('assets/' . $file, dirname(__DIR__) . '/canriv-store.php')) ?>" width="<?= (int) $width ?>" height="<?= (int) $height ?>" alt="<?= esc_attr('Mitsubishi Electric ' . $model . ' in Canriv’s showroom') ?>" loading="lazy" decoding="async">
                    </div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface">Mitsubishi Electric <?= esc_html($model) ?></h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant"><?= esc_html($description) ?></p>
                    <p class="mt-auto font-title-md text-body-sm text-primary">Price and availability on request</p>
                </div>
                <div class="p-space-md pt-0">
                    <a class="canriv-button" href="<?= esc_url($quote_url) ?>" aria-label="<?= esc_attr('Request a quote for ' . $model) ?>">Request a quote <span aria-hidden="true">→</span></a>
                </div>
            </article>
        <?php endforeach; ?>
<?php elseif ($section === 'aircon-shop-photos') :
    $photos = [
        ['canriv-showroom.webp', 960, 720, 'Mitsubishi Electric showroom display'],
        ['canriv-mr-slim-display.webp', 720, 960, 'MSZ-GC Series indoor and outdoor units'],
        ['canriv-mr-slim-outdoor.webp', 960, 720, 'Mr. Slim outdoor inverter unit'],
        ['canriv-floor-and-cassette.webp', 640, 854, 'Floor-standing unit and ceiling cassette'],
    ];
    ?>
    <section class="canriv-company-info canriv-company-inner" aria-label="Mitsubishi Electric showroom photos">
        <details class="canriv-showroom-detail">
            <summary>See more Mr. Slim showroom photos</summary>
            <div>
                <?php foreach ($photos as [$file, $width, $height, $caption]) : ?>
                    <figure>
                        <img src="<?= esc_url(plugins_url('assets/' . $file, dirname(__DIR__) . '/canriv-store.php')) ?>" width="<?= (int) $width ?>" height="<?= (int) $height ?>" alt="<?= esc_attr($caption) ?>" loading="lazy" decoding="async">
                        <figcaption><?= esc_html($caption) ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </details>
    </section>
<?php elseif ($section === 'home-featured') :
    require __DIR__ . '/home-featured-cards.php';
elseif ($section === 'company-project-story') :
    require __DIR__ . '/company-chronicle.html';
elseif ($section === 'company-team') : ?>
    <section class="canriv-company-info canriv-company-inner" aria-labelledby="canriv-team-heading">
        <h2 id="canriv-team-heading">Meet the Canriv team</h2>
        <p>Canriv’s team helps customers choose HVAC equipment, plan installations, and coordinate mechanical work for residential, commercial, and industrial projects.</p>
        <a class="canriv-button" href="https://www.facebook.com/CanrivJCorp/photos/">See Canriv’s project photos on Facebook</a>
    </section>
<?php elseif ($section === 'company-quote') : ?>
    <section id="canriv-free-quote" class="canriv-company-info canriv-company-inner canriv-quote" aria-labelledby="canriv-quote-heading">
        <h2 id="canriv-quote-heading">Request a free quote</h2>
        <p>Tell us about your HVAC or elevator project, where the equipment will be installed, and whether you need supply, installation, or both.</p>
        <address>
            <a href="tel:+639454481254">0945 448 1254</a>
            <a href="tel:+63284639559">(02) 8463-9559</a>
            <a href="tel:+63286973247">(02) 8697-3247</a>
            <a href="mailto:sales@canriv.com">sales@canriv.com</a>
            <a href="mailto:info@canriv.com">info@canriv.com</a>
            <a href="https://www.facebook.com/CanrivJCorp">Canriv-J Corporation on Facebook</a>
        </address>
        <div class="canriv-location">
            <div class="canriv-location-map">
                <h3><span class="material-symbols-outlined" aria-hidden="true">location_on</span> Valenzuela office map</h3>
                <iframe class="canriv-contact-map" title="Google Map showing the Canriv office address in Valenzuela" src="https://maps.google.com/maps?q=65%20Overland%20Drive%2C%20Dalandanan%2C%20Valenzuela%20City%2C%20Philippines&amp;z=16&amp;output=embed" width="800" height="360" loading="lazy" allowfullscreen></iframe>
                <a class="canriv-button" href="https://www.google.com/maps/dir/?api=1&amp;destination=65%20Overland%20Drive%2C%20Sumilang%2C%20Dalandanan%2C%20Valenzuela%201443%2C%20Philippines">Get directions in Google Maps <span aria-hidden="true">↗</span></a>
            </div>
            <div class="canriv-location-guide">
                <h3>Plan your visit</h3>
                <dl>
                    <div><dt>Office address</dt><dd>65 Overland Drive, Sumilang, Dalandanan, Valenzuela, Philippines 1443</dd></div>
                    <div><dt>Before you go</dt><dd>Call <a href="tel:+639454481254">0945 448 1254</a> to confirm office hours or arrange a visit.</dd></div>
                    <div><dt>Metro Manila service areas</dt><dd>Muntinlupa, Quezon City, Makati, Valenzuela, and Manila.</dd></div>
                </dl>
            </div>
        </div>
    </section>
<?php elseif (in_array($section, ['vertical-transport', 'vertical-transport-summary'], true)) :
    $full = $section === 'vertical-transport';
    $lineup = canriv_equipment_catalog();
    $details = canriv_page_url('products-services') . '#mitsubishi-vertical-transport';
    ?>
    <section class="canriv-company-info canriv-company-inner canriv-vertical" <?= $full ? 'id="mitsubishi-vertical-transport"' : '' ?>>
        <h2>Mitsubishi Electric elevators, escalators &amp; moving walks</h2>
        <p>Explore equipment for homes, hospitals, commercial buildings, and freight operations. Contact Canriv for project specifications, availability, and a quote.</p>
        <?php if ($full) : ?>
            <?php
            $quote = canriv_page_url('contact') . '#canriv-free-quote';
            ?>
            <?php foreach ($lineup as $group => $products) : ?>
                <?php $icon = ['Elevators' => 'elevator', 'Escalators and moving walks' => 'escalator', 'Controls and upgrades' => 'settings'][ $group ]; ?>
                <h3><?= esc_html($group) ?></h3>
                <div class="canriv-equipment-cards">
                    <?php foreach ($products as $name => $item) : $detail_url = canriv_equipment_url($item['slug']); ?>
                        <article>
                            <header class="canriv-equipment-card-header">
                            <p class="canriv-equipment-category"><?= esc_html($group) ?></p>
                            <h4><a href="<?= esc_url($detail_url) ?>"><?= esc_html($name) ?></a></h4>
                            <span class="material-symbols-outlined canriv-equipment-icon" aria-hidden="true"><?= esc_html($icon) ?></span>
                            </header>
                            <a class="canriv-equipment-card-image" href="<?= esc_url($detail_url) ?>"><img src="<?= esc_url(plugins_url('assets/' . $item['file'], dirname(__DIR__) . '/canriv-store.php')) ?>" alt="<?= esc_attr($item['alt']) ?>" loading="lazy" decoding="async"></a>
                            <p><?= esc_html($item['description']) ?></p>
                            <dl class="canriv-equipment-details">
                                <div><dt>Manufacturer</dt><dd>Mitsubishi Electric</dd></div>
                                <div><dt>Application</dt><dd><?= esc_html($item['application']) ?></dd></div>
                            </dl>
                            <div class="canriv-equipment-action">
                                <a class="canriv-button" href="<?= esc_url($detail_url) ?>" aria-label="<?= esc_attr('View details for ' . $name) ?>">View details <span aria-hidden="true">→</span></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <a class="canriv-button" href="<?= esc_url($details) ?>">Explore the Mitsubishi Electric lineup</a>
        <?php endif; ?>
    </section>
<?php endif; ?>
