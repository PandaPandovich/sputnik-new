<?php
$title  = get_field('title');
$image  = get_field('image');
$button = get_field('button');
$items  = get_field('items');
?>

<section class="why">
    <div class="container">
        <h2 class="why__title"><?php echo $title; ?></h2>
        <div class="why__grid">
            <div class="why__photo">
                <?php if ($image): ?>
                    <?php echo wp_get_attachment_image($image, 'full', false, ['class' => 'why__image']); ?>
                <?php endif; ?>
                <?php if (is_array($button) && !empty($button['url'])): ?>
                    <a class="why__link" href="<?php echo esc_url($button['url']); ?>" <?php echo !empty($button['target']) ? 'target="' . esc_attr($button['target']) . '"' : ''; ?>>
                        <?php echo esc_html($button['title']); ?>
                    </a>
                <?php endif; ?>
            </div>
            <?php if ($items): ?>
                <div class="why__items">
                    <?php foreach ($items as $item): ?>
                        <div class="why__item">
                            <div class="why__item-icon">
                                <?php if (!empty($item['icon'])): ?>
                                    <?php echo wp_get_attachment_image($item['icon'], 'full', false, ['class' => 'why__item-icon-img']); ?>
                                <?php endif; ?>
                            </div>
                            <div class="why__item-content">
                                <h4 class="why__item-title"><?php echo $item['item_title']; ?></h4>
                                <p class="why__item-text"><?php echo $item['item_text']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
