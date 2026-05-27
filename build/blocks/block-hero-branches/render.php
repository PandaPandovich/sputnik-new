<?php
$title  = get_field('title');
$text   = get_field('text');
$button = get_field('button');
$image  = get_field('image');
?>

<section class="branches-hero">
    <div class="container">
        <div class="branches-hero__banner">
            <div class="branches-hero__content">
                <div class="branches-hero__info">
                    <h1 class="branches-hero__title"><?php echo $title; ?></h1>
                    <p class="branches-hero__desc"><?php echo $text; ?></p>
                </div>
                <?php if (is_array($button) && !empty($button['url'])): ?>
                    <a href="<?php echo esc_url($button['url']); ?>" class="branches-hero__button branches-hero__button--red" <?php echo !empty($button['target']) ? 'target="' . esc_attr($button['target']) . '"' : ''; ?>>
                        <?php echo esc_html($button['title']); ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="branches-hero__image">
                <?php if ($image): ?>
                    <?php echo wp_get_attachment_image($image, 'full'); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
