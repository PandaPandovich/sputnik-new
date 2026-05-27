<?php
$title  = get_field('title');
$slides = get_field('slides');
?>

<section class="works">
    <div class="container">
        <h2 class="works__title"><?php echo $title; ?></h2>
        <?php if ($slides): ?>
            <div class="works__slider">
                <div class="swiper-wrapper">
                    <?php foreach ($slides as $slide): ?>
                        <div class="swiper-slide">
                            <div class="works__card">
                                <?php if (!empty($slide['image'])): ?>
                                    <?php echo wp_get_attachment_image($slide['image'], 'large', false, ['class' => 'works__card-img']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
