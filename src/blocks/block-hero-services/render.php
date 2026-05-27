<?php
$title  = get_field('title');
$text   = get_field('text');
$button = get_field('button');
$image  = get_field('image');
?>

<section class="services-hero">
    <div class="container">
        <div class="services-hero__banner">
            <div class="services-hero__content">
                <div class="services-hero__info">
                    <h1 class="services-hero__title"><?php echo $title; ?></h1>
                    <p class="services-hero__desc"><?php echo $text; ?></p>
                </div>
                <?php if (is_array($button) && !empty($button['url'])): ?>
                    <a href="<?php echo esc_url($button['url']); ?>" class="services-hero__button services-hero__button--red" <?php echo !empty($button['target']) ? 'target="' . esc_attr($button['target']) . '"' : ''; ?>>
                        <?php echo esc_html($button['title']); ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="services-hero__image">
                <?php if ($image): ?>
                    <?php echo wp_get_attachment_image($image, 'full'); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
