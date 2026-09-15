<?php
/**
 * Блок «Наша команда» (тизер для страницы вакансий).
 * Сотрудники выбираются из «Настройки темы → Команда» — фото, имя
 * и специализация подтягиваются оттуда, дублировать их в блоке не нужно.
 */

$title  = get_field( 'title' );
$text   = get_field( 'text' );
$button = get_field( 'button' );
$rows   = get_field( 'members' );

$members = [];
if ( $rows && function_exists( 'sputnik_plus_get_team_member' ) ) {
    foreach ( $rows as $row ) {
        $name = isset( $row['member'] ) ? trim( (string) $row['member'] ) : '';
        if ( '' === $name ) {
            continue;
        }

        // Сотрудника могли переименовать или удалить из настроек темы.
        $member = sputnik_plus_get_team_member( $name );
        if ( $member ) {
            $members[] = $member;
        }
    }
}

if ( ! $title && ! $text && ! $members ) {
    return;
}
?>

<section class="careers-team">
    <div class="container">
        <div class="careers-team__grid<?php echo $members ? '' : ' careers-team__grid--single'; ?>">
            <div class="careers-team__content">
                <?php if ( $title ) : ?>
                    <h2 class="careers-team__title"><?php echo esc_html( $title ); ?></h2>
                <?php endif; ?>

                <?php if ( $text ) : ?>
                    <p class="careers-team__text"><?php echo esc_html( $text ); ?></p>
                <?php endif; ?>

                <?php if ( $button && ! empty( $button['url'] ) ) : ?>
                    <a class="careers-team__button"
                       href="<?php echo esc_url( $button['url'] ); ?>"
                       <?php echo ! empty( $button['target'] ) ? 'target="' . esc_attr( $button['target'] ) . '"' : ''; ?>>
                        <?php echo esc_html( $button['title'] ); ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php if ( $members ) : ?>
                <div class="careers-team__members">
                    <?php foreach ( $members as $member ) : ?>
                        <figure class="careers-team__member">
                            <div class="careers-team__photo">
                                <?php if ( ! empty( $member['photo'] ) ) : ?>
                                    <?php echo wp_get_attachment_image( $member['photo'], 'medium_large', false, [
                                        'alt'   => $member['name'],
                                        'sizes' => '(max-width: 568px) 45vw, (max-width: 1024px) 30vw, 20vw',
                                    ] ); ?>
                                <?php endif; ?>
                            </div>

                            <figcaption class="careers-team__caption">
                                <span class="careers-team__name"><?php echo esc_html( $member['name'] ); ?></span>

                                <?php if ( ! empty( $member['specialization'] ) ) : ?>
                                    <span class="careers-team__position"><?php echo esc_html( $member['specialization'] ); ?></span>
                                <?php endif; ?>
                            </figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
