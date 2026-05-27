<?php
$title  = get_field('title');
$button = get_field('button');
$image  = get_field('image');
?>

<section class="banner">
    <div class="banner__bg">
        <?php if ($image): ?>
            <?php echo wp_get_attachment_image($image, 'full', false, ['class' => 'banner__bg-img']); ?>
        <?php endif; ?>
    </div>
    <div class="container">
        <div class="banner__content">
            <h2 class="banner__title"><?php echo $title; ?></h2>
            <?php if (is_array($button) && !empty($button['url'])): ?>
                <a href="<?php echo esc_url($button['url']); ?>" class="banner__link banner__link--red" <?php echo !empty($button['target']) ? 'target="' . esc_attr($button['target']) . '"' : ''; ?>>
                    <?php echo esc_html($button['title']); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
