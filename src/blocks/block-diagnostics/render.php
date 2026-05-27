<?php
/**
 * Блок «Диагностика» — этапы прохождения диагностики.
 */

$title = get_field('title') ?: 'Как проходит диагностика?';
$steps = get_field('steps');
?>

<section class="diagnostics">
    <div class="container">
        <div class="diagnostics__card">
            <h2 class="diagnostics__title"><?php echo esc_html($title); ?></h2>
            <?php if ($steps) : ?>
                <div class="diagnostics__steps">
                    <?php foreach ($steps as $i => $step) : ?>
                        <div class="diagnostics__step">
                            <span class="diagnostics__step-num"><?php echo $i + 1; ?></span>
                            <h4 class="diagnostics__step-title"><?php echo esc_html($step['step_title']); ?></h4>
                            <p class="diagnostics__step-text"><?php echo esc_html($step['step_text']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
