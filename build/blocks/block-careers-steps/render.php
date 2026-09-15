<?php
/**
 * Блок «Как проходит найм?» — пошаговая схема.
 */

$title = get_field( 'title' );
$items = get_field( 'items' );

if ( ! $title && ! $items ) {
    return;
}
?>

<section class="careers-steps">
    <div class="container">
        <?php if ( $title ) : ?>
            <h2 class="careers-steps__title"><?php echo esc_html( $title ); ?></h2>
        <?php endif; ?>

        <?php if ( $items ) : ?>
            <ol class="careers-steps__list">
                <?php foreach ( $items as $i => $item ) : ?>
                    <li class="careers-steps__item">
                        <span class="careers-steps__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>

                        <?php if ( ! empty( $item['item_title'] ) ) : ?>
                            <h3 class="careers-steps__item-title"><?php echo esc_html( $item['item_title'] ); ?></h3>
                        <?php endif; ?>

                        <?php if ( ! empty( $item['item_text'] ) ) : ?>
                            <p class="careers-steps__item-text"><?php echo esc_html( $item['item_text'] ); ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
</section>
