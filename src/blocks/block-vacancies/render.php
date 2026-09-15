<?php
/**
 * Блок «Открытые вакансии» — фильтры по направлениям + список карточек.
 * Клик по карточке открывает модальное окно с описанием вакансии.
 */

$title      = get_field( 'title' );
$subtitle   = get_field( 'subtitle' );
$all_label  = get_field( 'all_filter_label' );
$vacancies  = get_field( 'items' );
$apply_note = get_field( 'apply_note' );
$form_id    = get_field( 'form' );

if ( ! $vacancies ) {
    return;
}

/**
 * Направления для фильтра собираются из поля «Направление» самих вакансий,
 * поэтому отдельный список направлений редактору вести не нужно.
 * Слаг — порядковый, чтобы не зависеть от транслитерации кириллицы.
 */
$directions = [];
foreach ( $vacancies as $vacancy ) {
    $group = isset( $vacancy['group'] ) ? trim( (string) $vacancy['group'] ) : '';
    if ( '' !== $group && ! isset( $directions[ $group ] ) ) {
        $directions[ $group ] = 'dir-' . ( count( $directions ) + 1 );
    }
}

$icons = [
    'group'    => '<circle cx="8" cy="5.5" r="2.6" stroke="currentColor" stroke-width="1.2"/><path d="M3 14.5a5 5 0 0 1 10 0" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>',
    'city'     => '<path d="M8 14.5s5-4.3 5-8a5 5 0 0 0-10 0c0 3.7 5 8 5 8Z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/><circle cx="8" cy="6.5" r="1.8" stroke="currentColor" stroke-width="1.2"/>',
    'schedule' => '<rect x="2.5" y="3.5" width="11" height="10" rx="2" stroke="currentColor" stroke-width="1.2"/><path d="M2.5 6.5h11M5.5 2v3M10.5 2v3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>',
];

/**
 * Строка мета-данных вакансии (направление · город · график).
 */
$render_meta = function ( $vacancy ) use ( $icons ) {
    foreach ( [ 'group', 'city', 'schedule' ] as $key ) {
        $value = isset( $vacancy[ $key ] ) ? trim( (string) $vacancy[ $key ] ) : '';
        if ( '' === $value ) {
            continue;
        }
        printf(
            '<span class="vacancies__tag"><svg viewBox="0 0 16 16" fill="none" aria-hidden="true">%s</svg>%s</span>',
            $icons[ $key ],
            esc_html( $value )
        );
    }
};

/**
 * Поля-списки заполняются построчно: одна строка textarea — один пункт.
 *
 * Модификатор /u обязателен: без него \R матчит байт 0x85, а это второй байт
 * UTF-8 последовательности буквы «х» — текст рвался бы посреди слов.
 */
$to_list = static function ( $value ) {
    $lines = preg_split( '/\R/u', (string) $value );
    $lines = array_map( 'trim', (array) $lines );

    return array_values( array_filter( $lines, static fn( $line ) => '' !== $line ) );
};
?>

<section class="vacancies" id="vacancies" data-vacancies>
    <div class="container">
        <?php if ( $title || $subtitle ) : ?>
            <div class="vacancies__header">
                <?php if ( $title ) : ?>
                    <h2 class="vacancies__title"><?php echo esc_html( $title ); ?></h2>
                <?php endif; ?>

                <?php if ( $subtitle ) : ?>
                    <p class="vacancies__subtitle"><?php echo esc_html( $subtitle ); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ( count( $directions ) > 1 ) : ?>
            <div class="vacancies__filters" role="tablist" aria-label="Направления">
                <button
                    type="button"
                    class="vacancies__filter is-active"
                    data-vacancy-filter="all"
                    aria-pressed="true"
                ><?php echo esc_html( $all_label ? $all_label : 'Все' ); ?></button>

                <?php foreach ( $directions as $label => $slug ) : ?>
                    <button
                        type="button"
                        class="vacancies__filter"
                        data-vacancy-filter="<?php echo esc_attr( $slug ); ?>"
                        aria-pressed="false"
                    ><?php echo esc_html( $label ); ?></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="vacancies__list">
            <?php foreach ( $vacancies as $i => $vacancy ) : ?>
                <?php $group = isset( $vacancy['group'] ) ? trim( (string) $vacancy['group'] ) : ''; ?>
                <button
                    type="button"
                    class="vacancies__card"
                    data-vacancy-category="<?php echo esc_attr( $group && isset( $directions[ $group ] ) ? $directions[ $group ] : '' ); ?>"
                    data-vacancy-index="<?php echo (int) $i; ?>"
                    aria-haspopup="dialog"
                >
                    <span class="vacancies__card-name"><?php echo esc_html( $vacancy['title'] ?? '' ); ?></span>

                    <span class="vacancies__card-meta">
                        <?php $render_meta( $vacancy ); ?>
                    </span>

                    <span class="vacancies__card-arrow" aria-hidden="true">
                        <svg width="8" height="14" viewBox="0 0 8 14" fill="none">
                            <path d="M1 1l6 6-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </button>
            <?php endforeach; ?>
        </div>

        <p class="vacancies__empty" data-vacancies-empty hidden>
            В&nbsp;этом направлении сейчас нет открытых вакансий
        </p>
    </div>

    <?php
    /**
     * Описания и модалку рендерим только на фронте.
     * В превью редактора ACF отдаёт HTML без атрибута hidden, скрытие завязано
     * на [hidden] — и fixed-модалка накрывала бы канвас, перехватывая клики
     * по остальным блокам.
     */
    ?>
    <?php if ( empty( $is_preview ) ) : ?>

    <!-- Описания вакансий: скрыты на странице, подставляются в модальное окно -->
    <div class="vacancies__details" hidden>
        <?php foreach ( $vacancies as $i => $vacancy ) : ?>
            <div class="vacancy-detail" data-vacancy-detail="<?php echo (int) $i; ?>">
                <?php if ( ! empty( $vacancy['group'] ) ) : ?>
                    <span class="vacancy-detail__group"><?php echo esc_html( $vacancy['group'] ); ?></span>
                <?php endif; ?>

                <h2 class="vacancy-detail__title"><?php echo esc_html( $vacancy['title'] ?? '' ); ?></h2>

                <div class="vacancy-detail__meta">
                    <?php $render_meta( $vacancy ); ?>
                </div>

                <?php if ( ! empty( $vacancy['salary'] ) ) : ?>
                    <p class="vacancy-detail__salary"><?php echo esc_html( $vacancy['salary'] ); ?></p>
                <?php endif; ?>

                <?php if ( ! empty( $vacancy['about'] ) ) : ?>
                    <p class="vacancy-detail__about"><?php echo esc_html( $vacancy['about'] ); ?></p>
                <?php endif; ?>

                <?php
                $sections = [
                    'Что предстоит делать' => $to_list( $vacancy['duties'] ?? '' ),
                    'Что мы ждём'          => $to_list( $vacancy['requirements'] ?? '' ),
                    'Что предлагаем'       => $to_list( $vacancy['offer'] ?? '' ),
                ];
                ?>
                <?php foreach ( $sections as $heading => $list ) : ?>
                    <?php if ( ! $list ) : ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <div class="vacancy-detail__section">
                        <h3 class="vacancy-detail__section-title"><?php echo esc_html( $heading ); ?></h3>
                        <ul class="vacancy-detail__list">
                            <?php foreach ( $list as $line ) : ?>
                                <li><?php echo esc_html( $line ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>

                <div class="vacancy-detail__actions">
                    <button
                        type="button"
                        class="vacancy-detail__button"
                        data-vacancy-apply
                        data-vacancy-title="<?php echo esc_attr( $vacancy['title'] ?? '' ); ?>"
                    >Откликнуться</button>
                    <?php if ( $apply_note ) : ?>
                        <span class="vacancy-detail__note"><?php echo esc_html( $apply_note ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Модальное окно вакансии -->
    <div class="vacancy-modal" data-vacancy-modal role="dialog" aria-modal="true" aria-label="Описание вакансии" hidden>
        <div class="vacancy-modal__overlay" data-vacancy-close></div>
        <div class="vacancy-modal__window">
            <button type="button" class="vacancy-modal__close" data-vacancy-close aria-label="Закрыть">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </button>
            <!-- Экран 1: описание вакансии -->
            <div class="vacancy-modal__body" data-vacancy-modal-body></div>

            <!-- Экран 2: форма отклика -->
            <div class="vacancy-modal__body" data-vacancy-form-view hidden>
                <button type="button" class="vacancy-form__back" data-vacancy-back>
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                        <path d="M8.5 2.5 4 7l4.5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Назад к вакансии
                </button>

                <div data-vacancy-form-wrap>
                    <?php /* data-heading-general — заголовок для отклика без вакансии (кнопка в баннере). */ ?>
                    <h2 class="vacancy-form__title" data-vacancy-form-heading data-heading-general="Отправить резюме">Отклик на вакансию</h2>
                    <p class="vacancy-form__vacancy" data-vacancy-form-title></p>

                    <?php if ( $form_id && shortcode_exists( 'contact-form-7' ) ) : ?>
                        <?php
                        // Разметка формы живёт в самой CF7-форме и написана на классах темы.
                        echo do_shortcode( sprintf( '[contact-form-7 id="%d" html_class="vacancy-form"]', (int) $form_id ) );
                        ?>
                    <?php else : ?>
                        <p class="vacancy-form__vacancy">Форма отклика не выбрана в настройках блока.</p>
                    <?php endif; ?>
                </div>

                <div class="vacancy-form__success" data-vacancy-success hidden>
                    <span class="vacancy-form__success-icon">
                        <svg width="26" height="26" viewBox="0 0 26 26" fill="none" aria-hidden="true">
                            <path d="m7 13.5 4 4 8-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h2 class="vacancy-form__success-title">Спасибо за отклик!</h2>
                    <p class="vacancy-form__success-text">
                        Мы&nbsp;получили ваше резюме и&nbsp;свяжемся с&nbsp;вами в&nbsp;течение двух рабочих дней.
                    </p>
                    <button type="button" class="vacancy-form__submit" data-vacancy-close>Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>
</section>
