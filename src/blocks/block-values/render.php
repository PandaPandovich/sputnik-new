<?php
/**
 * Блок «Наши ценности» — сетка карточек с иконками и описанием.
 */

$values = [
    [
        'icon'  => 'value-doctors.png',
        'title' => 'Квалифицированные ветеринарные врачи',
        'text'  => 'Все наши специалисты с высшим образованием и опытом работы от 3-х лет',
    ],
    [
        'icon'  => 'value-doctors.png',
        'title' => 'Квалифицированные ветеринарные врачи',
        'text'  => 'Все наши специалисты с высшим образованием и опытом работы от 3-х лет',
    ],
    [
        'icon'  => 'value-doctors.png',
        'title' => 'Квалифицированные ветеринарные врачи',
        'text'  => 'Все наши специалисты с высшим образованием и опытом работы от 3-х лет',
    ],
    [
        'icon'  => 'value-stationary.png',
        'title' => 'Круглосуточный ветеринарный стационар',
        'text'  => 'Ваш питомец под наблюдением дежурного врача',
    ],
    [
        'icon'  => 'value-stationary.png',
        'title' => 'Круглосуточный ветеринарный стационар',
        'text'  => 'Ваш питомец под наблюдением дежурного врача',
    ],
    [
        'icon'  => 'value-clock.png',
        'title' => 'Прием ветеринара без очередей',
        'text'  => 'Работаем по предварительной записи, за исключением срочных случаев',
    ],
];
?>

<section class="values">
    <div class="container">
        <h2 class="values__title">Наши ценности</h2>

        <div class="values__grid">
            <?php foreach ($values as $item) : ?>
                <div class="values__card">
                    <div class="values__icon">
                        <img src="<?php echo get_template_directory_uri(); ?>/build/img/<?php echo esc_attr($item['icon']); ?>" alt="">
                    </div>
                    <h4 class="values__card-title"><?php echo esc_html($item['title']); ?></h4>
                    <p class="values__card-text"><?php echo esc_html($item['text']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
