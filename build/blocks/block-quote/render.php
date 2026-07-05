<?php
/**
 * Блок «Цитата» — цитата с автором в стиле Спутник.
 */

$quote  = get_field( 'quote' );
$author = get_field( 'author' );

if ( ! $quote && ! $author ) {
    return;
}
?>

<figure class="quote">
    <?php if ( $quote ) : ?>
        <blockquote class="quote__body"><?php echo wp_kses_post( $quote ); ?></blockquote>
    <?php endif; ?>
    <?php if ( $author ) : ?>
        <figcaption class="quote__author"><?php echo esc_html( $author ); ?></figcaption>
    <?php endif; ?>
</figure>
