<?php
/**
 * Блок «Миф и реальность» — заголовок мифа + две карточки-сравнения.
 */

$heading       = get_field( 'heading' );
$myth_label    = get_field( 'myth_label' );
$myth_text     = get_field( 'myth_text' );
$reality_label = get_field( 'reality_label' );
$reality_text  = get_field( 'reality_text' );

if ( ! $heading && ! $myth_text && ! $reality_text ) {
    return;
}
?>

<div class="myth">
    <?php if ( $heading ) : ?>
        <h3 class="myth__heading"><?php echo esc_html( $heading ); ?></h3>
    <?php endif; ?>

    <div class="myth__cards">
        <div class="myth__card myth__card--myth">
            <div class="myth__head">
                <span class="myth__badge myth__badge--myth" aria-hidden="true">✕</span>
                <?php if ( $myth_label ) : ?>
                    <span class="myth__label myth__label--myth"><?php echo esc_html( $myth_label ); ?></span>
                <?php endif; ?>
            </div>
            <?php if ( $myth_text ) : ?>
                <div class="myth__text"><?php echo wp_kses_post( $myth_text ); ?></div>
            <?php endif; ?>
        </div>

        <div class="myth__card myth__card--reality">
            <div class="myth__head">
                <span class="myth__badge myth__badge--reality" aria-hidden="true">✓</span>
                <?php if ( $reality_label ) : ?>
                    <span class="myth__label myth__label--reality"><?php echo esc_html( $reality_label ); ?></span>
                <?php endif; ?>
            </div>
            <?php if ( $reality_text ) : ?>
                <div class="myth__text"><?php echo wp_kses_post( $reality_text ); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
