<?php
/**
 * Блок «Документы» — сетка плиток с иконкой, названием, размером и ссылкой.
 */

/**
 * Определяет тип документа по расширению имени файла.
 *
 * @param string $filename Имя файла.
 * @return array{type:string,label:string} Класс-модификатор и текстовая метка.
 */
if ( ! function_exists( 'sputnik_document_ext' ) ) {
    function sputnik_document_ext( $filename ) {
        $ext = strtolower( pathinfo( (string) $filename, PATHINFO_EXTENSION ) );

        $map = array(
            'pdf'  => 'pdf',
            'doc'  => 'doc',
            'docx' => 'doc',
            'rtf'  => 'doc',
            'odt'  => 'doc',
            'xls'  => 'xls',
            'xlsx' => 'xls',
            'csv'  => 'xls',
            'ods'  => 'xls',
            'ppt'  => 'ppt',
            'pptx' => 'ppt',
            'odp'  => 'ppt',
            'zip'  => 'zip',
            'rar'  => 'zip',
            '7z'   => 'zip',
            'jpg'  => 'img',
            'jpeg' => 'img',
            'png'  => 'img',
            'gif'  => 'img',
            'webp' => 'img',
            'svg'  => 'img',
        );

        $type  = isset( $map[ $ext ] ) ? $map[ $ext ] : 'file';
        $label = $ext ? strtoupper( $ext ) : 'FILE';

        return array(
            'type'  => $type,
            'label' => $label,
        );
    }
}

$title = get_field( 'title' );
$items = get_field( 'items' );

if ( ! $title && ! $items ) {
    return;
}
?>

<section class="documents">
    <div class="container">
        <?php
        if ( function_exists( 'sputnik_plus_breadcrumbs' ) ) {
            sputnik_plus_breadcrumbs( array(
                array( 'url' => home_url( '/' ), 'title' => 'Главная' ),
                array( 'title' => get_the_title() ),
            ) );
        }
        ?>

        <?php if ( $title ) : ?>
            <h2 class="documents__title"><?php echo esc_html( $title ); ?></h2>
        <?php endif; ?>

        <?php if ( $items ) : ?>
            <div class="documents__grid">
                <?php
                foreach ( $items as $item ) :
                    $file = isset( $item['file'] ) ? $item['file'] : null;

                    if ( ! $file || empty( $file['url'] ) ) {
                        continue;
                    }

                    $filename = ! empty( $file['filename'] ) ? $file['filename'] : ( ! empty( $file['title'] ) ? $file['title'] : $file['url'] );
                    $meta     = sputnik_document_ext( $filename );
                    $name     = ! empty( $item['title_override'] ) ? $item['title_override'] : $filename;
                    $size     = ( isset( $file['filesize'] ) && $file['filesize'] ) ? size_format( (int) $file['filesize'] ) : '';
                    ?>
                    <a class="documents__item" href="<?php echo esc_url( $file['url'] ); ?>" download>
                        <span class="documents__icon documents__icon--<?php echo esc_attr( $meta['type'] ); ?>">
                            <?php echo esc_html( $meta['label'] ); ?>
                        </span>
                        <span class="documents__body">
                            <span class="documents__name"><?php echo esc_html( $name ); ?></span>
                            <?php if ( $size ) : ?>
                                <span class="documents__meta"><?php echo esc_html( $meta['label'] . ' · ' . $size ); ?></span>
                            <?php endif; ?>
                        </span>
                        <svg class="documents__download" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M10 3v9m0 0 3.5-3.5M10 12 6.5 8.5M4 15h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
