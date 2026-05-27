<?php
/**
 * Блок «Диспансеризация» — CTA-блок про плановую диспансеризацию.
 */

$title  = get_field( 'title' );
$text   = get_field( 'text' );
$button = get_field( 'button' );
$image  = get_field( 'image' );

if ( ! $title && ! $text && ! $button && ! $image ) {
    return;
}
?>

<section class="dispensarization">
    <div class="container">
        <div class="dispensarization__layout">
            <div class="dispensarization__content">
                <?php if ( $title ) : ?>
                    <h2 class="dispensarization__title"><?php echo wp_kses_post( $title ); ?></h2>
                <?php endif; ?>

                <?php if ( $text ) : ?>
                    <p class="dispensarization__text"><?php echo esc_html( $text ); ?></p>
                <?php endif; ?>

                <?php if ( $button ) : ?>
                    <a href="<?php echo esc_url( $button['url'] ); ?>"
                       class="dispensarization__button"
                       <?php echo ! empty( $button['target'] ) ? 'target="' . esc_attr( $button['target'] ) . '"' : ''; ?>>
                        <?php echo esc_html( $button['title'] ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
                        <?php if ( $image ) : ?>
                <div class="dispensarization__image">
                    <?php echo wp_get_attachment_image( $image, 'full' ); ?>
                </div>
            <?php endif; ?>
    </div>
</section>
