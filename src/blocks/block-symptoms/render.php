<?php
/**
 * Блок «Симптомы» — когда нужно показать питомца ветеринарному онкологу.
 */
$title = get_field('title');
$desc = get_field('desc');
$columns = get_field('columns');
$image = get_field('image');
?>

<section class="symptoms">
    <div class="container">
        <div class="symptoms__layout">
            <div class="symptoms__content">
                <?php if ($title): ?>
                    <h2 class="symptoms__title"><?php echo $title; ?></h2>
                <?php endif; ?>
                <?php if ($desc): ?>
                    <div class="symptoms__desc"><?php echo $desc; ?></div>
                <?php endif; ?>

                <?php if ($columns): ?>
                    <div class="symptoms__columns">
                        <?php foreach ($columns as $column): ?>
                            <div class="symptoms__column symptoms__column--<?php echo esc_attr($column['color']); ?>">
                                <?php if (!empty($column['column_title'])): ?>
                                    <h4 class="symptoms__column-title"><?php echo esc_html($column['column_title']); ?></h4>
                                <?php endif; ?>
                                <?php if (!empty($column['items'])): ?>
                                    <ul class="symptoms__list">
                                        <?php foreach ($column['items'] as $item): ?>
                                            <li><?php echo esc_html($item['text']); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($image): ?>
                <div class="symptoms__image">
                    <?php echo wp_get_attachment_image($image, 'full'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
