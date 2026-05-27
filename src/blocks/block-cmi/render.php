<?php
$title = get_field('title');
$logos = get_field('logos');
?>

<section class="media">
    <div class="container">
        <h2 class="media__title"><?php echo $title; ?></h2>
        <?php if ($logos): ?>
            <div class="media__logos">
                <?php foreach ($logos as $item): ?>
                    <a <?php echo !empty($item['link']) ? 'href="' . esc_url($item['link']) . '" target="_blank" rel="noopener"' : ''; ?> class="media__logo">
                        <?php if (!empty($item['logo'])): ?>
                            <?php echo wp_get_attachment_image($item['logo'], 'medium', false, ['class' => 'media__logo-image']); ?>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
