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
                    <h4 class="footer__nav-title">Цены и услуги</h4>
                    <?php wp_nav_menu([
                        'theme_location' => 'footer_menu_about',
                        'container' => false,
                        'menu_class' => 'footer__nav-list',
                        'fallback_cb' => false,
                        'depth' => 1,
                    ]); ?>
                </div>
                <div class="footer__nav">
                    <h4 class="footer__nav-title">Отделения</h4>
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

<?php $privacy_url = function_exists('get_privacy_policy_url') ? get_privacy_policy_url() : ''; ?>
<div class="cookie-banner" id="cookie-banner" role="dialog" aria-live="polite"
    aria-label="Уведомление об использовании cookie" hidden>
    <div class="container">
        <div class="cookie-banner__inner">
            <p class="cookie-banner__text">
                Мы используем файлы cookie и обрабатываем данные о посещении сайта,
                чтобы улучшить его работу и сделать удобнее для вас. Продолжая пользоваться сайтом,
                вы даёте согласие на обработку cookie и персональных данных в соответствии
                с Федеральным законом № 152-ФЗ
                <?php if ($privacy_url): ?>
                    и <a href="<?php echo esc_url($privacy_url); ?>" class="cookie-banner__link">Политикой
                        конфиденциальности</a>.
                <?php else: ?>
                    .
                <?php endif; ?>
            </p>
            <div class="cookie-banner__actions">
                <button type="button" class="cookie-banner__btn cookie-banner__btn--decline"
                    data-cookie-action="decline">Отклонить</button>
                <button type="button" class="cookie-banner__btn cookie-banner__btn--accept"
                    data-cookie-action="accept">Принять</button>
            </div>
        </div>
    </div>
</div>
</body>
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function (m, e, t, r, i, k, a) {
        m[i] = m[i]function() { (m[i].a = m[i].a[]).push(arguments) };
        m[i].l = 1 * new Date();
        for (var j = 0; j < document.scripts.length; j++) { if (document.scripts[j].src === r) { return; } }
        k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
    })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');

    ym(49604095, 'init', { webvisor: true, clickmap: true, referrer: document.referrer, url: location.href, accurateTrackBounce: true, trackLinks: true });
</script>
<noscript>
    <div><img src="https://mc.yandex.ru/watch/49604095" style="position:absolute; left:-9999px;" alt="" /></div>
</noscript>
<!-- /Yandex.Metrika counter -->
<script src="//cdn.callibri.ru/callibri.js" type="text/javascript" charset="utf-8" defer></script>
<?php wp_footer() ?>