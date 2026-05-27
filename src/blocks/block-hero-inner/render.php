<?php
/**
 * Блок Hero Inner — герой для внутренних страниц услуг.
 */
$title = get_field('title');
$desc = get_field('desc');
$button = get_field('button');
$image = get_field('image');
?>

<section class="hero-inner">
    <div class="container">
        <div class="hero-inner__wrapper">
            <div class="hero-inner__card">
                <div class="hero-inner__content">
                    <?php if ($title): ?>
                        <h1 class="hero-inner__title"><?php echo $title; ?></h1>
                    <?php endif; ?>
                    <?php if ($desc): ?>
                        <p class="hero-inner__desc"><?php echo $desc; ?></p>
                    <?php endif; ?>
                </div>
                <?php if (is_array($button) && !empty($button['url'])): ?>
                    <a href="<?php echo esc_url($button['url']); ?>" class="hero-inner__button" <?php echo !empty($button['target']) ? 'target="' . esc_attr($button['target']) . '"' : ''; ?>><?php echo esc_html($button['title']); ?></a>
                <?php endif; ?>
            </div>
            <?php if ($image): ?>
                <div class="hero-inner__image">
                    <?php echo wp_get_attachment_image($image, 'full'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
