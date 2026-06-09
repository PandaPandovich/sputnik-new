<?php
$title = get_field('title');
$text = get_field('text');
$desc = get_field('text');
$button = get_field('button');
$image = get_field('image');
?>

<section class="hero">
    <div class="container">
        <div class="hero__banner">
            <div class="hero__content">
                <div class="hero__info">
                    <h1 class="hero__title"><?php echo $title; ?></h1>
                    <p class="hero__desc"><?php echo $desc; ?></p>
                </div>
                <?php if (is_array($button) && !empty($button['url'])): ?>
                    <a href="<?php echo esc_url($button['url']); ?>" class="hero__button hero__button--red" <?php echo !empty($button['target']) ? 'target="' . esc_attr($button['target']) . '"' : ''; ?>>
                        <?php echo esc_html(!empty($button['title']) ? $button['title'] : 'Записаться на консультацию'); ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="hero__image">
                <?php echo wp_get_attachment_image($image, 'full'); ?>
            </div>
        </div>
    </div>
</section>