/**
 * Вакансии: фильтрация по направлению, модальное окно с описанием
 * и форма отклика внутри того же окна.
 */
( function () {
	document.querySelectorAll( '[data-vacancies]' ).forEach( function ( root ) {
		initFilters( root );
		initModal( root );
	} );

	function initFilters( root ) {
		const filters = root.querySelectorAll( '[data-vacancy-filter]' );
		const cards = root.querySelectorAll( '[data-vacancy-category]' );
		const empty = root.querySelector( '[data-vacancies-empty]' );
		if ( ! filters.length ) return;

		filters.forEach( function ( filter ) {
			filter.addEventListener( 'click', function () {
				const type = filter.dataset.vacancyFilter;

				filters.forEach( function ( f ) {
					const active = f === filter;
					f.classList.toggle( 'is-active', active );
					f.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
				} );

				let visible = 0;
				cards.forEach( function ( card ) {
					const show = type === 'all' || card.dataset.vacancyCategory === type;
					card.hidden = ! show;
					if ( show ) visible += 1;
				} );

				if ( empty ) empty.hidden = visible !== 0;
			} );
		} );
	}

	function initModal( root ) {
		const modal = root.querySelector( '[data-vacancy-modal]' );
		const detailView = root.querySelector( '[data-vacancy-modal-body]' );
		const cards = root.querySelectorAll( '[data-vacancy-index]' );
		if ( ! modal || ! detailView || ! cards.length ) return;

		const closeBtn = modal.querySelector( '.vacancy-modal__close' );
		const formView = modal.querySelector( '[data-vacancy-form-view]' );
		const formWrap = modal.querySelector( '[data-vacancy-form-wrap]' );
		// Разметку формы отдаёт Contact Form 7 — цепляемся за её классы и поля.
		const form = modal.querySelector( 'form.vacancy-form' );
		const formTitle = modal.querySelector( '[data-vacancy-form-title]' );
		const formInput = modal.querySelector( 'input[name="vacancy"]' );
		const success = modal.querySelector( '[data-vacancy-success]' );
		const fileInput = modal.querySelector( 'input[type="file"]' );
		const fileName = modal.querySelector( '[data-vacancy-file-name]' );
		const filePlaceholder = fileName ? fileName.innerHTML : '';
		const backBtn = modal.querySelector( '[data-vacancy-back]' );
		const formHeading = modal.querySelector( '[data-vacancy-form-heading]' );
		const headingForVacancy = formHeading ? formHeading.textContent : '';

		let lastTrigger = null;

		function showDetail() {
			if ( formView ) formView.hidden = true;
			detailView.hidden = false;
			detailView.scrollTop = 0;
		}

		function showForm( title ) {
			if ( ! formView ) return;

			detailView.hidden = true;
			formView.hidden = false;
			formView.scrollTop = 0;

			// Отклик из баннера идёт без вакансии — подставляем значение,
			// зашитое в скрытое поле CF7 по умолчанию.
			const label = title || ( formInput ? formInput.defaultValue : '' );

			// В модалке показываем только реальную вакансию, служебную пометку — нет.
			if ( formTitle ) {
				formTitle.textContent = title || '';
				formTitle.hidden = ! title;
			}

			// В письмо пометка всё-таки уходит — чтобы отличать общий отклик.
			if ( formInput ) formInput.value = label;

			// «Отклик на вакансию» рядом с «Без конкретной вакансии» читался бы странно.
			if ( formHeading ) {
				formHeading.textContent = title
					? headingForVacancy
					: formHeading.dataset.headingGeneral;
			}

			// Возвращаться некуда, если описание вакансии не открывали.
			if ( backBtn ) backBtn.hidden = ! title;

			// Возврат к форме после успешной отправки предыдущего отклика.
			if ( success ) success.hidden = true;
			if ( formWrap ) formWrap.hidden = false;
		}

		/**
		 * Показывает модалку. Содержимое к этому моменту уже подготовлено.
		 */
		function showModal( trigger, label ) {
			lastTrigger = trigger;
			modal.setAttribute( 'aria-label', label );

			modal.hidden = false;
			// Принудительный reflow, иначе переход с display:none не анимируется.
			void modal.offsetWidth;
			modal.classList.add( 'is-open' );
			document.body.classList.add( 'menu-open' );
			if ( closeBtn ) closeBtn.focus();
		}

		function resetForm() {
			if ( form ) {
				// wpcf7.reset заодно снимает подсветку ошибок и плашку ответа,
				// иначе они остались бы от предыдущей вакансии.
				if ( window.wpcf7 && typeof window.wpcf7.reset === 'function' ) {
					window.wpcf7.reset( form );
				} else {
					form.reset();
				}
			}
			if ( fileName ) fileName.innerHTML = filePlaceholder;
		}

		function open( index, trigger ) {
			const detail = root.querySelector( '[data-vacancy-detail="' + index + '"]' );
			if ( ! detail ) return;

			detailView.replaceChildren( detail.cloneNode( true ) );
			// Иначе следующая вакансия откроется с прокруткой от предыдущей.
			detailView.scrollTop = 0;
			showDetail();
			resetForm();

			const title = detail.querySelector( '.vacancy-detail__title' );
			showModal( trigger, title ? title.textContent : 'Описание вакансии' );
		}

		/**
		 * Отклик без конкретной вакансии — кнопка «Отправить резюме» в баннере.
		 */
		function openForm( trigger ) {
			resetForm();
			showForm( '' );
			showModal( trigger, 'Отправить резюме' );
		}

		function close() {
			modal.classList.remove( 'is-open' );
			document.body.classList.remove( 'menu-open' );
			if ( lastTrigger ) lastTrigger.focus();

			const done = function () {
				modal.hidden = true;
				detailView.replaceChildren();
			};
			modal.addEventListener( 'transitionend', done, { once: true } );
			// Страховка, если transitionend не придёт (reduced motion и т.п.).
			setTimeout( done, 400 );
		}

		cards.forEach( function ( card ) {
			card.addEventListener( 'click', function () {
				open( card.dataset.vacancyIndex, card );
			} );
		} );

		// Кнопка «Откликнуться» живёт в клонированной разметке — ловим делегированием.
		detailView.addEventListener( 'click', function ( e ) {
			const apply = e.target.closest( '[data-vacancy-apply]' );
			if ( ! apply ) return;
			showForm( apply.dataset.vacancyTitle );
		} );

		if ( backBtn ) backBtn.addEventListener( 'click', showDetail );

		// Кнопки вне блока («Отправить резюме» в баннере) открывают ту же форму.
		// Модалка на странице одна, поэтому вешаем обработчик только на первый блок.
		if ( root === document.querySelector( '[data-vacancies]' ) ) {
			document.querySelectorAll( '[data-vacancy-open-form]' ).forEach( function ( el ) {
				el.addEventListener( 'click', function ( e ) {
					// Без блока вакансий кнопка осталась бы обычной ссылкой.
					e.preventDefault();
					openForm( el );
				} );
			} );
		}

		if ( fileInput && fileName ) {
			fileInput.addEventListener( 'change', function () {
				const file = fileInput.files && fileInput.files[ 0 ];
				fileName.textContent = file ? file.name : '';
				if ( ! file ) fileName.innerHTML = filePlaceholder;
			} );
		}

		if ( form ) {
			// Отправляет и валидирует Contact Form 7 — нам остаётся показать
			// свой экран благодарности вместо штатной зелёной плашки.
			// Ошибки валидации и недоставку CF7 выводит сам.
			document.addEventListener( 'wpcf7mailsent', function ( e ) {
				if ( e.detail.contactFormId !== formId( form ) ) return;

				if ( formWrap ) formWrap.hidden = true;
				if ( success ) success.hidden = false;
				formView.scrollTop = 0;
				if ( fileName ) fileName.innerHTML = filePlaceholder;
			} );
		}

		/**
		 * ID формы CF7 кладёт в скрытое поле _wpcf7 внутри самой формы.
		 */
		function formId( el ) {
			const input = el.querySelector( 'input[name="_wpcf7"]' );
			return input ? Number( input.value ) : null;
		}

		modal.querySelectorAll( '[data-vacancy-close]' ).forEach( function ( el ) {
			el.addEventListener( 'click', close );
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && ! modal.hidden ) close();
		} );
	}
} )();
