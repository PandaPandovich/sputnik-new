<?php
/**
 * Блок «Важно знать» — информационная плашка (callout).
 */

$title   = get_field( 'title' );
$content = get_field( 'content' );

if ( ! $title && ! $content ) {
    return;
}
?>

<div class="imp">
    <span class="imp__icon" aria-hidden="true">i</span>
    <div class="imp__body">
        <?php if ( $title ) : ?>
            <p class="imp__title"><?php echo esc_html( $title ); ?></p>
        <?php endif; ?>
        <?php if ( $content ) : ?>
            <div class="imp__text"><?php echo wp_kses_post( $content ); ?></div>
        <?php endif; ?>
    </div>
</div>
