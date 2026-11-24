<?php
defined('ABSPATH') || exit;
$equipment = canriv_equipment_find(get_post_field('post_name', get_queried_object_id()));
if (!$equipment) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    include get_404_template();
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?= esc_attr(get_bloginfo('charset')) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('canriv-equipment-detail-page'); ?>>
<?php wp_body_open(); ?>
<?= canriv_equipment_render_detail($equipment['slug']) ?>
<?php wp_footer(); ?>
</body>
</html>
