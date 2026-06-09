<?php
/**
 * Блок «Прайс-лист» — отделения → категории услуг → таблица цен.
 * Данные берутся из ACF options (Настройки темы → Цены на услуги).
 */

$categories = get_field('price_categories', 'option');

if (empty($categories)) {
    return;
}

// Настройки блока
$display_mode = get_field('display_mode') ?: 'all';
$selected_departments = get_field('departments') ?: [];
if (!is_array($selected_departments)) {
    $selected_departments = [$selected_departments];
}
$selected_departments = array_filter($selected_departments);

// Группируем категории по отделениям
$departments = [];
$no_department = [];

foreach ($categories as $index => $cat) {
    $dept_id = $cat['department'] ?? null;
    if ($dept_id) {
        // Фильтруем по выбранным отделениям
        if ($display_mode === 'selected' && !empty($selected_departments) && !in_array($dept_id, $selected_departments)) {
            continue;
        }

        if (!isset($departments[$dept_id])) {
            $dept_post = get_post($dept_id);
            $departments[$dept_id] = [
                'title' => $dept_post ? $dept_post->post_title : "Отделение #{$dept_id}",
                'categories' => [],
            ];
        }
        $departments[$dept_id]['categories'][] = [
            'index' => $index,
            'data' => $cat,
        ];
    } else {
        if ($display_mode === 'all') {
            $no_department[] = [
                'index' => $index,
                'data' => $cat,
            ];
        }
    }
}

// Если есть категории без отделения — добавляем в «Прочее»
if (!empty($no_department)) {
    $departments['other'] = [
        'title' => 'Прочее',
        'categories' => $no_department,
    ];
}

if (empty($departments)) {
    return;
}

$preview_count = 6;
$first_dept = true;
?>

<section class="price-list">
    <div class="container">
        <h2 class="price-list__title">Наши услуги</h2>

        <div class="price-list__layout">
            <!-- Боковая навигация по отделениям -->
            <aside class="price-list__sidebar">
                <ul class="price-list__departments">
                    <?php foreach ($departments as $dept_id => $dept): ?>
                        <li class="price-list__department <?php echo $first_dept ? 'is-active' : ''; ?>"
                            data-department="<?php echo esc_attr($dept_id); ?>">
                            <?php if ($first_dept): ?>
                                <span class="price-list__category-arrow">→</span>
                            <?php endif; ?>
                            <?php echo esc_html($dept['title']); ?>
                        </li>
                        <?php $first_dept = false; ?>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <!-- Контент -->
            <div class="price-list__content">
                <!-- Поиск -->
                <div class="price-list__search">
                    <input type="text" class="price-list__search-input" placeholder="Поиск">
                    <svg class="price-list__search-icon" width="18" height="18" viewBox="0 0 18 18" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12.5 11H11.71L11.43 10.73C12.41 9.59 13 8.11 13 6.5C13 2.91 10.09 0 6.5 0C2.91 0 0 2.91 0 6.5C0 10.09 2.91 13 6.5 13C8.11 13 9.59 12.41 10.73 11.43L11 11.71V12.5L16 17.49L17.49 16L12.5 11ZM6.5 11C4.01 11 2 8.99 2 6.5C2 4.01 4.01 2 6.5 2C8.99 2 11 4.01 11 6.5C11 8.99 8.99 11 6.5 11Z"
                            fill="#B5B5B5" />
                    </svg>
                </div>

                <!-- Группы категорий по отделениям -->
                <?php $first_dept = true; ?>
                <?php foreach ($departments as $dept_id => $dept): ?>
                    <div class="price-list__dept-group <?php echo $first_dept ? 'is-active' : ''; ?>"
                        data-department="<?php echo esc_attr($dept_id); ?>">

                        <?php foreach ($dept['categories'] as $cat_entry):
                            $cat = $cat_entry['data'];
                            $prices = $cat['prices'] ?? [];
                            $total = count($prices);
                            ?>
                            <div class="price-list__table">
                                <!-- Заголовок категории -->
                                <div class="price-list__table-header">
                                    <span class="price-list__table-name">
                                        <?php echo esc_html($cat['name']); ?>
                                    </span>
                                    <span class="price-list__table-count">
                                        <?php echo $total; ?>         <?php
                                                    $mod10 = $total % 10;
                                                    $mod100 = $total % 100;
                                                    if ($mod10 === 1 && $mod100 !== 11) {
                                                        echo 'услуга';
                                                    } elseif ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 12 || $mod100 > 14)) {
                                                        echo 'услуги';
                                                    } else {
                                                        echo 'услуг';
                                                    }
                                                    ?> ↓
                                    </span>
                                </div>

                                <!-- Строки прайса -->
                                <?php foreach ($prices as $i => $price): ?>
                                    <div class="price-list__row <?php echo $i >= $preview_count ? 'is-hidden' : ''; ?>">
                                        <span class="price-list__row-name">
                                            <?php echo wp_kses_post($price['price_category']); ?>
                                        </span>
                                        <span class="price-list__row-price">
                                            <?php
                                            $p = $price['price'];
                                            if (is_numeric($p)) {
                                                echo 'от ' . number_format((float) $p, 0, '', ' ') . ' ₽';
                                            } else {
                                                echo esc_html($p);
                                            }
                                            ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>

                                <!-- Кнопка «Показать все» -->
                                <?php if ($total > $preview_count): ?>
                                    <button class="price-list__show-all" type="button">
                                        Показать все <?php echo $total; ?>             <?php
                                                        if ($mod10 === 1 && $mod100 !== 11) {
                                                            echo 'позицию';
                                                        } elseif ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 12 || $mod100 > 14)) {
                                                            echo 'позиции';
                                                        } else {
                                                            echo 'позиций';
                                                        }
                                                        ?> →
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php $first_dept = false; ?>
                <?php endforeach; ?>

                <!-- Поиск: пустой результат -->
                <div class="price-list__no-results" style="display:none;">
                    Ничего не найдено
                </div>

                <!-- CTA -->
                <div class="price-list__cta">
                    <span class="price-list__cta-text">Не нашли нужную услугу?</span>
                    <a href="tel:+74954450120" class="price-list__cta-button">Свяжитесь с нами</a>
                </div>
            </div>
        </div>
    </div>
</section>