<?php
wp_enqueue_script_module( '@cp/archivePagePlugin' );

$context = [
    'nonce'             => wp_create_nonce('wp_rest'),
    "result"            => [],
    "fileData"          => '',
    "data"              => [],
    "ids"               => [],
    "preloaderWidth"    => "0%",
    "idsArchive"        => [],
    "preloaderText"     => '0%',
    "loadedHtml"        => '',
    "countItem"         => 1,
    "nameInput"         => __( 'Select file CSV', 'archive' )
];

echo wp_create_nonce('wp_rest');
?>

<div class="archive-page" data-wp-interactive="archivePagePlugin" <?php echo wp_interactivity_data_wp_context( $context ); ?>>

    <form class="archive-page__form" data-wp-on--submit="actions.checkFile">

        <div class="archive-page__select-file">

            <label for="fileCSV" data-wp-text="context.nameInput"></label>

            <input type="file"
                   accept=".csv"
                   name="fileCSV"
                   class="archive-page__input"
                   data-wp-on--input="actions.getFile"
            >

        </div><!-- .archive-page__select-file  -->

        <button type="submit" class="archive-page__button button"><?php echo __( 'Start', 'archive' ); ?></button>

    </form><!-- .archive-page__form -->

    <a class="archive-page__download--file" href="<?php echo plugins_url('../files/csv-example.csv', __FILE__); ?>" download><?php echo __( 'Download example file', 'archive' ); ?></a>

    <div class="archive-page__preloader">

        <div class="archive-page__preloader-text" data-wp-text="context.preloaderText"></div>

        <div class="archive-page__preloader-line" data-wp-style--width="context.preloaderWidth"></div>

    </div><!-- .archive-page__form -->

    <div class="archive-page__table widefat">

        <div class="archive-page__item-head">

            <div class="archive-page__item">

                <div class="archive-page__item--count"><?php echo __( 'Count', 'archive' ); ?></div>

                <div class="archive-page__item--id"><?php echo __( 'Post ID', 'archive' ); ?></div>

                <div class="archive-page__item--url"><?php echo __( 'Url', 'archive' ); ?></div>

                <div class="archive-page__item--redirect"><?php echo __( 'Redirect Url', 'archive' ); ?></div>

            </div><!-- .archive-page__item -->

        </div><!-- .archive-page__item-head -->

        <div class="archive-page__item-body">

            <template data-wp-each="context.result">

                <div class="archive-page__item">

                    <div class="archive-page__item--count" data-wp-text="context.item.count"></div>

                    <div class="archive-page__item--id" data-wp-text="context.item.post_id"></div>

                    <div class="archive-page__item--url">

                        <a data-wp-bind--href="context.item.url" data-wp-text="context.item.url" target="_blank"></a>

                    </div>

                    <div class="archive-page__item--redirect" data-wp-text="context.item.redirect_url"></div>

                </div><!-- .archive-page__item -->

            </template>

        </div><!-- .archive-page__item-body -->

    </div><!-- .archive-page__table -->

</div><!-- .archive-page -->

