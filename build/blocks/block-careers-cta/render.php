<?php
/**
 * Блок «Не нашли подходящую вакансию?» — CTA на отправку резюме.
 */

$title  = get_field( 'title' );
$text   = get_field( 'text' );
$button = get_field( 'button' );
$image  = get_field( 'image' );

if ( ! $title && ! $text && ! $image ) {
    return;
}
?>

<section class="careers-cta">
    <div class="container">
        <div class="careers-cta__wrap<?php echo $image ? '' : ' careers-cta__wrap--no-media'; ?>">
            <?php if ( $image ) : ?>
                <div class="careers-cta__media">
                    <?php echo wp_get_attachment_image( $image, 'large', false, [
                        'sizes' => '(max-width: 900px) 100vw, 50vw',
                    ] ); ?>
                </div>
            <?php endif; ?>

            <div class="careers-cta__content">
                <?php if ( $title ) : ?>
                    <h2 class="careers-cta__title"><?php echo esc_html( $title ); ?></h2>
                <?php endif; ?>

                <?php if ( $text ) : ?>
                    <p class="careers-cta__text"><?php echo esc_html( $text ); ?></p>
                <?php endif; ?>

                <?php if ( $button && ! empty( $button['url'] ) ) : ?>
                    <?php /* Если на странице есть блок вакансий, его view.js перехватит клик и откроет форму отклика. */ ?>
                    <a class="careers-cta__button"
                       href="<?php echo esc_url( $button['url'] ); ?>"
                       data-vacancy-open-form
                       <?php echo ! empty( $button['target'] ) ? 'target="' . esc_attr( $button['target'] ) . '"' : ''; ?>>
                        <?php echo esc_html( $button['title'] ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
