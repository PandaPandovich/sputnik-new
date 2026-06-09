<?php
$footer_logo = get_field('footer_logo', 'option');
$footer_phone = get_field('footer_phone', 'option');
$footer_email = get_field('footer_email', 'option');
$footer_worktime = get_field('footer_worktime', 'option');
$footer_branches = get_field('footer_branches', 'option');
$footer_links = get_field('footer_links', 'option');
?>

<footer class="footer">
    <div class="container">
        <div class="footer__heading">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="footer__logo">
                <?php if ($footer_logo): ?>
                    <?php echo wp_get_attachment_image($footer_logo, 'full', false, ['class' => 'footer__logo-img']); ?>
                <?php endif; ?>
            </a>
            <div class="footer__reviews">
                <iframe src="https://yandex.ru/sprav/widget/rating-badge/192757229546?type=rating&theme=dark"
                    width="150" height="50" frameborder="0"></iframe>
            </div>
        </div>
        <div class="footer__content">
            <div class="footer__info">
                <div class="footer__info-items">
                    <div class="footer__info-item">
                        <?php if ($footer_phone): ?>
                            <a href="tel:<?php echo esc_attr(preg_replace('/[^\d+]/', '', $footer_phone)); ?>"
                                class="footer__info-phone"><?php echo esc_html($footer_phone); ?></a>
                        <?php endif; ?>
                        <?php if ($footer_worktime): ?>
                            <p class="footer__info-time"><?php echo esc_html($footer_worktime); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="footer__info-item">
                        <?php if ($footer_email): ?>
                            <a href="mailto:<?php echo esc_attr(preg_replace('/[^\d+]/', '', $footer_email)); ?>"
                                class="footer__info-phone">
                                <?php echo esc_html($footer_email); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($footer_branches): ?>
                    <div class="footer__info-adresses">
                        <?php foreach ($footer_branches as $branch): ?>
                            <div class="footer__info-adress">
                                <a <?php echo !empty($branch['link']) ? 'href="' . esc_url($branch['link']) . '"' : ''; ?>
                                    class="footer__info-adress-link"><?php echo esc_html($branch['name']); ?></a>
                                <p class="footer__info-adress-text"><?php echo esc_html($branch['address']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="footer__navs">
                <div class="footer__nav">
                    <h4 class="footer__nav-title">О клинике</h4>
                    <?php wp_nav_menu([
                        'theme_location' => 'footer_menu_about',
                        'container' => false,
                        'menu_class' => 'footer__nav-list',
                        'fallback_cb' => false,
                        'depth' => 1,
                    ]); ?>
                </div>
                <div class="footer__nav">
                    <h4 class="footer__nav-title">Услуги и цены</h4>
                    <?php wp_nav_menu([
                        'theme_location' => 'footer_menu_services',
                        'container' => false,
                        'menu_class' => 'footer__nav-list',
                        'fallback_cb' => false,
                        'depth' => 1,
                    ]); ?>
                </div>
            </div>
        </div>
        <div class="footer__row">
            <?php if ($footer_links): ?>
                <div class="footer__links">
                    <?php foreach ($footer_links as $item):
                        $link = $item['link'];
                        if (is_array($link) && !empty($link['url'])): ?>
                            <a href="<?php echo esc_url($link['url']); ?>" <?php echo !empty($link['target']) ? 'target="' . esc_attr($link['target']) . '"' : ''; ?>><?php echo esc_html($link['title']); ?></a>
                        <?php endif;
                    endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="footer__copyrights">
                &copy; <?php echo date('Y'); ?> Ветеринарная клиника «Спутник». Все права защищены.
            </div>
        </div>
    </div>
</footer>
</body>
<?php wp_footer() ?>