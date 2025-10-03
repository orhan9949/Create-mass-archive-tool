<?php

class Archive_Plugin_Page{

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'page_assets']);
        add_action('rest_api_init', [$this, 'reg_api_archive_page']);
    }

    /**
     * Add admin menu
     *
     * @return void
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tools.php',
            'Archive Page',
            'Archive Page',
            'manage_options',
            'archive_page_slug',
            [$this, 'admin_page'],
            'dashicons-admin-generic',
            40
        );
    }

    /**
     * Admin page
     *
     * @return void
     */
    public function admin_page() {

        $template_path = dirname(__DIR__) . '/pattern/archive-page.php';

        if (file_exists($template_path)) {
            include $template_path;
        }

    }

    /**
     * Page assets
     *
     * @param $hook
     * @return void
     */
    public function page_assets( $hook ) {

        if ($hook != 'tools_page_archive_page_slug') {
            return;
        }

        wp_enqueue_style(
            'archive-page-css',
            plugins_url('../assets/css/archive-page.css', __FILE__)
        );

        wp_register_script_module(
            '@cp/papaparse',
            plugins_url('../assets/js/papaparse.js', __FILE__),
            [],
            '5.5.3'
        );

        wp_register_script_module(
            '@cp/archivePagePlugin',
            plugins_url('../assets/js/archive-page.js', __FILE__),
            ['@cp/papaparse', '@wordpress/interactivity'],
            '1.0.0',
        );
    }

    /**
     * Reg API archive page
     *
     * @return void
     */
    public function reg_api_archive_page() {
        register_rest_route('crypto/v1', '/update-posts-status/', array(
            'methods' => 'POST',
            'callback' => [ $this, 'update_posts_status' ],
            'permission_callback'   => function () {
                return current_user_can('manage_options');
            }
        ));
    }

    /**
     * Update posts status
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function update_posts_status(WP_REST_Request $request){
        global $wpdb;
        $posts = $request->get_param('data_posts');
        $id_posts = [];

        foreach ( $posts as $post ) {
            $id_posts[] = $post['Post ID'];
            if ( $post['Redirect URL'] !== null ) {
                $this->save_rankmath_redirect( $post['URL'], $post['Redirect URL']);
            }
        }

        $post_ids = array_map('intval', $id_posts);
        $new_status = sanitize_key($request->get_param('new_status'));
        $placeholders = implode(',', array_fill(0, count($post_ids), '%d'));

        $updated = $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->posts}
        SET post_status = %s
        WHERE ID IN ($placeholders)",
            array_merge([$new_status], $post_ids)
        ));

        if ($updated > 0) {

            foreach ( $post_ids as $post_id) {

               $this->clear_post_cache( $post_id );

            }

        }

        return new WP_REST_Response([
            'success' => $updated !== false,
            'updated' => $updated !== false ? $updated : 0,
            'posts' => $posts,
            'new_status' => $new_status,
        ], 200);

    }

    /**
     * Clear post cache
     *
     * @param $post_id
     * @return void
     */
    public function clear_post_cache($post_id ) {

        wp_cache_delete( $post_id, 'posts' );

        if ( function_exists( 'spinupwp_purge_post' ) ) {
            spinupwp_purge_post( $post_id );
        }

        do_action( 'archive_change_status', $post_id );

    }

    /**
     * Save Rank Mgath redirect
     *
     * @param $source_url
     * @param $target_url
     * @param $status_code
     * @return bool|WP_Error
     */
    public function save_rankmath_redirect($source_url, $target_url, $status_code = 301 ) {
        if ( ! function_exists( 'rank_math' ) || ! class_exists( '\RankMath\Redirections\Redirection' ) ) {
            return new WP_Error( 'rankmath_missing', 'Rank Math no active or class was not defined' );
        }

        $redirect_data = [
            'sources' => [
                [
                    'ignore' => '',
                    'pattern' => wp_make_link_relative( $source_url ),
                    'comparison' => 'exact',

                ],
            ],
            'url_to'      => $target_url,
            'header_code' => $status_code,
            'status'      => 'active',
        ];

        $redirect = new \RankMath\Redirections\Redirection( $redirect_data );

        $result = $redirect->save();

        if ( $result ) {
            return true;
        }

        return new WP_Error( 'redirect_failed', 'Can not to create redirect' );
    }

}

new Archive_Plugin_Page;