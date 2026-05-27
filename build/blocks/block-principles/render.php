<?php
$title = get_field('title');
$items = get_field('items');
?>

<section class="principles">
    <div class="container">
        <h2 class="principles__title"><?php echo $title; ?></h2>
        <?php if ($items): ?>
            <div class="principles__items">
                <?php foreach ($items as $item): ?>
                    <div class="principles__item">
                        <h4 class="principles__item-title"><?php echo $item['item_title']; ?></h4>
                        <p class="principles__item-text"><?php echo $item['item_text']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
