<?php
$title  = get_field('title');
$slides = get_field('slides');

if (is_array($slides) && count($slides) > 1) {
    shuffle($slides);
}
?>

<section class="works">
    <div class="container">
        <div class="works__header">
            <h2 class="works__title"><?php echo $title; ?></h2>
            <div class="works__nav">
                <button class="works__nav-btn works__nav-prev" type="button" aria-label="Назад">
                    <svg width="8" height="9" viewBox="0 0 8 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 1L1.5 4.5L6 8" stroke="#1d3658" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button class="works__nav-btn works__nav-next" type="button" aria-label="Вперёд">
                    <svg width="8" height="9" viewBox="0 0 8 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 1L6.5 4.5L2 8" stroke="#1d3658" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>
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
