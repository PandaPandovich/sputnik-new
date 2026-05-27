<?php
/**
 * Шаблон одиночной записи (статья).
 *
 * @package sputnik-plus
 */

get_header();

while ( have_posts() ) : the_post();
?>

<article class="single-post">
	<!-- Шапка статьи -->
	<div class="single-post__header">
		<div class="container">
			<h1 class="single-post__title"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="single-post__subtitle"><?php echo get_the_excerpt(); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<!-- Обложка -->
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="single-post__hero">
			<div class="container">
				<figure class="single-post__hero-image">
					<?php the_post_thumbnail( 'full', [ 'class' => 'single-post__hero-img' ] ); ?>
				</figure>
			</div>
		</div>
	<?php endif; ?>

	<!-- Контент статьи -->
	<div class="single-post__body">
		<div class="container">
			<div class="single-post__content">
				<?php the_content(); ?>
			</div>
		</div>
	</div>

	<!-- Похожие статьи -->
	<?php
	$related = new WP_Query( [
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post__not_in'   => [ get_the_ID() ],
		'post_status'    => 'publish',
		'orderby'        => 'rand',
	] );

	if ( $related->have_posts() ) :
	?>
		<section class="single-post__related">
			<div class="container">
				<h2 class="single-post__related-title">Читайте также</h2>
				<div class="single-post__related-grid">
					<?php while ( $related->have_posts() ) : $related->the_post(); ?>
						<a href="<?php the_permalink(); ?>" class="blog-archive__card blog-archive__card--link">
							<div class="blog-archive__card-image">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'medium_large', [ 'class' => 'blog-archive__card-img' ] ); ?>
								<?php endif; ?>
							</div>
							<div class="blog-archive__card-content">
								<span class="blog-archive__card-date"><?php echo get_the_date( 'j F Y' ); ?></span>
								<h3 class="blog-archive__card-heading"><?php the_title(); ?></h3>
								<p class="blog-archive__card-text"><?php echo get_the_excerpt(); ?></p>
							</div>
						</a>
					<?php endwhile; wp_reset_postdata(); ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
</article>

<?php
endwhile;
get_footer();
?>
