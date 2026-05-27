<?php
/**
 * Блок «FAQ» — часто задаваемые вопросы (аккордеон).
 */

$title = get_field( 'title' );
$items = get_field( 'items' );

if ( ! $title && ! $items ) {
    return;
}
?>

<section class="faq">
    <div class="container">
        <?php if ( $title ) : ?>
            <h2 class="faq__title"><?php echo esc_html( $title ); ?></h2>
        <?php endif; ?>

        <?php if ( $items ) : ?>
            <div class="faq__list">
                <?php foreach ( $items as $item ) : ?>
                    <div class="faq__item">
                        <button class="faq__question" type="button">
                            <span><?php echo esc_html( $item['question'] ); ?></span>
                            <span class="faq__icon"></span>
                        </button>
                        <div class="faq__answer">
                            <?php echo wp_kses_post( $item['answer'] ); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
