<?php
/**
 * Блок «Преимущества» — сетка карточек с иконками.
 */
$title = get_field('title');
$items = get_field('items');
?>

<section class="advantages">
    <div class="container">
        <?php if ($title): ?>
            <h2 class="advantages__title"><?php echo $title; ?></h2>
        <?php endif; ?>

        <?php if ($items): ?>
            <div class="advantages__grid">
                <?php foreach ($items as $item): ?>
                    <div class="advantages__item<?php echo !empty($item['wide']) ? ' advantages__item--wide' : ''; ?>">
                        <?php if (!empty($item['icon'])): ?>
                            <div class="advantages__item-icon">
                                <?php echo wp_get_attachment_image($item['icon'], 'full'); ?>
                            </div>
                        <?php endif; ?>
                        <div class="advantages__item-content">
                            <?php if (!empty($item['item_title'])): ?>
                                <h4 class="advantages__item-title"><?php echo $item['item_title']; ?></h4>
                            <?php endif; ?>
                            <?php if (!empty($item['item_text'])): ?>
                                <p class="advantages__item-text"><?php echo $item['item_text']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
