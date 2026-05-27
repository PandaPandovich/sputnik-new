<?php
$title  = get_field('title');
$text   = get_field('text');
$button = get_field('button');
$image  = get_field('image');
?>

<section class="about">
    <div class="container">
        <div class="about__row">
            <div class="about__image">
                <?php if ($image): ?>
                    <?php echo wp_get_attachment_image($image, 'full', false, ['class' => 'about__img']); ?>
                <?php endif; ?>
            </div>
            <div class="about__content">
                <h3 class="about__title"><?php echo $title; ?></h3>
                <div class="about__text">
                    <p><?php echo $text; ?></p>
                </div>
                <?php if (is_array($button) && !empty($button['url'])): ?>
                    <a href="<?php echo esc_url($button['url']); ?>" class="about__button" <?php echo !empty($button['target']) ? 'target="' . esc_attr($button['target']) . '"' : ''; ?>>
                        <?php echo esc_html($button['title']); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
