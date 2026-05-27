<?php
/**
 * Template Name: Владельцам (Блог)
 * Description: Страница со списком записей блога с пагинацией.
 *
 * @package sputnik-plus
 */

get_header();

$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

$blog_query = new WP_Query( [
	'post_type'      => 'post',
	'posts_per_page' => 6,
	'paged'          => $paged,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
] );
?>

<section class="blog-archive">
	<div class="container">
		<h1 class="blog-archive__title">Все что нужно о вашем питомце</h1>

		<?php if ( $blog_query->have_posts() ) : ?>
			<div class="blog-archive__grid">
				<?php while ( $blog_query->have_posts() ) : $blog_query->the_post(); ?>
					<article class="blog-archive__card">
						<div class="blog-archive__card-image">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'medium_large', [ 'class' => 'blog-archive__card-img' ] ); ?>
							<?php endif; ?>
						</div>
						<div class="blog-archive__card-content">
							<span class="blog-archive__card-date"><?php echo get_the_date( 'j F Y' ); ?></span>
							<h2 class="blog-archive__card-heading"><?php the_title(); ?></h2>
							<p class="blog-archive__card-text"><?php echo get_the_excerpt(); ?></p>
							<a href="<?php the_permalink(); ?>" class="blog-archive__card-link">Читать статью</a>
						</div>
					</article>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>

			<?php
			$pagination = paginate_links( [
				'total'     => $blog_query->max_num_pages,
				'current'   => $paged,
				'prev_text' => '&lsaquo;',
				'next_text' => '&rsaquo;',
				'type'      => 'list',
			] );

			if ( $pagination ) :
			?>
				<nav class="blog-archive__pagination">
					<?php echo $pagination; ?>
				</nav>
			<?php endif; ?>

		<?php else : ?>
			<p class="blog-archive__empty">Записей пока нет.</p>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
