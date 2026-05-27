/**
 * Аккордеон для блока услуг отделения.
 */
document.querySelectorAll( '.dept-services__item-header' ).forEach( ( button ) => {
    button.addEventListener( 'click', () => {
        const item = button.closest( '.dept-services__item' );
        const isActive = item.classList.contains( 'is-active' );

        // Закрываем все элементы в этом блоке
        item.closest( '.dept-services__list' )
            .querySelectorAll( '.dept-services__item' )
            .forEach( ( el ) => el.classList.remove( 'is-active' ) );

        // Открываем текущий, если он был закрыт
        if ( ! isActive ) {
            item.classList.add( 'is-active' );
        }
    });
});
