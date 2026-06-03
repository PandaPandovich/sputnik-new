<?php
/**
 * Блок «Hero О нас» — приветственный баннер страницы о клинике.
 */

$title = get_field('title') ?: 'Привет, <br>мы Спутник';
$image = get_field('image');
?>

<section class="about-hero">
    <div class="container">
        <h1 class="about-hero__title"><?php echo $title; ?></h1>
        <?php if ($image) : ?>
            <div class="about-hero__image">
                <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
            </div>
        <?php endif; ?>
    </div>
</section>
