<?php
/**
 * Блок «Врачи» — карточки врачей отделения.
 */
$title = get_field('title');
$text = get_field('text');
$button = get_field('button');
$doctors = get_field('doctors');
?>

<section class="doctors">
    <div class="container">
        <div class="doctors__layout">
            <div class="doctors__info">
                <?php if ($title): ?>
                    <h2 class="doctors__title"><?php echo $title; ?></h2>
                <?php endif; ?>
                <?php if ($text): ?>
                    <p class="doctors__text"><?php echo $text; ?></p>
                <?php endif; ?>
                <?php if (is_array($button) && !empty($button['url'])): ?>
                    <a href="<?php echo esc_url($button['url']); ?>" class="doctors__button" <?php echo !empty($button['target']) ? 'target="' . esc_attr($button['target']) . '"' : ''; ?>><?php echo esc_html($button['title']); ?></a>
                <?php endif; ?>
            </div>
            <?php if ($doctors): ?>
                <div class="doctors__cards">
                    <?php foreach ($doctors as $doctor):
                        $link = $doctor['link'] ?? null;
                        $has_url = is_array($link) && !empty($link['url']);
                        $tag = $has_url ? 'a' : 'div';
                    ?>
                        <<?php echo $tag; ?> class="doctors__card"<?php if ($has_url): ?> href="<?php echo esc_url($link['url']); ?>"<?php endif; ?>>
                            <?php if (!empty($doctor['photo'])): ?>
                                <div class="doctors__card-photo">
                                    <?php echo wp_get_attachment_image($doctor['photo'], 'medium_large'); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($doctor['name'])): ?>
                                <h4 class="doctors__card-name"><?php echo esc_html($doctor['name']); ?></h4>
                            <?php endif; ?>
                            <?php if (!empty($doctor['role'])): ?>
                                <span class="doctors__card-role"><?php echo esc_html($doctor['role']); ?></span>
                            <?php endif; ?>
                        </<?php echo $tag; ?>>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
