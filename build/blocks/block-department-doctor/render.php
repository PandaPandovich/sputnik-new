<?php
/**
 * Блок «Врач отделения» — описание специалиста + карусель карточек врачей.
 * Врачи подтягиваются из ACF options (Настройки темы → Команда).
 */

$title       = get_field( 'title' );
$description = get_field( 'description' );
$button      = get_field( 'button' );
$doctor_ids  = get_field( 'doctors' );

// Загружаем команду из настроек
$team = get_field( 'team_members', 'option' ) ?: [];

// Собираем выбранных врачей
$doctors = [];
if ( $doctor_ids ) {
    foreach ( $doctor_ids as $index ) {
        if ( isset( $team[ $index ] ) ) {
            $doctors[] = $team[ $index ];
        }
    }
}
?>

<section class="dept-doctor">
    <div class="container">
        <div class="dept-doctor__layout">
            <div class="dept-doctor__info">
                <?php if ( $title ) : ?>
                    <h2 class="dept-doctor__title"><?php echo esc_html( $title ); ?></h2>
                <?php endif; ?>

                <?php if ( $description ) : ?>
                    <div class="dept-doctor__text"><?php echo wp_kses_post( $description ); ?></div>
                <?php endif; ?>

                <?php if ( is_array( $button ) && ! empty( $button['url'] ) ) : ?>
                    <a href="<?php echo esc_url( $button['url'] ); ?>" class="dept-doctor__button">
                        <?php echo esc_html( $button['title'] ); ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php if ( $doctors ) : ?>
                <div class="dept-doctor__slider">
                    <div class="dept-doctor__cards swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ( $doctors as $i => $doctor ) :
                                $name  = $doctor['name'] ?? '';
                                $role  = $doctor['specialization'] ?? '';
                                $photo = $doctor['photo'] ?? '';
                            ?>
                                <div class="dept-doctor__card swiper-slide" data-doctor="<?php echo $i; ?>">
                                    <?php if ( $photo ) : ?>
                                        <div class="dept-doctor__card-photo">
                                            <?php echo wp_get_attachment_image( $photo, 'medium_large', false, [ 'class' => 'dept-doctor__card-img' ] ); ?>
                                        </div>
                                    <?php endif; ?>
                                    <h4 class="dept-doctor__card-name"><?php echo esc_html( $name ); ?></h4>
                                    <span class="dept-doctor__card-role"><?php echo esc_html( $role ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="dept-doctor__nav">
                        <button class="dept-doctor__nav-prev" type="button" aria-label="Предыдущий">
                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7 1L3 5L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <button class="dept-doctor__nav-next" type="button" aria-label="Следующий">
                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 1L7 5L3 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ( $doctors ) : ?>
        <!-- Модальное окно врача -->
        <div class="team-modal" id="deptDoctorModal">
            <div class="team-modal__overlay"></div>
            <button class="team-modal__nav team-modal__nav--prev" id="deptDoctorModalPrev" aria-label="Предыдущий">
                <svg width="10" height="16" viewBox="0 0 10 16" fill="none"><path d="M9 1L1 8L9 15" stroke="#1D3658" stroke-width="1.5"/></svg>
            </button>
            <button class="team-modal__nav team-modal__nav--next" id="deptDoctorModalNext" aria-label="Следующий">
                <svg width="10" height="16" viewBox="0 0 10 16" fill="none"><path d="M1 1L9 8L1 15" stroke="#1D3658" stroke-width="1.5"/></svg>
            </button>
            <div class="team-modal__window">
                <div class="team-modal__left">
                    <img class="team-modal__photo" id="deptModalPhoto" src="" alt="">
                </div>
                <div class="team-modal__right">
                    <button class="team-modal__close" id="deptDoctorModalClose" aria-label="Закрыть">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M1 1L11 11M11 1L1 11" stroke="#1D3658" stroke-width="1.5"/></svg>
                    </button>
                    <div class="team-modal__content">
                        <span class="team-modal__tag" id="deptModalTag"></span>
                        <h2 class="team-modal__name" id="deptModalName"></h2>
                        <p class="team-modal__position" id="deptModalPosition"></p>

                        <div class="team-modal__section" id="deptModalEduSection">
                            <div class="team-modal__section-header">
                                <span class="team-modal__section-title">Образование</span>
                                <span class="team-modal__section-line"></span>
                            </div>
                            <p class="team-modal__text" id="deptModalEduText"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Подготовка данных для JS
        $js_doctors = array_map( function( $m ) {
            $photo_url = '';
            if ( ! empty( $m['photo'] ) ) {
                $photo_url = wp_get_attachment_image_url( $m['photo'], 'large' );
            }
            return [
                'name'     => $m['name'] ?? '',
                'position' => $m['specialization'] ?? '',
                'photo'    => $photo_url,
                'tag'      => $m['tag'] ?? '',
                'edu_text' => $m['description'] ?? '',
            ];
        }, $doctors );
        ?>
        <script type="application/json" id="deptDoctorData"><?php echo wp_json_encode( array_values( $js_doctors ) ); ?></script>
    <?php endif; ?>
</section>
