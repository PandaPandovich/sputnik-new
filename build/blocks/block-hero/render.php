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
                <a href="#" class="hero__button hero__button--red">Записаться на консультацию</a>
            </div>
            <div class="hero__image">
                <?php echo wp_get_attachment_image($image, 'full'); ?>
            </div>
        </div>
    </div>
</section>