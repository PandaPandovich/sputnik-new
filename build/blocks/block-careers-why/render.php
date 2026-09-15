<?php
/**
 * Блок «Почему „Спутник“?» — ключевые ценности работы в клинике.
 */

$title = get_field( 'title' );
$text  = get_field( 'text' );
$items = get_field( 'items' );

if ( ! $title && ! $text && ! $items ) {
    return;
}
?>

<section class="careers-why">
    <div class="container">
        <div class="careers-why__grid">
            <div class="careers-why__intro">
                <?php if ( $title ) : ?>
                    <h2 class="careers-why__title"><?php echo esc_html( $title ); ?></h2>
                <?php endif; ?>

                <?php if ( $text ) : ?>
                    <p class="careers-why__text"><?php echo esc_html( $text ); ?></p>
                <?php endif; ?>
            </div>

            <?php if ( $items ) : ?>
                <ul class="careers-why__items">
                    <?php foreach ( $items as $item ) : ?>
                        <li class="careers-why__card">
                            <?php if ( ! empty( $item['icon'] ) ) : ?>
                                <span class="careers-why__icon">
                                    <?php echo wp_get_attachment_image( $item['icon'], 'full' ); ?>
                                </span>
                            <?php endif; ?>

                            <?php if ( ! empty( $item['item_title'] ) ) : ?>
                                <h3 class="careers-why__card-title"><?php echo esc_html( $item['item_title'] ); ?></h3>
                            <?php endif; ?>

                            <?php if ( ! empty( $item['item_text'] ) ) : ?>
                                <p class="careers-why__card-text"><?php echo esc_html( $item['item_text'] ); ?></p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</section>
