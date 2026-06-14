<?php
/**
 * Блок «Наша команда» — сетка с фото сотрудников + модальное окно.
 * Данные берутся из ACF-опций «Настройки темы → Команда».
 */

$members = get_field('team_members', 'option');
if (empty($members)) return;
?>

<section class="team">
    <div class="container">
        <div class="team__header">
            <h2 class="team__title">Наша команда</h2>
            <p class="team__subtitle">Наши сотрудники досконально знают свое дело, увлечены профессией и&nbsp;постоянно совершенствуются в&nbsp;ней</p>
        </div>

        <div class="team__grid">
            <?php foreach ($members as $index => $m) : ?>
                <div class="team__item" data-member="<?php echo $index; ?>">
                    <div class="team__photo">
                        <?php if (!empty($m['photo'])) : ?>
                            <?php echo wp_get_attachment_image($m['photo'], 'medium_large', false, ['class' => 'team__img']); ?>
                        <?php endif; ?>
                    </div>
                    <div class="team__info">
                        <h4 class="team__name"><?php echo esc_html($m['name']); ?></h4>
                        <?php if (!empty($m['specialization'])) : ?>
                            <p class="team__position"><?php echo esc_html($m['specialization']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Модальное окно сотрудника -->
    <div class="team-modal" id="teamModal">
        <div class="team-modal__overlay"></div>
        <button class="team-modal__nav team-modal__nav--prev" id="teamModalPrev" aria-label="Предыдущий">
            <svg width="10" height="16" viewBox="0 0 10 16" fill="none"><path d="M9 1L1 8L9 15" stroke="#1D3658" stroke-width="1.5"/></svg>
        </button>
        <button class="team-modal__nav team-modal__nav--next" id="teamModalNext" aria-label="Следующий">
            <svg width="10" height="16" viewBox="0 0 10 16" fill="none"><path d="M1 1L9 8L1 15" stroke="#1D3658" stroke-width="1.5"/></svg>
        </button>
        <div class="team-modal__window">
            <div class="team-modal__left">
                <img class="team-modal__photo" id="modalPhoto" src="" alt="">
            </div>
            <div class="team-modal__right">
                <button class="team-modal__close" id="teamModalClose" aria-label="Закрыть">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M1 1L11 11M11 1L1 11" stroke="#1D3658" stroke-width="1.5"/></svg>
                </button>
                <div class="team-modal__content">
                    <span class="team-modal__tag" id="modalTag"></span>
                    <h2 class="team-modal__name" id="modalName"></h2>
                    <p class="team-modal__position" id="modalPosition"></p>

                    <div class="team-modal__section" id="modalEduSection">
                        <div class="team-modal__section-header">
                            <span class="team-modal__section-line"></span>
                        </div>
                        <p class="team-modal__text" id="modalEduText"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Подготовка данных для JS
    $js_data = array_map(function($m) {
        $photo_url = '';
        if (!empty($m['photo'])) {
            $photo_url = wp_get_attachment_image_url($m['photo'], 'large');
        }
        return [
            'name'     => $m['name'] ?? '',
            'position' => $m['specialization'] ?? '',
            'photo'    => $photo_url,
            'tag'      => $m['tag'] ?? '',
            'edu_text' => $m['description'] ?? '',
        ];
    }, $members);
    ?>
    <script type="application/json" id="teamData"><?php echo wp_json_encode($js_data); ?></script>
</section>
