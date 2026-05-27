<?php
/**
 * Шаблон страницы 404
 */
get_header();
?>

<section class="page-404">
    <div class="container">
        <div class="page-404__content">
            <span class="page-404__code">404</span>
            <h1 class="page-404__title">Страница не найдена</h1>
            <p class="page-404__text">К сожалению, запрашиваемая страница не существует или была перемещена. Попробуйте вернуться на главную.</p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="page-404__button">
                Вернуться на главную
            </a>
        </div>
    </div>
</section>

<?php get_footer(); ?>
