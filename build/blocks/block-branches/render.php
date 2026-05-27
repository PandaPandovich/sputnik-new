<?php
$title = get_field('title');

$branches_query = new WP_Query([
    'post_type' => 'branch',
    'posts_per_page' => -1,
    'orderby' => 'menu_order title',
    'order' => 'ASC',
    'post_status' => 'publish',
]);
?>

<section class="branches">
    <div class="container">
        <h2 class="branches__title"><?php echo $title; ?></h2>
        <?php if ($branches_query->have_posts()): ?>
            <div class="branches__grid">
                <?php while ($branches_query->have_posts()):
                    $branches_query->the_post(); ?>
                    <a href="<?php the_permalink(); ?>" class="branches__item">
                        <?php if (has_post_thumbnail()): ?>
                            <div class="branches__item-image">
                                <?php the_post_thumbnail('medium', ['class' => 'branches__item-img']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="branches__item-info">
                            <h4 class="branches__item-title"><?php the_title(); ?></h4>
                            <div class="branches__item-squad">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0.707031 1H14.707M14.707 1V15M14.707 1L0.707031 15" stroke="white"
                                        stroke-width="2" />
                                </svg>
                            </div>
                        </div>
                    </a>
                <?php endwhile;
                wp_reset_postdata(); ?>
            </div>
        <?php endif; ?>
    </div>
</section>