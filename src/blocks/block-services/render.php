<?php
$title = get_field('title');
$text = get_field('text');
$desc = get_field('text');
$button = get_field('button');
$image = get_field('image');
?>

<section class="services">
    <div class="container">
        <div class="services__header">
            <h2 class="services__title">Мы оказываем полный спектр услуг</h2>
            <div class="services__nav">
                <button class="services__nav-btn services__nav-prev" type="button" aria-label="Назад">
                    <svg width="8" height="9" viewBox="0 0 8 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 1L1.5 4.5L6 8" stroke="#1d3658" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button class="services__nav-btn services__nav-next" type="button" aria-label="Вперёд">
                    <svg width="8" height="9" viewBox="0 0 8 9" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 1L6.5 4.5L2 8" stroke="#1d3658" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>
        <?php
        $args = array(
            'post_type' => 'branch',
            'posts_per_page' => -1,
            'orderby' => 'menu_order title',
            'order' => 'ASC',
            'post_status' => 'publish',
        );

        $services_query = new WP_Query($args);

        if ($services_query->have_posts()): ?>
            <div class="services__items">
                <div class="swiper-wrapper">
                    <?php while ($services_query->have_posts()):
                        $services_query->the_post();
                        ?>
                        <a href="<?php the_permalink(); ?>" class="services__item swiper-slide">
                            <div class="services__item-header">
                                <h4 class="services__item-title">
                                    <?php the_title(); ?>
                                </h4>
                                <div class="services__item-arrow">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0.707031 1H14.707M14.707 1V15M14.707 1L0.707031 15" stroke="white"
                                            stroke-width="2" />
                                    </svg>
                                </div>
                            </div>
                            <div class="services__item-image">
                                <?php
                                if (has_post_thumbnail()) {
                                    the_post_thumbnail('medium', array(
                                        'class' => 'services__item-img',
                                    ));
                                }
                                ?>
                            </div>
                        </a>
                        <?php
                    endwhile;

                    wp_reset_postdata(); ?>
                </div>
            </div>
        <?php endif;
        ?>
    </div>
</section>