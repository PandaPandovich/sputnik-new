<?php
/**
 * Template Name: Текстовая страница
 * Description: Шаблон для текстовых страниц (политика конфиденциальности, оферта и т.д.)
 */
get_header(); ?>

<main class="page-content">
    <div class="container">
        <?php while (have_posts()): the_post(); ?>
            <h1 class="page-content__title"><?php the_title(); ?></h1>
            <div class="page-content__body">
                <?php the_content(); ?>
            </div>
        <?php endwhile; ?>
    </div>
</main>

<?php get_footer(); ?>
