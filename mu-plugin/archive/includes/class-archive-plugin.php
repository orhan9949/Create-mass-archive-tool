<?php

class Archive_Plugin {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
        add_action( 'init', [ $this, 'register_post_status' ] );
        add_filter( 'post_row_actions', [ $this, 'add_actions' ], 20, 2 );
        add_action( 'admin_init', [ $this, 'change_status' ] );
        add_filter( 'cloudflare_purge_url_actions', [ $this, 'add_cloudflare_purge_url_action' ], 10, 2);
        add_action( 'admin_footer-post.php', [ $this, 'add_move_to_archive_button' ] );

    }

    /**
     * Loads the plugin's translated strings.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'archive', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages' );
    }

    /**
     * Register post status.
     *
     * @return void
     */
    public function register_post_status() {
        register_post_status( 'archive', array(
            'label'                     => __( 'Archived', 'archive' ),
            'public'                    => false,
            'exclude_from_search'       => true,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Archived <span class="count">(%s)</span>', 'Archived <span class="count">(%s)</span>', 'archive' ),
        ) );
    }

    /**
     * Add actions to post row.
     *
     * @param $actions
     * @param $post
     * @return mixed
     */
    public function add_actions( $actions, $post ) {

        if ( ! current_user_can( 'edit_others_posts' ) ) {
            return $actions;
        }

        $url_template = 'post.php?post=%s&action=%s&status=%s';

        if ( in_array( $post->post_status, [ 'publish', 'draft' ] ) ) {

            $url = admin_url( sprintf(
                $url_template,
                $post->ID,
                'archive_change_status',
                'archive'
            ) );

            $url = add_query_arg( '_wpnonce', wp_create_nonce( 'action' ), $url );

            $actions['archive'] = sprintf(
                '<a href="%s">%s</a>',
                esc_url( $url ),
                __( 'Archive', 'archive' )
            );

        } elseif ( 'archive' == $post->post_status ) {

            $url_draft = admin_url( sprintf(
                $url_template,
                $post->ID,
                'archive_change_status',
                'draft'
            ) );

            $url_publish = admin_url( sprintf(
                $url_template,
                $post->ID,
                'archive_change_status',
                'publish'
            ) );

            $url_draft = add_query_arg( '_wpnonce', wp_create_nonce( 'action' ), $url_draft );
            $url_publish = add_query_arg( '_wpnonce', wp_create_nonce( 'action' ), $url_publish );

            $actions['archive_change_status_draft'] = sprintf(
                '<a href="%s">%s</a>',
                 esc_url( $url_draft ),
                 __( 'Restore to Draft', 'archive' )
            );

            $actions['archive_change_status_publish'] = sprintf(
                '<a href="%s">%s</a>',
                 esc_url( $url_publish ),
                 __( 'Restore to Publish', 'archive' )
            );

        }

        return $actions;
    }

    /**
     * Change status callback.
     *
     * @return void
     */
    public function change_status() {
        global $wpdb;

        if ( ! current_user_can( 'edit_others_posts' ) ) {
            return;
        }

        if ( isset ( $_GET['action'] ) && 'archive_change_status' == $_GET['action'] ) {

            $post_id = $_GET['post'];

            $wpdb->update(
                $wpdb->prefix . 'posts',
                [ 'post_status' => $_GET['status'] ],
                [ 'ID' => $post_id ]
            );

            wp_cache_delete( $post_id, 'posts' );

            if ( function_exists( 'spinupwp_purge_post' ) ) {
                spinupwp_purge_post( $post_id );
            }

            do_action( 'archive_change_status', $post_id );

            $link = sprintf( 'edit.php?post_type=%s', get_post_type( $post_id ) );

            if ( 'edit' == $_GET['return'] ) {
                $link = sprintf( 'post.php?post=%s&action=edit', $post_id );
            }

            wp_redirect( admin_url( $link ) );

            exit;
        }
    }

    /**
     * Add action to cloudflare purge url list.
     *
     * @param $action_items
     * @return mixed
     */
    public function add_cloudflare_purge_url_action( $actions ) {
        $actions[] = 'archive_change_status';

        return $actions;
    }

    /**
     * Add Move to Archive button to the Edit Post screen.
     *
     * @return void
     */
    public function add_move_to_archive_button() {

        if ( ! current_user_can( 'edit_others_posts' ) ) {
            return;
        }

        $post_id = $_GET['post'];

        $post_status = get_post_status( $post_id );

        if ( ! in_array( $post_status, [ 'publish', 'draft' ] ) ) {
            return;
        }

        $url_template = sprintf(
            'post.php?post=%s&action=%s&status=%s&return=edit',
            $post_id,
            'archive_change_status',
            'archive'
        );

        $url = admin_url( $url_template );

        $button_template = sprintf(
            '<a class="%s" href="%s">%s</a>',
            'components-button editor-post-trash is-next-40px-default-size is-secondary',
            esc_url( $url ),
            __( 'Move to Archive', 'archive' )
        );

        ?>
            <script type="text/javascript">
                window.onload = () => document.querySelector('.editor-post-trash').insertAdjacentHTML('beforebegin', '<?php echo $button_template; ?>');
            </script>
        <?php
    }
}

new Archive_Plugin;