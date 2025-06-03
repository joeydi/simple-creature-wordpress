<?php

new DL_Projects();

class DL_Projects {
    function __construct() {
        add_action( 'init',         [$this, 'register_post_type_project'] );
        add_action( 'init',         [$this, 'register_taxonomy_project_category'] );
    }

    // Register Custom Post Type
    function register_post_type_project() {
        register_post_type( 'project', [
            'labels' => [
                'name'              => 'Projects',
                'singular_name'     => 'Project',
                'add_new_item'      => 'Add New Project',
                'edit_item'         => 'Edit Project',
            ],
            'supports' => [
                'title',
                'editor',
                'thumbnail',
            ],
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-images-alt2',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'rewrite'               => ['slug' => 'projects'],
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
            'show_in_rest'          => false,
        ] );
    }

    function register_taxonomy_project_category() {
        register_taxonomy( 'project_category', ['project'], [
            'labels' => [
                'name'              => 'Categories',
                'singular_name'     => 'Category',
            ],
            'hierarchical'          => true,
            'public'                => true,
            'rewrite'               => ['slug' => 'projects'],
            'show_ui'               => true,
            'show_admin_column'     => true,
            'show_in_nav_menus'     => true,
        ] );
    }

    static function get( $args = null ) {
        $args = wp_parse_args( $args, [
            'post_type' => 'project',
            'posts_per_page' => -1,
        ] );

        return new WP_Query( $args );
    }

    static function get_next() {
        global $post;

        if ( empty( $post ) || 'project' !== get_post_type( $post ) ) {
            return null;
        }

        $projects = self::get()->posts;
        $post_ids = wp_list_pluck( $projects, 'ID' );

        $current = array_search( $post->ID, $post_ids );
        $next = !empty( $post_ids[$current + 1] ) ? $post_ids[$current + 1] : $post_ids[0];

        return $next;
    }
}
