<?php
// Нормализуем невидимые разделители строк (U+2028 / U+2029), которые попадают
// в текст при вставке из макета. Safari на мобильных рендерит их нулевой шириной —
// предлоги «склеиваются» с соседними словами. Заменяем на обычный пробел.
if (!function_exists('sputnik_normalize_separators')) {
    function sputnik_normalize_separators($value) {
        return str_replace(["\xE2\x80\xA8", "\xE2\x80\xA9"], ' ', (string) $value);
    }
}

$slides = get_field('slides');

// Обратная совместимость: если репитер пустой, но есть старые поля — превращаем их в один слайд.
if (empty($slides)) {
    $legacy_title  = get_field('title');
    $legacy_text   = get_field('text');
    $legacy_button = get_field('button');
    $legacy_image  = get_field('image');

    if ($legacy_title || $legacy_text || $legacy_image || !empty($legacy_button)) {
        $slides = [[
            'title'  => $legacy_title,
            'text'   => $legacy_text,
            'button' => $legacy_button,
            'image'  => $legacy_image,
        ]];
    }
}

// Баннер без картинки показывать нечем — выкидываем, чтобы не сбить счётчик слайдов.
$slides = array_values(array_filter((array) $slides, function ($slide) {
    return ($slide['type'] ?? '') !== 'banner'
        || !empty($slide['banner_image'])
        || !empty($slide['banner_image_mobile']);
}));

if (empty($slides)) {
    return;
}

$multi = count($slides) > 1;
?>

<section class="hero">
    <div class="container">
        <div class="hero__wrap<?php echo $multi ? ' hero__wrap--multi' : ''; ?>">
            <div class="hero__slider swiper<?php echo $multi ? ' hero__slider--multi' : ''; ?>">
                <div class="swiper-wrapper">
                    <?php foreach ($slides as $slide):
                        // Слайд-баннер: только картинка, без текста и кнопки.
                        if (($slide['type'] ?? '') === 'banner'):
                            $banner        = $slide['banner_image'] ?? 0;
                            $banner_mobile = $slide['banner_image_mobile'] ?? 0;
                            $banner_link   = $slide['banner_link'] ?? [];
                            $has_link      = is_array($banner_link) && !empty($banner_link['url']);
                            $tag           = $has_link ? 'a' : 'div';
                            ?>
                            <div class="swiper-slide">
                                <<?php echo $tag; ?> class="hero__banner hero__banner--image"<?php if ($has_link): ?> href="<?php echo esc_url($banner_link['url']); ?>"<?php echo !empty($banner_link['target']) ? ' target="' . esc_attr($banner_link['target']) . '"' : ''; ?><?php echo !empty($banner_link['title']) ? ' aria-label="' . esc_attr($banner_link['title']) . '"' : ''; ?><?php endif; ?>>
                                    <picture>
                                        <?php if ($banner && $banner_mobile): ?>
                                            <source media="(max-width: 1024px)" srcset="<?php echo esc_url(wp_get_attachment_image_url($banner_mobile, 'full')); ?>">
                                        <?php endif; ?>
                                        <?php echo wp_get_attachment_image($banner ?: $banner_mobile, 'full', false, ['class' => 'hero__banner-img']); ?>
                                    </picture>
                                </<?php echo $tag; ?>>
                            </div>
                            <?php
                            continue;
                        endif;

                        $title  = sputnik_normalize_separators($slide['title'] ?? '');
                        $text   = sputnik_normalize_separators($slide['text']  ?? '');
                        $button = $slide['button'] ?? [];
                        $image  = $slide['image']  ?? 0;
                        ?>
                        <div class="swiper-slide">
                            <div class="hero__banner">
                                <div class="hero__content">
                                    <div class="hero__info">
                                        <h1 class="hero__title"><?php echo $title; ?></h1>
                                        <p class="hero__desc"><?php echo $text; ?></p>
                                    </div>
                                    <?php if (!empty($slide['button_booking'])): ?>
                                        <?php // Слайды клонирует Swiper (loop), поэтому кнопка — прокси на скрытый триггер в подвале. ?>
                                        <?php sputnik_crm_booking_button(
                                            is_array($button) && !empty($button['title']) ? $button['title'] : 'Записаться на консультацию',
                                            '',
                                            'hero__button hero__button--red'
                                        ); ?>
                                    <?php elseif (is_array($button) && !empty($button['url'])): ?>
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
                    <?php endforeach; ?>
                </div>

                <?php if ($multi): ?>
                    <div class="hero__pagination swiper-pagination"></div>
                <?php endif; ?>
            </div>

            <?php if ($multi): ?>
                <button class="hero__nav hero__nav--prev" type="button" aria-label="Назад">
                    <svg width="8" height="9" viewBox="0 0 8 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 1L1.5 4.5L6 8" stroke="#1d3658" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button class="hero__nav hero__nav--next" type="button" aria-label="Вперёд">
                    <svg width="8" height="9" viewBox="0 0 8 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 1L6.5 4.5L2 8" stroke="#1d3658" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            <?php endif; ?>
        </div>
    </div>
</section>
