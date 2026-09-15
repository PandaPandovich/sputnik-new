<?php
/**
 * Создаёт форму Contact Form 7 «Отклик на вакансию» (слаг vacancy-application).
 *
 * Разметка формы написана на классах темы, поэтому вёрстка модалки не меняется.
 * Автоформатирование CF7 для этой формы отключено в functions.php — иначе
 * wpautop насыпал бы <p> внутрь разметки.
 *
 * Запуск:
 *   wp eval-file wp-content/themes/sputnik-new/tools/create-vacancy-form.php
 *
 * Повторный запуск перезаписывает содержимое и письмо формы, но сохраняет её ID,
 * поэтому выбор формы в блоке не слетает.
 *
 * @package sputnik-plus
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "Скрипт рассчитан на запуск через wp eval-file.\n" );
}

if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
	WP_CLI::error( 'Contact Form 7 не активен.' );
}

const SPUTNIK_FORM_SLUG = 'vacancy-application';

/**
 * Ссылка на политику конфиденциальности — подставляем реальный URL страницы,
 * чтобы не зашивать его руками на каждом окружении.
 */
$privacy      = get_page_by_path( 'politika-konfidencialnosti', OBJECT, 'page' );
$privacy_url  = $privacy ? get_permalink( $privacy ) : home_url( '/' );

$form_content = <<<FORM
<div class="vacancy-form__row">
	<label class="vacancy-form__field">
		<span class="vacancy-form__label">Имя и фамилия <i>*</i></span>
		[text* name class:vacancy-form__input autocomplete:name]
	</label>
	<label class="vacancy-form__field">
		<span class="vacancy-form__label">Телефон <i>*</i></span>
		[tel* phone class:vacancy-form__input autocomplete:tel placeholder "+7 (___) ___-__-__"]
	</label>
</div>

<label class="vacancy-form__field">
	<span class="vacancy-form__label">E-mail</span>
	[email email class:vacancy-form__input autocomplete:email]
</label>

<div class="vacancy-form__field">
	<span class="vacancy-form__label">Резюме</span>
	<label class="vacancy-form__file">
		[file resume limit:10mb filetypes:pdf|doc|docx]
		<span class="vacancy-form__file-button">Прикрепить файл</span>
		<span class="vacancy-form__file-name" data-vacancy-file-name>PDF или DOC, до 10&nbsp;МБ</span>
	</label>
</div>

<label class="vacancy-form__field">
	<span class="vacancy-form__label">Сопроводительное письмо</span>
	[textarea message 40x4 class:vacancy-form__input class:vacancy-form__input--area placeholder "Расскажите коротко о себе и опыте"]
</label>

<div class="vacancy-form__consent">
	[acceptance consent] Согласен на обработку персональных данных и принимаю <a href="{$privacy_url}">политику конфиденциальности</a> [/acceptance]
</div>

[hidden vacancy "Без конкретной вакансии"]

[submit class:vacancy-form__submit "Отправить отклик"]
FORM;

$mail_body = <<<BODY
Новый отклик на вакансию с сайта.

Вакансия: [vacancy]

Имя: [name]
Телефон: [phone]
E-mail: [email]

Сопроводительное письмо:
[message]

--
Отправлено со страницы [_url]
BODY;

/* -------------------------------------------------------------------------
 * Создание или обновление формы
 * ---------------------------------------------------------------------- */

$existing = get_posts(
	[
		'name'          => SPUTNIK_FORM_SLUG,
		'post_type'     => 'wpcf7_contact_form',
		'post_status'   => 'any',
		'numberposts'   => 1,
		'no_found_rows' => true,
	]
);

if ( $existing ) {
	$form   = WPCF7_ContactForm::get_instance( $existing[0]->ID );
	$action = 'обновлена';
} else {
	$form   = WPCF7_ContactForm::get_template( [ 'title' => 'Отклик на вакансию' ] );
	$action = 'создана';
}

if ( ! $form ) {
	WP_CLI::error( 'Не удалось получить объект формы.' );
}

$props         = $form->get_properties();
$props['form'] = $form_content;

$props['mail'] = array_merge(
	(array) $props['mail'],
	[
		'active'      => true,
		// Отклик может прийти и без вакансии — из баннера «Отправить резюме»,
		// поэтому [vacancy] ставим первым, а не после двоеточия.
		'subject'     => '[vacancy] — отклик с сайта',
		'recipient'   => '[_site_admin_email]',
		'body'        => $mail_body,
		// Reply-To на кандидата, чтобы отвечать прямо из почтового клиента.
		'additional_headers' => 'Reply-To: [email]',
		'attachments' => '[resume]',
		'use_html'    => false,
		'exclude_blank' => false,
	]
);

// Автоответ кандидату не настраиваем — e-mail в форме необязателен.
$props['mail_2'] = array_merge( (array) $props['mail_2'], [ 'active' => false ] );

$form->set_properties( $props );
$form->set_title( 'Отклик на вакансию' );

$id = $form->save();

if ( ! $id ) {
	WP_CLI::error( 'Не удалось сохранить форму.' );
}

// CF7 генерирует слаг из заголовка — кириллица превратилась бы в проценты,
// а по слагу мы отключаем автоформатирование в functions.php.
if ( SPUTNIK_FORM_SLUG !== get_post_field( 'post_name', $id ) ) {
	wp_update_post(
		[
			'ID'        => $id,
			'post_name' => SPUTNIK_FORM_SLUG,
		]
	);
}

WP_CLI::success( sprintf( 'Форма «Отклик на вакансию» %s: ID %d, слаг %s', $action, $id, SPUTNIK_FORM_SLUG ) );
WP_CLI::log( 'Выберите её в блоке «Открытые вакансии» → поле «Форма отклика».' );
