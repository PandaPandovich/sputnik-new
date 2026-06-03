<?php
/**
 * Блок Hero Contacts — герой для страницы контактов.
 */
$title = get_field('title');
$phone_label = get_field('phone_label');
$phone = get_field('phone');
$email_label = get_field('email_label');
$email = get_field('email');
$button = get_field('button');
$image = get_field('image');
$image_mobile = get_field('image_mobile');

$bg_desktop = $image ? wp_get_attachment_image_url($image, 'full') : '';
$bg_mobile = $image_mobile ? wp_get_attachment_image_url($image_mobile, 'full') : '';
?>

<section class="hero-contacts">
    <div class="container">
        <div class="hero-contacts__wrapper"
            <?php if ($bg_desktop): ?>
                style="background-image: url('<?php echo esc_url($bg_desktop); ?>')"
            <?php endif; ?>
            <?php if ($bg_mobile): ?>
                data-mobile-bg="<?php echo esc_url($bg_mobile); ?>"
            <?php endif; ?>
        >
            <div class="hero-contacts__content">
                <?php if ($title): ?>
                    <h1 class="hero-contacts__title"><?php echo $title; ?></h1>
                <?php endif; ?>
                <?php if ($phone): ?>
                    <div class="hero-contacts__phone-block">
                        <?php if ($phone_label): ?>
                            <span class="hero-contacts__label"><?php echo esc_html($phone_label); ?></span>
                        <?php endif; ?>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^+\d]/', '', $phone)); ?>" class="hero-contacts__phone"><?php echo esc_html($phone); ?></a>
                    </div>
                <?php endif; ?>
                <?php if ($email): ?>
                    <div class="hero-contacts__phone-block">
                        <?php if ($email_label): ?>
                            <span class="hero-contacts__label"><?php echo esc_html($email_label); ?></span>
                        <?php endif; ?>
                        <a href="mailto:<?php echo esc_attr(antispambot($email)); ?>" class="hero-contacts__phone"><?php echo esc_html(antispambot($email)); ?></a>
                    </div>
                <?php endif; ?>
                <?php if (is_array($button) && !empty($button['url'])): ?>
                    <a href="<?php echo esc_url($button['url']); ?>" class="hero-contacts__button" <?php echo !empty($button['target']) ? 'target="' . esc_attr($button['target']) . '"' : ''; ?>><?php echo esc_html($button['title']); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if ($bg_mobile): ?>
        <style>
            @media (max-width: 768px) {
                .hero-contacts__wrapper {
                    background-image: url('<?php echo esc_url($bg_mobile); ?>') !important;
                }
            }
        </style>
    <?php endif; ?>
</section>
