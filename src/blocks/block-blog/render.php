<?php
$title             = get_field('title');
$subtitle          = get_field('subtitle');
$posts_count       = get_field('posts_count') ?: 6;
$read_more_text    = get_field('read_more_text') ?: 'Читать статью';
$all_articles_link = get_field('all_articles_link');
$has_all_link      = is_array($all_articles_link) && !empty($all_articles_link['url']);

$blog_query = new WP_Query([
    'post_type'      => 'post',
    'posts_per_page' => $posts_count,
    'post_status'    => 'publish',
    'orderby'        => 'rand',
]);
?>

<section class="blog">
    <div class="container">
        <div class="blog__header">
            <div class="blog__heading">
                <h2 class="blog__title"><?php echo $title; ?></h2>
                <?php if ($subtitle): ?>
                    <p><?php echo $subtitle; ?></p>
                <?php endif; ?>
            </div>
            <?php if ($has_all_link): ?>
                <a class="blog__all-link"
                   href="<?php echo esc_url($all_articles_link['url']); ?>"
                   <?php if (!empty($all_articles_link['target'])): ?>target="<?php echo esc_attr($all_articles_link['target']); ?>" rel="noopener"<?php endif; ?>>
                    <?php echo esc_html(!empty($all_articles_link['title']) ? $all_articles_link['title'] : 'Все статьи'); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php if ($blog_query->have_posts()): ?>
            <div class="blog__slider">
                <div class="swiper-wrapper">
                    <?php while ($blog_query->have_posts()): $blog_query->the_post(); ?>
                        <div class="swiper-slide">
                            <div class="blog__card">
                                <div class="blog__card-image">
                                    <?php if (has_post_thumbnail()): ?>
                                        <?php the_post_thumbnail('medium', ['class' => 'blog__card-img']); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="blog__card-content">
                                    <span class="blog__card-date"><?php echo get_the_date(); ?></span>
                                    <h4 class="blog__card-title"><?php the_title(); ?></h4>
                                    <p class="blog__card-text"><?php echo get_the_excerpt(); ?></p>
                                    <a href="<?php the_permalink(); ?>" class="blog__card-link blog__card-link--outline"><?php echo esc_html($read_more_text); ?></a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
