<?php
/**
 * Шаблон результатов поиска.
 * Ищет по статьям, отделениям, врачам (опции) и услугам (опции).
 */
get_header();

$query   = get_search_query();
$results = function_exists( 'sputnik_collect_search_results' ) ? sputnik_collect_search_results( $query ) : array();
$total   = count( $results );

$counts = array( 'all' => $total, 'service' => 0, 'article' => 0, 'branch' => 0, 'doctor' => 0, 'page' => 0 );
foreach ( $results as $r ) {
	if ( isset( $counts[ $r['type'] ] ) ) {
		$counts[ $r['type'] ]++;
	}
}

$filters = array(
	'all'     => 'Все',
	'service' => 'Услуги',
	'branch'  => 'Отделения',
	'article' => 'Статьи',
	'doctor'  => 'Врачи',
	'page'    => 'Страницы',
);

$phone       = sputnik_search_phone_link();
$results_word = sputnik_plural( $total, 'результат', 'результата', 'результатов' );
?>

<section class="search">
	<div class="container">
		<header class="search__head">
			<h1 class="search__title">Результаты по запросу «<?php echo esc_html( $query ); ?>»</h1>
			<p class="search__count">Найдено <?php echo esc_html( $total . ' ' . $results_word ); ?></p>
		</header>

		<?php if ( $total > 0 ) : ?>
			<div class="search__layout">
				<aside class="search__filters">
					<span class="search__filters-label">Категория</span>
					<?php foreach ( $filters as $key => $label ) : ?>
						<?php if ( 'all' === $key || $counts[ $key ] > 0 ) : ?>
							<button
								type="button"
								class="search__filter<?php echo 'all' === $key ? ' is-active' : ''; ?>"
								data-filter="<?php echo esc_attr( $key ); ?>">
								<span class="search__filter-name"><?php echo esc_html( $label ); ?></span>
								<span class="search__filter-count"><?php echo esc_html( $counts[ $key ] ); ?></span>
							</button>
						<?php endif; ?>
					<?php endforeach; ?>
				</aside>

				<div class="search__results">
					<?php foreach ( $results as $r ) : ?>
						<?php
						$is_link = ! empty( $r['url'] );
						$tag     = $is_link ? 'a' : 'div';
						?>
						<<?php echo $tag; ?> class="search__result" data-type="<?php echo esc_attr( $r['type'] ); ?>"<?php echo $is_link ? ' href="' . esc_url( $r['url'] ) . '"' : ''; ?>>
							<span class="search__result-icon search__result-icon--<?php echo esc_attr( $r['type'] ); ?>" aria-hidden="true"><?php echo sputnik_search_icon( $r['type'] ); ?></span>
							<div class="search__result-body">
								<div class="search__result-meta">
									<span class="search__result-type"><?php echo esc_html( $r['type_label'] ); ?></span>
									<?php if ( ! empty( $r['breadcrumb'] ) ) : ?>
										<span class="search__result-crumb"><?php echo esc_html( $r['breadcrumb'] ); ?></span>
									<?php endif; ?>
								</div>
								<span class="search__result-title"><?php echo esc_html( $r['title'] ); ?></span>
								<?php if ( ! empty( $r['excerpt'] ) ) : ?>
									<p class="search__result-excerpt"><?php echo esc_html( $r['excerpt'] ); ?></p>
								<?php endif; ?>
							</div>
							<div class="search__result-aside">
								<?php if ( ! empty( $r['price'] ) ) : ?>
									<span class="search__result-price"><?php echo esc_html( $r['price'] ); ?></span>
								<?php endif; ?>
								<?php if ( ! empty( $r['bookable'] ) && $phone ) : ?>
									<a class="search__result-book" href="tel:<?php echo esc_attr( $phone ); ?>">Записаться</a>
								<?php elseif ( $is_link ) : ?>
									<span class="search__result-arrow" aria-hidden="true">→</span>
								<?php endif; ?>
							</div>
						</<?php echo $tag; ?>>
					<?php endforeach; ?>

					<div class="search__filter-empty" hidden>В этой категории ничего не найдено.</div>
				</div>
			</div>
		<?php else : ?>
			<div class="search__empty">
				<div class="search__empty-title">Ничего не нашлось</div>
				<p class="search__empty-text">По запросу «<?php echo esc_html( $query ); ?>» ничего не найдено. Попробуйте переформулировать или свяжитесь с нами — подберём врача.</p>
				<?php if ( $phone ) : ?>
					<a class="search__empty-btn" href="tel:<?php echo esc_attr( $phone ); ?>">Связаться с клиникой</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
