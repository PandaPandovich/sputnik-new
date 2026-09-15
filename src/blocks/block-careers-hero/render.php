<?php
/**
 * Блок «Работа в клинике» — первый экран страницы вакансий.
 */

$image       = get_field( 'image' );
$note_line_1 = get_field( 'note_line_1' );
$note_line_2 = get_field( 'note_line_2' );
$title       = get_field( 'title' );
$subtitle    = get_field( 'subtitle' );
$text        = get_field( 'text' );
$button      = get_field( 'button' );

if ( ! $title && ! $image ) {
    return;
}
?>

<section class="careers-hero">
    <?php if ( $image || $note_line_1 || $note_line_2 ) : ?>
        <div class="careers-hero__media">
            <?php if ( $image ) : ?>
                <?php echo wp_get_attachment_image( $image, 'full', false, [
                    'class' => 'careers-hero__img',
                    'sizes' => '(max-width: 900px) 100vw, 52vw',
                ] ); ?>
            <?php endif; ?>

            <?php if ( $note_line_1 || $note_line_2 ) : ?>
                <span class="careers-hero__note" aria-hidden="true">
                    <?php if ( $note_line_1 ) : ?>
                        <span class="careers-hero__note-line"><?php echo esc_html( $note_line_1 ); ?></span>
                    <?php endif; ?>
                    <?php if ( $note_line_2 ) : ?>
                        <span class="careers-hero__note-line careers-hero__note-line--indent"><?php echo esc_html( $note_line_2 ); ?></span>
                    <?php endif; ?>
                    <svg class="careers-hero__note-heart" width="26" height="23" viewBox="0 0 26 23" fill="none">
                        <path d="M13 21.2S1.6 14.6 1.6 7.6A5.5 5.5 0 0 1 13 5a5.5 5.5 0 0 1 11.4 2.6c0 7-11.4 13.6-11.4 13.6Z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/>
                    </svg>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="careers-hero__content">
            <?php if ( $title ) : ?>
                <h1 class="careers-hero__title"><?php echo nl2br( esc_html( $title ) ); ?></h1>
            <?php endif; ?>

            <?php if ( $subtitle ) : ?>
                <p class="careers-hero__subtitle"><?php echo esc_html( $subtitle ); ?></p>
            <?php endif; ?>

            <?php if ( $text ) : ?>
                <p class="careers-hero__text"><?php echo esc_html( $text ); ?></p>
            <?php endif; ?>

            <?php if ( $button && ! empty( $button['url'] ) ) : ?>
                <a class="careers-hero__button"
                   href="<?php echo esc_url( $button['url'] ); ?>"
                   <?php echo ! empty( $button['target'] ) ? 'target="' . esc_attr( $button['target'] ) . '"' : ''; ?>>
                    <?php echo esc_html( $button['title'] ); ?>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M8 2.5v11M3.5 9l4.5 4.5L12.5 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
