<?php

require_once('inc/projects.php');

new SC();

class SC
{
  private $version;

  public static $sm = '(min-width: 576px)';
  public static $md = '(min-width: 768px)';
  public static $lg = '(min-width: 992px)';
  public static $xl = '(min-width: 1200px)';

  function __construct()
  {
    $theme = wp_get_theme();
    $this->version = $theme->Version;

    add_theme_support('menus');
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');

    // 2x3
    add_image_size('portrait',    320, 480, true);
    add_image_size('portrait-md', 420, 640, true);
    add_image_size('portrait-lg', 640, 960, true);

    // 1x1
    add_image_size('square',       480, 480, true);
    add_image_size('square-md',    640, 640, true);
    add_image_size('square-lg',    960, 960, true);

    // 6x5
    add_image_size('block',        480, 400, true);
    add_image_size('block-md',     640, 530, true);
    add_image_size('block-lg',     960, 800, true);

    // 3x2
    add_image_size('landscape',    480, 320, true);
    add_image_size('landscape-md', 960, 640, true);
    add_image_size('landscape-lg', 1440, 960, true);

    // 16x9
    add_image_size('video',        480, 270, true);
    add_image_size('video-md',     960, 540, true);
    add_image_size('video-lg',     1440, 810, true);

    // 11x5
    add_image_size('hero',         480, 220, true);
    add_image_size('hero-md',      1280, 580, true);
    add_image_size('hero-lg',      1920, 870, true);

    add_action('init',                         [$this, 'action_register_blocks']);
    add_action('init',                         [$this, 'action_acf_add_options_page']);
    add_action('wp_enqueue_scripts',           [$this, 'action_enqueue_scripts']);
    add_action('wp_enqueue_scripts',           [$this, 'action_enqueue_styles']);
    add_action('admin_enqueue_scripts',        [$this, 'action_admin_enqueue_styles']);
    add_action('login_enqueue_scripts',        [$this, 'action_login_enqueue_styles']);
    add_action('enqueue_block_editor_assets',  [$this, 'action_enqueue_block_editor_assets']);
    add_action('admin_init',                   [$this, 'add_editor_styles']);
    add_action('wp_ajax_download',             [$this, 'action_download']);
    add_action('pre_get_posts',                [$this, 'action_pre_get_posts']);

    remove_action('wp_head',                   'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts',       'print_emoji_detection_script');
    remove_action('wp_print_styles',           'print_emoji_styles');
    remove_action('admin_print_styles',        'print_emoji_styles');
    remove_action('wp_head',                   'wp_oembed_add_host_js');
    remove_action('wp_head',                   'wp_oembed_add_discovery_links', 10);
    remove_action('wp_head',                   'rest_output_link_wp_head', 10);
    remove_action('template_redirect',         'rest_output_link_header', 11, 0);

    add_filter('style_loader_tag',             [$this, 'filter_style_loader_tag']);
    add_filter('script_loader_tag',            [$this, 'filter_script_loader_tag'], 10, 3);
    add_filter('login_headertext',             [$this, 'filter_login_headertext']);
    add_filter('login_headerurl',              [$this, 'filter_login_headerurl']);
    add_filter('the_excerpt',                  [$this, 'filter_the_excerpt']);
    add_filter('the_permalink',                [$this, 'filter_the_permalink']);
    add_filter('get_the_categories',           [$this, 'filter_get_the_categories']);
    add_filter('get_the_archive_title',        [$this, 'filter_get_the_archive_title']);
    add_filter('body_class',                   [$this, 'filter_body_class']);
    add_filter('wp_nav_menu_objects',          [$this, 'filter_wp_nav_menu_objects'], 10, 2);
    add_filter('wp_get_nav_menu_items',        [$this, 'filter_wp_get_nav_menu_items']);
    add_filter('mce_buttons_2',                [$this, 'filter_mce_buttons_2']);
    add_filter('tiny_mce_before_init',         [$this, 'filter_tiny_mce_before_init']);
    add_filter(
      'wp_get_attachment_image_attributes',
      [$this, 'filter_image_attributes']
    );
  }

  function action_register_blocks()
  {
    register_block_type(__DIR__ . '/blocks/section');
    register_block_type(__DIR__ . '/blocks/featured-projects');
    register_block_type(__DIR__ . '/blocks/testimonial-slider');

    register_block_style(
      'core/paragraph',
      [
        'name'         => 'lead',
        'label'        => __('Lead', 'textdomain'),
      ]
    );

    register_block_style(
      'core/paragraph',
      [
        'name'         => 'eyebrow',
        'label'        => __('Eyebrow', 'textdomain'),
      ]
    );

    register_block_style(
      'core/list',
      [
        'name'         => 'icons',
        'label'        => __('Icons', 'textdomain'),
      ]
    );
  }

  function action_acf_add_options_page()
  {
    if (function_exists('acf_add_options_page')) {
      acf_add_options_page([
        'page_title'    => __('Theme Settings'),
        'menu_title'    => __('Theme Settings'),
        'menu_slug'     => 'theme-settings',
        'parent_slug'   => 'options-general.php',
        'capability'    => 'manage_options',
      ]);
    }
  }

  function action_enqueue_scripts()
  {
    if ('development' === wp_get_environment_type()) {
      $viteDevServer = 'http://localhost:5173/wp-content/themes/distefanolandscaping';
      wp_enqueue_script_module('vite-client', $viteDevServer . '/@vite/client', [], null);
      wp_enqueue_script_module('vite-script', $viteDevServer . '/src/js/main.js', ['vite-client'], null);
    } else {
      wp_enqueue_script_module('main', self::get_asset_url('src/js/main.js'), [], $this->version);
    }

    $data = [
      'ajaxurl' => admin_url('admin-ajax.php'),
      'template_directory_url' => get_stylesheet_directory_uri(),
      'ga_measurement_id' => get_field('ga_measurement_id', 'options'),
    ];

    wp_localize_script('main', 'SC', $data);
  }

  function action_enqueue_styles()
  {
    if ('development' === wp_get_environment_type()) {
      $viteDevServer = 'http://localhost:5173/wp-content/themes/distefanolandscaping';
      wp_enqueue_style('vite-styles', $viteDevServer . '/src/scss/main.scss', [], $this->version);
    } else {
      wp_enqueue_style('main', self::get_asset_url('src/scss/main.scss'), [], $this->version);
    }


    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('contact-form-7');
  }

  function action_admin_enqueue_styles()
  {
    wp_enqueue_style('admin', self::get_asset_url('src/scss/admin.scss'), [], $this->version);
  }

  function action_login_enqueue_styles()
  {
    wp_enqueue_style('customlogin', self::get_asset_url('src/scss/login.scss'), [], $this->version);
  }

  function action_enqueue_block_editor_assets()
  {
    wp_enqueue_style('customeditor', self::get_asset_url('src/scss/editor.scss'), [], $this->version);
    wp_enqueue_script('customeditor', self::get_asset_url('src/js/editor.js'), ['wp-blocks', 'wp-dom'], $this->version, true);
  }

  function add_editor_styles()
  {
    add_editor_style(self::get_asset_url('src/scss/mce.scss'));
  }

  function action_download()
  {
    $attachment_id = $_REQUEST['attachment_id'];

    if (empty($attachment_id)) {
      echo json_encode(array(
        'error' => 'You must specify an Attachment ID'
      ));
      exit;
    }

    $attachment = get_attached_file($attachment_id);

    if (empty($attachment)) {
      echo json_encode(array(
        'error' => 'No Attachment found for the specified ID'
      ));
      exit;
    }

    if (!file_exists($attachment)) {
      echo json_encode(array(
        'error' => 'Attachment file missing'
      ));
      exit;
    }

    // Process download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($attachment) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($attachment));
    readfile($attachment);
    exit;
  }

  function action_pre_get_posts($query)
  {
    if (!is_admin() && $query->is_main_query() && !$query->is_single()) {
      $page_for_posts = get_option('page_for_posts');
      $featured_post = get_field('featured_post', $page_for_posts);
      $query->set('post__not_in', [$featured_post->ID]);
    }

    // Only search blog posts
    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
      $query->set('post_type', 'post');
    }
  }

  function filter_style_loader_tag($tag)
  {
    if (!is_admin()) {
      // Remove type attribute
      $tag = str_replace("type='text/css' ", '', $tag);

      // Replace single quotes with double quotes
      $tag = str_replace("'", '"', $tag);
    }

    return $tag;
  }

  function filter_script_loader_tag($tag, $hanSCe, $src)
  {
    if (!is_admin()) {
      // Remove type attribute
      $tag = str_replace("type='text/javascript' ", '', $tag);

      // Replace single quotes with double quotes
      $tag = str_replace("'", '"', $tag);
    }

    if ("main" === $hanSCe) {
      $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
    }

    return $tag;
  }

  function filter_login_headertext()
  {
    return get_bloginfo('name');
  }

  function filter_login_headerurl()
  {
    return site_url('/');
  }

  function filter_the_excerpt($excerpt)
  {
    return sprintf('<p>%s</p>', SC::truncate_string(strip_tags($excerpt), 150, ' '));
  }

  function filter_the_permalink($url)
  {
    $permalink = get_field('permalink');

    return $permalink ? $permalink : $url;
  }

  function filter_get_the_categories($categories)
  {
    $categories = array_filter($categories, function ($category) {
      return $category->name !== 'Uncategorized';
    });

    return $categories;
  }

  function filter_get_the_archive_title($title)
  {
    if (is_category()) {
      $title = sprintf('Category: <span class="text-darker-grey">%s</span>', single_cat_title('', false));
    } elseif (is_tag()) {
      $title = sprintf('Tag: <span class="text-darker-grey">%s</span>', single_cat_title('', false));
    } elseif (is_author()) {
      $title = 'Author: <span class="vcard text-darker-grey">' . get_the_author() . '</span>';
    } elseif (is_search()) {
      $title = sprintf('Results for <span class="text-darker-grey">&ldquo;%s&rdquo;</span>', get_search_query());
    }

    return $title;
  }

  function filter_body_class($classes)
  {
    global $post;

    if (isset($post) && is_singular()) {
      $classes[] = $post->post_type . '-' . $post->post_name;
    }

    return $classes;
  }

  function filter_wp_nav_menu_objects($items, $args)
  {
    if ($args->menu === 'social') {
      foreach ($items as &$item) {
        $icon = get_field('icon', $item);
        if ($icon) {
          $image = wp_get_attachment_image($icon);
          $item->title = sprintf('%s<span class="visually-hidden">%s</span>', $image, $item->title);
        }
      }
    }

    return $items;
  }

  function filter_wp_get_nav_menu_items($items)
  {
    foreach ($items as $key => $item)
      $items[$key]->description = $items[$key]->description ?? '';

    return $items;
  }

  function filter_mce_buttons_2($buttons)
  {
    array_unshift($buttons, 'styleselect');
    return $buttons;
  }

  function filter_tiny_mce_before_init($init)
  {
    $style_formats = [
      [
        'title' => 'Lead',
        'block' => 'p',
        'classes' => 'lead',
        'wrapper' => false,
      ],
      [
        'title' => 'Small',
        'block' => 'p',
        'classes' => 'small',
        'wrapper' => false,
      ],
      [
        'title' => 'Eyebrow',
        'block' => 'p',
        'classes' => 'eyebrow',
        'wrapper' => false,
      ],
      [
        'title' => 'Pill List',
        'block' => 'ul',
        'classes' => 'pills',
        'wrapper' => false,
      ],
      [
        'title' => 'Features List',
        'block' => 'ul',
        'classes' => 'features',
        'wrapper' => false,
      ],
      [
        'title' => 'Green Button',
        'inline' => 'a',
        'block' => 'a',
        'classes' => 'btn btn-primary',
        'wrapper' => false,
      ],
      [
        'title' => 'Green Outline Button',
        'inline' => 'a',
        'block' => 'a',
        'classes' => 'btn btn-outline-primary',
        'wrapper' => false,
      ],
      [
        'title' => 'White Button',
        'inline' => 'a',
        'block' => 'a',
        'classes' => 'btn btn-light',
        'wrapper' => false,
      ],
    ];

    $init['style_formats'] = json_encode($style_formats);

    $custom_colours = [
      'FFC627',
      'Gold',
      '2A2A2A',
      'Black',
    ];

    $init['textcolor_map'] = json_encode($custom_colours);
    $init['textcolor_cols'] = 8;

    return $init;
  }

  /**
   * Change src and srcset to data-src and data-srcset, and add class 'lazyload'
   * @param $attributes
   * @return mixed
   */
  function filter_image_attributes($attributes)
  {
    if (is_admin()) {
      return $attributes;
    }

    // Init lazysizes attrs
    if (!isset($attributes['loading']) || $attributes['loading'] !== 'eager') {
      if (isset($attributes['src'])) {
        $attributes['data-src'] = $attributes['src'];
      }

      if (isset($attributes['srcset'])) {
        $attributes['data-srcset'] = $attributes['srcset'];
        $attributes['srcset'] = 'data:image/gif;base64,R0lGOSChAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
      }

      unset($attributes['sizes']);
      $attributes['data-sizes'] = 'auto';

      $attributes['class'] .= ' lazyload';
    }

    return $attributes;
  }

  static function get_asset_url($path)
  {
    $manifest_path = get_stylesheet_directory() . '/dist/.vite/manifest.json';

    if (!file_exists($manifest_path)) {
      wp_die('Run <code>npm run build</code> in your application root!');
    }

    $manifest = json_decode(file_get_contents($manifest_path), true);
    $url = get_stylesheet_directory_uri() . "/dist/{$manifest[$path]['file']}";

    return $url;
  }

  static function theme_url($path)
  {
    $path = (0 === strpos($path, '/')) ? $path : '/' . $path;
    echo get_stylesheet_directory_uri() . $path;
  }

  static function theme_path($path)
  {
    $path = (0 === strpos($path, '/')) ? $path : '/' . $path;
    return get_template_directory() . $path;
  }

  static function remote_get($url)
  {
    if ($cached = get_transient($url)) {
      return $cached;
    }

    $response = wp_remote_get($url);

    if (!is_wp_error($response)) {
      set_transient($url, $response['body'], 60 * 60 * 24);
      return $response['body'];
    }
  }

  static function truncate_string($string, $limit, $break = '.', $pad = '...')
  {
    // return with no change if string is shorter than $limit
    if (strlen($string) <= $limit) {
      return $string;
    }

    // is $break present between $limit and the end of the string?
    if (false !== ($breakpoint = strpos($string, $break, $limit))) {
      if ($breakpoint < strlen($string) - 1) {
        $string = substr($string, 0, $breakpoint) . $pad;
      }
    }

    return $string;
  }

  static function get_the_excerpt($post = null)
  {
    $deck = get_field('deck', $post);
    $deck = !empty($deck) ? $deck : get_the_excerpt($post);

    return $deck;
  }

  static function the_excerpt($post = null)
  {
    echo self::get_the_excerpt($post);
  }

  static function get_related_posts($category = null)
  {
    $args = [
      'post_type' => 'post',
      'posts_per_page' => 3,
      'post__not_in' => [get_the_ID()],
    ];

    if ($category) {
      $args['tax_query'] = [
        [
          'taxonomy' => 'category',
          'field'    => 'slug',
          'terms'    => $category->slug,
        ],
      ];
    }

    return new WP_Query($args);
  }

  static function is_video_url($url)
  {
    if (stripos($url, '//www.youtube.com') !== false) {
      return true;
    }

    if (stripos($url, '//youtu.be') !== false) {
      return true;
    }

    if (stripos($url, '//vimeo.com') !== false) {
      return true;
    }

    return false;
  }

  static function format_url($url)
  {
    $parts = parse_url($url);

    if (!empty($parts['host'])) {
      return str_replace('www.', '', $parts['host']);
    }

    return $url;
  }

  static function format_filesize($bytes, $decimals = 1)
  {
    $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
  }
}
