<?php
defined('ABSPATH') || exit;
$image_url = plugins_url('assets/' . $item['file'], dirname(__DIR__) . '/canriv-store.php');
$services_url = canriv_page_url('products-services') . '#mitsubishi-vertical-transport';
$quote_url = add_query_arg(['equipment' => $item['slug']], canriv_page_url('contact')) . '#canriv-free-quote';
?>
<main class="canriv-equipment-detail" id="content">
    <nav class="canriv-equipment-breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= esc_url(home_url('/')) ?>"><span class="material-symbols-outlined" aria-hidden="true">home</span> Home</a>
        <span aria-hidden="true">/</span><a href="<?= esc_url(canriv_page_url('products-services')) ?>">Products &amp; Services</a>
        <span aria-hidden="true">/</span><span aria-current="page"><?= esc_html($item['name']) ?></span>
    </nav>

    <section class="canriv-equipment-hero" aria-labelledby="canriv-equipment-title">
        <div class="canriv-equipment-gallery">
            <div class="canriv-equipment-photo">
                <div class="canriv-equipment-photo-brand"><span class="material-symbols-outlined" aria-hidden="true">ac_unit</span> MITSUBISHI ELECTRIC</div>
                <img src="<?= esc_url($image_url) ?>" alt="<?= esc_attr($item['alt']) ?>" fetchpriority="high">
            </div>
            <section class="canriv-equipment-overview" aria-labelledby="canriv-equipment-overview-title">
                <h2 id="canriv-equipment-overview-title">Equipment overview</h2>
                <dl>
                    <div><dt>Manufacturer</dt><dd>Mitsubishi Electric</dd></div>
                    <div><dt>Category</dt><dd><?= esc_html($item['group']) ?></dd></div>
                    <div><dt>Availability</dt><dd>Made-to-order / Project quote</dd></div>
                </dl>
            </section>
        </div>

        <div class="canriv-equipment-summary">
            <p class="canriv-equipment-kicker"><span>ME</span> · <?= esc_html(strtoupper($item['group'])) ?></p>
            <h1 id="canriv-equipment-title">Mitsubishi Electric <?= esc_html($item['name']) ?></h1>
            <p class="canriv-equipment-description"><?= esc_html($item['description']) ?></p>

            <section class="canriv-project-quote" aria-label="Quotation details">
                <span class="material-symbols-outlined" aria-hidden="true">request_quote</span>
                <div><h2>Project-Based Quotation</h2><p>Pricing and equipment selection depend on your building plan, application, and installation requirements.</p></div>
            </section>

            <h2 class="canriv-equipment-highlights-title">System highlights</h2>
            <ul class="canriv-equipment-highlights">
                <?php foreach ($item['highlights'] as $highlight) : ?>
                    <li><span class="material-symbols-outlined" aria-hidden="true">check_circle</span><?= esc_html($highlight) ?></li>
                <?php endforeach; ?>
            </ul>

            <a class="canriv-equipment-quote-button" href="<?= esc_url($quote_url) ?>"><span class="material-symbols-outlined" aria-hidden="true">request_quote</span> Request an Official Quote <span aria-hidden="true">→</span></a>

            <section class="canriv-project-details" aria-label="Information for a project quote">
                <h2>Project details to prepare</h2>
                <div><span>Application</span><strong><?= esc_html($item['application']) ?></strong></div>
                <div><span>Site information</span><strong>Building plans and location</strong></div>
            </section>
        </div>
    </section>

    <section class="canriv-related-equipment" aria-labelledby="canriv-related-title">
        <div class="canriv-related-heading"><div><p>PLAN YOUR SYSTEM</p><h2 id="canriv-related-title">Related Equipment &amp; Systems</h2></div><a href="<?= esc_url($services_url) ?>">View full catalog <span aria-hidden="true">+</span></a></div>
        <div class="canriv-related-grid">
            <?php foreach ($related as $related_item) : ?>
                <article class="canriv-related-equipment-card">
                    <a class="canriv-related-image" href="<?= esc_url(canriv_equipment_url($related_item['slug'])) ?>"><img src="<?= esc_url(plugins_url('assets/' . $related_item['file'], dirname(__DIR__) . '/canriv-store.php')) ?>" alt="<?= esc_attr($related_item['alt']) ?>" loading="lazy" decoding="async"></a>
                    <p class="canriv-related-category"><?= esc_html($related_item['group']) ?></p>
                    <h3><a href="<?= esc_url(canriv_equipment_url($related_item['slug'])) ?>"><?= esc_html($related_item['name']) ?></a></h3>
                    <p class="canriv-related-description"><?= esc_html($related_item['description']) ?></p>
                    <a class="canriv-related-link" href="<?= esc_url(canriv_equipment_url($related_item['slug'])) ?>">View details <span aria-hidden="true">→</span></a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
