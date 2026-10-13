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
            <?php // Кнопка открывает форму онлайн-записи; из ACF-ссылки берётся только подпись. ?>
            <?php sputnik_crm_booking_open_button(
                !empty($button['title']) ? $button['title'] : 'Записаться',
                'banner__link banner__link--red'
            ); ?>
        </div>
    </div>
</section>
