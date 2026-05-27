<?php
/**
 * Превью полного контента статьи для одного поста.
 * Запуск: php preview-article.php 2612
 */

$target_post_id = (int)($argv[1] ?? 0);
if (!$target_post_id) {
    die("Укажите ID поста: php preview-article.php 2612\n");
}

// Подключаем функции из основного скрипта (без выполнения)
$stop_words = [
    'у', 'в', 'на', 'и', 'с', 'к', 'по', 'из', 'от', 'за', 'для', 'все', 'что', 'как', 'это',
    'собак', 'кошек', 'котов', 'кошки', 'собаки', 'кота', 'собаку', 'животных', 'питомца', 'питомцу',
    'причины', 'симптомы', 'лечение', 'первая', 'помощь', 'полное', 'руководство',
    'нужно', 'знать', 'мифы', 'реальность', 'операция', 'восстановление',
    'такое', 'бороться', 'этим', 'спасение', 'сложной', 'ситуации',
    'здоровой', 'жизни', 'домашних',
];

$posts = [
    2590 => 'Пиометра у собак',
    2591 => 'Пупочная грыжа у собак',
    2592 => 'Уретростомия у котов: спасение в сложной ситуации',
    2593 => 'Бурсит у собак: все, что нужно знать',
    2594 => 'Ожоги у собак: причины, симптомы, первая помощь и лечение',
    2595 => 'Астма у кошек: полное руководство к здоровой жизни питомца',
    2596 => 'Мастэктомия у собак: причины, операция и восстановление',
    2597 => 'Промежностная грыжа у собак: мифы и реальность',
    2598 => 'Что такое спленэктомия?',
    2599 => 'Паховая грыжа у собак: причины, симптомы и лечение',
    2600 => 'Эктопия мочеточника у собак: что это и как с этим бороться?',
    2601 => 'Заворот века у кошек: причины, симптомы и лечение',
    2602 => 'Брахицефалический синдром',
    2603 => 'Ожирение у кошек',
    2604 => 'Сердечная недостаточность у собак',
    2605 => 'Стоматологические заболевания у кошек',
    2606 => 'Дерматит у собак',
    2607 => 'Защита вашего питомца',
    2608 => 'Мочекаменная болезнь у кошек',
    2609 => 'Панкреатит у кошек',
    2610 => 'Артрит у собак',
    2611 => 'Аллергия у собак',
    2612 => 'Заворот желудка у собак',
    2613 => 'Эпилепсия у собак',
    2614 => 'Хроническая болезнь почек у кошек',
    2615 => 'Крипторхизм у собак',
    2616 => 'Ложная беременность у собак',
    2617 => 'Инородное тело у кошки в кишечнике',
    2618 => 'Демодекоз у собак',
    2619 => 'Гипертиреоз у кошек',
    2620 => 'Липидоз печени у кошек',
];

if (!isset($posts[$target_post_id])) {
    die("Пост #{$target_post_id} не найден в списке\n");
}

function extract_keywords(string $text, array $stop_words): array {
    $text = strip_tags($text);
    $text = mb_strtolower($text, 'UTF-8');
    preg_match_all('/[\p{Cyrillic}\p{Latin}]+/u', $text, $matches);
    $words = $matches[0] ?? [];
    $keywords = [];
    foreach ($words as $word) {
        if (mb_strlen($word, 'UTF-8') >= 4 && !in_array($word, $stop_words, true)) {
            $keywords[] = $word;
        }
    }
    return array_unique($keywords);
}

function find_keyword_matches(array $post_keywords, array $service_keywords): array {
    $matches = [];
    foreach ($post_keywords as $pk) {
        foreach ($service_keywords as $sk) {
            if ($pk === $sk) { $matches[] = $pk; continue; }
            if (mb_strlen($pk, 'UTF-8') >= 5 && mb_strlen($sk, 'UTF-8') >= 5) {
                if (mb_strpos($pk, $sk) !== false || mb_strpos($sk, $pk) !== false) {
                    $matches[] = "$pk~$sk";
                }
            }
        }
    }
    return array_unique($matches);
}

function clean_text(string $text): string {
    $text = str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>', '</li>'], "\n", $text);
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/[ \t]+/', ' ', $text);
    $text = preg_replace('/\n{3,}/', "\n\n", $text);
    return trim($text);
}

function nl2br_wp(string $text): string {
    return str_replace("\n", "<br>", $text);
}

function html_to_gutenberg(string $html): string {
    $html = str_replace(["\r\n", "\r"], "\n", $html);
    $blocks = [];
    $pattern = '/(<h[2-3][^>]*>.*?<\/h[2-3]>|<[uo]l[^>]*>.*?<\/[uo]l>)/uis';
    $parts = preg_split($pattern, $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    foreach ($parts as $part) {
        $part = trim($part);
        if (empty($part)) continue;
        if (preg_match('/<h2[^>]*>(.*?)<\/h2>/uis', $part, $m)) {
            $title = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8'));
            if (!empty($title)) $blocks[] = "<!-- wp:heading -->\n<h2>{$title}</h2>\n<!-- /wp:heading -->";
        } elseif (preg_match('/<h3[^>]*>(.*?)<\/h3>/uis', $part, $m)) {
            $title = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8'));
            if (!empty($title)) $blocks[] = "<!-- wp:heading {\"level\":3} -->\n<h3>{$title}</h3>\n<!-- /wp:heading -->";
        } elseif (preg_match('/<ul[^>]*>(.*?)<\/ul>/uis', $part, $m)) {
            preg_match_all('/<li[^>]*>(.*?)<\/li>/uis', $m[1], $items);
            if (!empty($items[1])) {
                $clean = [];
                foreach ($items[1] as $item) {
                    $item = trim(html_entity_decode(strip_tags($item, '<strong><em><b><i>'), ENT_QUOTES, 'UTF-8'));
                    if (!empty($item)) $clean[] = "<li>{$item}</li>";
                }
                if (!empty($clean)) $blocks[] = "<!-- wp:list -->\n<ul>" . implode("\n", $clean) . "</ul>\n<!-- /wp:list -->";
            }
        } elseif (preg_match('/<ol[^>]*>(.*?)<\/ol>/uis', $part, $m)) {
            preg_match_all('/<li[^>]*>(.*?)<\/li>/uis', $m[1], $items);
            if (!empty($items[1])) {
                $clean = [];
                foreach ($items[1] as $item) {
                    $item = trim(html_entity_decode(strip_tags($item, '<strong><em><b><i>'), ENT_QUOTES, 'UTF-8'));
                    if (!empty($item)) $clean[] = "<li>{$item}</li>";
                }
                if (!empty($clean)) $blocks[] = "<!-- wp:list {\"ordered\":true} -->\n<ol>" . implode("\n", $clean) . "</ol>\n<!-- /wp:list -->";
            }
        } else {
            $text = trim(html_entity_decode(strip_tags($part, '<strong><em><b><i>'), ENT_QUOTES, 'UTF-8'));
            $text = preg_replace('/[ \t]+/', ' ', $text);
            if (!empty($text)) {
                foreach (preg_split('/\n{2,}/', $text) as $p) {
                    $p = trim($p);
                    if (!empty($p)) $blocks[] = "<!-- wp:paragraph -->\n<p>" . str_replace("\n", "<br>", $p) . "</p>\n<!-- /wp:paragraph -->";
                }
            }
        }
    }
    return implode("\n\n", $blocks);
}

function collect_cards(array $row): array {
    $cards = [];
    for ($i = 0; $i <= 11; $i++) {
        $title = $row["card_repitor_{$i}_card_title"] ?? '';
        $desc = $row["card_repitor_{$i}_card_desc"] ?? '';
        if (!empty($title) || !empty($desc)) {
            $cards[] = ['title' => $title, 'desc' => $desc];
        }
    }
    return $cards;
}

function collect_cards2(array $row): array {
    $cards = [];
    for ($i = 0; $i <= 17; $i++) {
        $title = $row["card_repitor2_{$i}_card_title2"] ?? '';
        $desc = $row["card_repitor2_{$i}_card_desc2"] ?? '';
        if (!empty($title) || !empty($desc)) {
            $cards[] = ['title' => $title, 'desc' => $desc];
        }
    }
    return $cards;
}

function collect_faq(array $row): array {
    $faq = [];
    for ($i = 0; $i <= 15; $i++) {
        $q = $row["questions_{$i}_question"] ?? '';
        $a = $row["questions_{$i}_answer"] ?? '';
        if (!empty($q) && !empty($a)) {
            $faq[] = ['question' => $q, 'answer' => $a];
        }
    }
    return $faq;
}

function build_article_content(array $row, string $post_title): string {
    $sections = [];

    // 1. text_selected — основной полный текст (идёт первым)
    $text_selected = trim($row['text_selected'] ?? '');
    if (!empty($text_selected)) {
        $sections[] = html_to_gutenberg($text_selected);
    }

    // 2. Вступительный блок
    $intro_parts = [];
    $usluga_desc = clean_text($row['usluga_desc'] ?? '');
    if (!empty($usluga_desc)) {
        $intro_parts[] = $usluga_desc;
    }
    $bottom_title = $row['bottom_title'] ?? '';
    if (!empty($bottom_title)) {
        $bt = preg_replace('/<h2>.*?<\/h2>/uis', '', $bottom_title);
        $bt = clean_text($bt);
        if (!empty($bt)) {
            $intro_parts[] = $bt;
        }
    }
    if (!empty($intro_parts)) {
        $intro = implode("\n\n", $intro_parts);
        $sections[] = "<!-- wp:paragraph -->\n<p>" . nl2br_wp($intro) . "</p>\n<!-- /wp:paragraph -->";
    }

    $cards = collect_cards($row);
    if (!empty($cards)) {
        $cards_html = '';
        foreach ($cards as $card) {
            $ct = clean_text($card['title']);
            $cd = clean_text($card['desc']);
            if (empty($ct) && empty($cd)) continue;
            if (!empty($ct)) {
                $cards_html .= "<!-- wp:heading {\"level\":3} -->\n<h3>{$ct}</h3>\n<!-- /wp:heading -->\n\n";
            }
            if (!empty($cd)) {
                $cards_html .= "<!-- wp:paragraph -->\n<p>" . nl2br_wp($cd) . "</p>\n<!-- /wp:paragraph -->\n\n";
            }
        }
        if (!empty($cards_html)) $sections[] = $cards_html;
    }

    $cards2 = collect_cards2($row);
    if (!empty($cards2)) {
        $c2html = '';
        foreach ($cards2 as $card) {
            $ct = clean_text($card['title']);
            $cd = clean_text($card['desc']);
            if (empty($ct) && empty($cd)) continue;
            if (!empty($ct)) {
                $c2html .= "<!-- wp:heading {\"level\":3} -->\n<h3>{$ct}</h3>\n<!-- /wp:heading -->\n\n";
            }
            if (!empty($cd)) {
                $c2html .= "<!-- wp:paragraph -->\n<p>" . nl2br_wp($cd) . "</p>\n<!-- /wp:paragraph -->\n\n";
            }
        }
        if (!empty($c2html)) $sections[] = $c2html;
    }

    $faq = collect_faq($row);
    if (!empty($faq)) {
        $faq_html = "<!-- wp:heading -->\n<h2>Часто задаваемые вопросы</h2>\n<!-- /wp:heading -->\n\n";
        foreach ($faq as $qa) {
            $q = clean_text($qa['question']);
            $a = clean_text($qa['answer']);
            if (empty($q) || empty($a)) continue;
            $faq_html .= "<!-- wp:heading {\"level\":3} -->\n<h3>{$q}</h3>\n<!-- /wp:heading -->\n\n";
            $faq_html .= "<!-- wp:paragraph -->\n<p>" . nl2br_wp($a) . "</p>\n<!-- /wp:paragraph -->\n\n";
        }
        $sections[] = $faq_html;
    }

    return implode("\n\n", $sections);
}

// --- Чтение CSV ---
$csv_path = __DIR__ . '/uslugi-export-2026-may-26-2216.csv';
$f = fopen($csv_path, 'r');
$headers = fgetcsv($f, 0, ',', '"', '');
$headers[0] = preg_replace("/^\xEF\xBB\xBF/", '', $headers[0]);

$csv_rows = [];
while (($data = fgetcsv($f, 0, ',', '"', '')) !== false) {
    $row = [];
    foreach ($headers as $idx => $header) {
        $row[$header] = $data[$idx] ?? '';
    }
    $sid = $row['id'] ?? '';
    if (!empty($sid)) $csv_rows[$sid] = $row;
}
fclose($f);

// --- Найти лучшее совпадение ---
$post_title = $posts[$target_post_id];
$pk = extract_keywords($post_title, $stop_words);
$best = null;

foreach ($csv_rows as $service_id => $row) {
    $st = trim($row['Title'] ?? '');
    if (empty($st)) continue;

    $title_keywords = extract_keywords($st, $stop_words);
    $title_matches = find_keyword_matches($pk, $title_keywords);

    $strength = 0;
    if (!empty($title_matches)) $strength += count($title_matches) * 10;

    $norm_post = mb_strtolower(trim($post_title), 'UTF-8');
    $norm_service = mb_strtolower(trim($st), 'UTF-8');
    $norm_post_base = preg_replace('/:.+$/', '', $norm_post);
    $norm_service_base = preg_replace('/:.+$/', '', $norm_service);

    if ($norm_post === $norm_service || $norm_post_base === $norm_service_base) {
        $strength += 100;
    } elseif (mb_strpos($norm_service, $norm_post_base) !== false) {
        $strength += 50;
    } elseif (mb_strpos($norm_post, $norm_service_base) !== false) {
        $strength += 50;
    }

    if ($strength > 0 && (!$best || $strength > $best['strength'])) {
        $best = ['service_id' => $service_id, 'title' => $st, 'strength' => $strength];
    }
}

if (!$best) {
    die("Совпадений не найдено для поста #{$target_post_id}\n");
}

echo "ПОСТ #{$target_post_id}: {$post_title}\n";
echo "  <- Услуга ID {$best['service_id']}: \"{$best['title']}\"\n\n";

$content = build_article_content($csv_rows[$best['service_id']], $post_title);

echo "=== ПОЛНЫЙ HTML КОНТЕНТ ===\n\n";
echo $content;
echo "\n\n=== ТЕКСТОВОЕ ПРЕДСТАВЛЕНИЕ ===\n\n";
echo strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $content));
