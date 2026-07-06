<?php
/**
 * Блок «Документы» — сетка плиток с иконкой, названием, размером и ссылкой.
 */

$title = get_field( 'title' );
$items = get_field( 'items' );

if ( ! $title && ! $items ) {
    return;
}
?>

<section class="documents">
    <div class="container">
        <?php if ( $title ) : ?>
            <h2 class="documents__title"><?php echo esc_html( $title ); ?></h2>
        <?php endif; ?>
    </div>
</section>
