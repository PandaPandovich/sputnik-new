<?php
$header_info_links = get_field('header_info_links', 'option');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Onest:wght@100..900&display=swap"
        rel="stylesheet">
    <script src="https://api-maps.yandex.ru/v3/?apikey=60f47ff7-063f-474f-8e9a-712c74327c38&lang=ru_RU"></script>
    <?php wp_head() ?>
</head>
<header class="header">
    <div class="container">
        <div class="header__row">
            <div class="header__info">
                <div class="header__info-block">
                    <a href="/" class="header__logo">
                        <img src="<?php echo get_template_directory_uri(); ?>/build/img/logo.png" alt="logo">
                        <span class="header__logo-text">9:00–21:00, без выходных</span>
                    </a>
                </div>
                <?php if ($header_info_links): ?>
                    <div class="header__info-links">
                        <?php foreach ($header_info_links as $item):
                            $text = $item['text'] ?? '';
                            if ($text === '') {
                                continue;
                            }
                            $href = !empty($item['link']) ? $item['link'] : '#';
                        ?>
                            <a href="<?php echo esc_url($href); ?>" class="header__info-link"><?php echo esc_html($text); ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="header__menu">
                <?php echo wp_nav_menu([
                    'theme_location' => 'header_menu',
                    'container' => 'nav',
                    'container_class' => 'header__nav',
                    'menu_class' => 'menu'
                ]); ?>
            </div>
            <form class="header__search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <div class="header__search-wrap">
                    <input class="header__search-input" type="search" name="s" placeholder="Поиск"
                        value="<?php echo get_search_query(); ?>" autocomplete="off">
                    <button class="header__search-btn" type="submit" aria-label="Поиск">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="7.5" cy="7.5" r="6.1" stroke="currentColor" stroke-width="1.44" />
                            <line x1="12.6" y1="12.6" x2="16.2" y2="16.2" stroke="currentColor" stroke-width="1.44"
                                stroke-linecap="round" />
                        </svg>
                    </button>
                    <div class="header__search-results"></div>
                </div>
            </form>
            <a href="tel:+74954450120" class="header__button">Связаться</a>
            <div class="header__func">
                <!-- Мобильные кнопки -->
                <button class="header__mobile-phone" type="button" aria-label="Позвонить"
                    onclick="window.location.href='tel:+74954450120'">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.69 9.87v1.75a1.17 1.17 0 0 1-1.27 1.17 11.55 11.55 0 0 1-5.04-1.79 11.38 11.38 0 0 1-3.5-3.5A11.55 11.55 0 0 1 1.09 2.45 1.17 1.17 0 0 1 2.24 1.17h1.75a1.17 1.17 0 0 1 1.17 1.01c.07.56.21 1.1.4 1.62a1.17 1.17 0 0 1-.26 1.22l-.74.74a9.33 9.33 0 0 0 3.5 3.5l.74-.74a1.17 1.17 0 0 1 1.22-.26c.52.19 1.06.33 1.62.4a1.17 1.17 0 0 1 1.01 1.21Z"
                            stroke="#1D3658" stroke-width="1.12" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <button class="header__mobile-search" type="button" aria-label="Поиск">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <circle cx="5.83" cy="5.83" r="4.73" stroke="#1D3658" stroke-width="1.12" />
                        <line x1="9.8" y1="9.8" x2="12.6" y2="12.6" stroke="#1D3658" stroke-width="1.12"
                            stroke-linecap="round" />
                    </svg>
                </button>
                <button class="header__burger" type="button" aria-label="Меню">
                    <svg class="header__burger-open" width="14" height="8" viewBox="0 0 14 8" fill="none">
                        <line x1="0" y1="1" x2="14" y2="1" stroke="#1D3658" stroke-width="1.6" stroke-linecap="round" />
                        <line x1="0" y1="7" x2="14" y2="7" stroke="#1D3658" stroke-width="1.6" stroke-linecap="round" />
                    </svg>
                    <svg class="header__burger-close" width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <line x1="1" y1="1" x2="13" y2="13" stroke="#1D3658" stroke-width="1.26"
                            stroke-linecap="round" />
                        <line x1="13" y1="1" x2="1" y2="13" stroke="#1D3658" stroke-width="1.26"
                            stroke-linecap="round" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Мобильное меню -->
    <div class="header__mobile-menu">
        <div class="header__mobile-menu-items">
            <?php echo wp_nav_menu([
                'theme_location' => 'header_menu',
                'container' => false,
                'menu_class' => 'mobile-menu',
                'echo' => false,
            ]); ?>
        </div>
        <div class="header__mobile-menu-footer">
            <a href="tel:+74954450120" class="header__mobile-menu-cta">Записаться на консультацию</a>
            <a href="tel:+74954450120" class="header__mobile-menu-phone">+7 (495) 445 - 01 - 20</a>
            <span class="header__mobile-menu-hours">9:00–21:00, без выходных</span>
            <div class="header__mobile-menu-addresses">
                <a href="#">ул. Шереметьевская, 34</a>
                <a href="#">Алтуфьевское шоссе, 48, к.2</a>
            </div>
        </div>
    </div>

    <!-- Мобильный поиск -->
    <div class="msearch">
        <div class="msearch__header">
            <button class="msearch__back" type="button" aria-label="Назад">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M11.25 3.75L5.25 9L11.25 14.25" stroke="#1D3658" stroke-width="1.62" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <div class="msearch__field">
                <svg class="msearch__field-icon" width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <circle cx="5.83" cy="5.83" r="4.38" stroke="#1074bc" stroke-width="1.23"/>
                    <line x1="9.63" y1="9.63" x2="12.25" y2="12.25" stroke="#1074bc" stroke-width="1.23" stroke-linecap="round"/>
                </svg>
                <input class="msearch__input" type="search" placeholder="Поиск" autocomplete="off">
                <button class="msearch__clear" type="button" aria-label="Очистить">
                    <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                        <line x1="1" y1="1" x2="7" y2="7" stroke="#9BA8B5" stroke-width="1.2" stroke-linecap="round"/>
                        <line x1="7" y1="1" x2="1" y2="7" stroke="#9BA8B5" stroke-width="1.2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="msearch__body">
            <div class="msearch__results"></div>
        </div>
        <div class="msearch__footer">
            <a href="#" class="msearch__cta">Записаться на консультацию</a>
            <a href="tel:+74954450120" class="msearch__phone">+7 (495) 445 - 01 - 20</a>
            <span class="msearch__hours">9:00–21:00, без выходных</span>
            <div class="msearch__addresses">
                <a href="#">ул. Шереметьевская, 34</a>
                <a href="#">Алтуфьевское шоссе, 48, к.2</a>
            </div>
        </div>
    </div>
</header>

<body>