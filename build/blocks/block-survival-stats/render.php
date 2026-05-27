<?php
/**
 * Блок «Статистика выживаемости» — процент по стадиям.
 */
?>

<section class="survival-stats">
    <div class="container">
        <img class="survival-stats__cat" src="<?php echo get_theme_file_uri('build/img/stats-cat.png'); ?>" alt=""
            aria-hidden="true">
        <img class="survival-stats__dog" src="<?php echo get_theme_file_uri('build/img/stats-dog.png'); ?>" alt=""
            aria-hidden="true">
        <h2 class="survival-stats__title">Процент выживаемости на разных<br>стадиях диагностики рака</h2>
        <div class="survival-stats__grid">
            <div class="survival-stats__item survival-stats__item--stage1">
                <span class="survival-stats__stage">1 стадия</span>
                <span class="survival-stats__value">98%</span>
            </div>
            <div class="survival-stats__item survival-stats__item--stage2">
                <span class="survival-stats__stage">2 стадия</span>
                <span class="survival-stats__value">93%</span>
            </div>
            <div class="survival-stats__item survival-stats__item--stage3">
                <span class="survival-stats__stage">3 стадия</span>
                <span class="survival-stats__value">72%</span>
            </div>
            <div class="survival-stats__item survival-stats__item--stage4">
                <span class="survival-stats__stage">4 стадия</span>
                <span class="survival-stats__value">22%</span>
            </div>
        </div>
    </div>
</section>