<?php
/**
 * Блок «Профиль врача» — карточка врача с детальной информацией.
 */

$photo        = get_field('photo');
$name         = get_field('name');
$role         = get_field('role');
$achievements = get_field('achievements');
$button       = get_field('button');
?>

<section class="doctor-profile">
    <div class="container">
         <?php if ($photo) : ?>
        <div class="doctor-profile__photo">
            <img src="<?php echo esc_url($photo['url']); ?>"
                alt="<?php echo esc_attr($photo['alt'] ?: $name); ?>">
        </div>
    <?php endif; ?>
        <div class="doctor-profile__layout">
            <div class="doctor-profile__card">
                <?php if ($name) : ?>
                    <h3 class="doctor-profile__name"><?php echo esc_html($name); ?></h3>
                <?php endif; ?>

                <?php if ($role) : ?>
                    <span class="doctor-profile__role"><?php echo esc_html($role); ?></span>
                <?php endif; ?>

                <?php if ($achievements) : ?>
                    <ul class="doctor-profile__list">
                        <?php foreach ($achievements as $item) : ?>
                            <li><?php echo esc_html($item['text']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ($button) : ?>
                    <a href="<?php echo esc_url($button['url']); ?>"
                       class="doctor-profile__button"
                       <?php echo $button['target'] ? 'target="' . esc_attr($button['target']) . '"' : ''; ?>>
                        <?php echo esc_html($button['title']); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
