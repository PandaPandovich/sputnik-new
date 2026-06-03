<?php
/**
 * Блок «Услуги отделения» — аккордеон услуг с привязкой к категориям прайса.
 * Цены подтягиваются из ACF options (Настройки темы → Цены на услуги).
 */

$title    = get_field( 'title' );
$image    = get_field( 'image' );
$services = get_field( 'services' );
?>

<section class="dept-services">
    <div class="container">
        <?php if ( $title ) : ?>
            <h2 class="dept-services__title"><?php echo esc_html( $title ); ?></h2>
        <?php endif; ?>

        <div class="dept-services__layout">
            <?php if ( $image ) : ?>
                <div class="dept-services__image">
                    <?php echo wp_get_attachment_image( $image, 'large', false, [ 'class' => 'dept-services__img' ] ); ?>
                </div>
            <?php endif; ?>

            <?php if ( $services ) : ?>
                <div class="dept-services__list">
                    <?php foreach ( $services as $item ) :
                        $name        = $item['name'] ?? '';
                        $description = $item['description'] ?? '';

                        if ( ! $name ) {
                            continue;
                        }
                    ?>
                        <div class="dept-services__item">
                            <button class="dept-services__item-header" type="button">
                                <span class="dept-services__item-name"><?php echo esc_html( $name ); ?></span>
                                <span class="dept-services__item-arrow">
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 1L9 9M9 9V1M9 9H1" stroke="#FF4D4D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </button>

                            <div class="dept-services__item-body">
                                <?php if ( $description ) : ?>
                                    <p class="dept-services__item-desc"><?php echo esc_html( $description ); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
