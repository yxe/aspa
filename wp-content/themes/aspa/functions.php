<?php
/**
 * Enqueue theme assets (CSS and JS).
 */
function aspa_enqueue_assets() {
  // Enqueue fonts 
  wp_enqueue_style(
    'aspa-fonts',
    get_template_directory_uri() . '/assets/css/fonts.css',
    [],
    null
  );

  // Enqueue main stylesheet
  wp_enqueue_style(
    'aspa-main-style',
    get_template_directory_uri() . '/assets/css/main.css',
    ['aspa-fonts'],
    '1.0'
  );

  // Enqueue main js
  wp_enqueue_script(
    'aspa-main-js',
    get_template_directory_uri() . '/assets/js/main.js',
    [],
    '1.0',
    true // Load in the footer
  );

  $chevron_icon_svg = aspa_get_lucide_icon('chevron-down');
  wp_localize_script(
    'aspa-main-js',
    'aspa_globals',
    array(
      'chevronIcon' => $chevron_icon_svg,
    )
  );
}
add_action( 'wp_enqueue_scripts', 'aspa_enqueue_assets' );

/**
 * Preload the main font files to improve performance.
 */
function aspa_preload_fonts() {
  echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/fonts/roboto-v48-latin-regular.woff2" as="font" type="font/woff2" crossorigin>';
  echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/fonts/roboto-condensed-v30-latin-700.woff2" as="font" type="font/woff2" crossorigin>';
}
add_action('wp_head', 'aspa_preload_fonts');

/**
 * Register theme-supported functions (menu, post thumbs, etc.).
 */
function aspa_theme_setup() {
  // Register menu
  register_nav_menus(
    array(
      'primary' => __( 'Primary Menu', 'aspa' ),
    )
  );

  // Add support for featured images
  add_theme_support( 'post-thumbnails' );

  // Define a custom color palette for the editor
  add_theme_support( 'editor-color-palette', array(
    array(
      'name'  => __( 'ASPA green', 'aspa' ),
      'slug'  => 'aspa-green',
      'color' => '#20513F',
    ),
    array(
      'name'  => __( 'ASPA yellow', 'aspa' ),
      'slug'  => 'aspa-yellow',
      'color' => '#FCE275',
    ),
    array(
      'name'  => __( 'Light grey', 'aspa' ),
      'slug'  => 'light-grey',
      'color' => '#EEEEEE',
    ),
  array(
      'name'  => __( 'Medium grey', 'aspa' ),
      'slug'  => 'medium-grey',
      'color' => '#666666',
    ),
    array(
      'name'  => __( 'Dark grey', 'aspa' ),
      'slug'  => 'dark-grey',
      'color' => '#333333',
    ),
    array(
      'name'  => __( 'Yellow', 'aspa' ),
      'slug'  => 'yellow',
      'color' => '#CEAB19',
    ),
    array(
      'name'  => __( 'Blue', 'aspa' ),
      'slug'  => 'blue',
      'color' => '#57889B',
    ),
    array(
      'name'  => __( 'Navy', 'aspa' ),
      'slug'  => 'navy',
      'color' => '#292945',
    ),
    array(
      'name'  => __( 'Red', 'aspa' ),
      'slug'  => 'red',
      'color' => '#B43A4F',
    )
  ) );
}
add_action( 'after_setup_theme', 'aspa_theme_setup' );

/**
 * Add the search form to the end of the primary navigation menu.
 *
 * @param string $items The HTML list of menu items.
 * @param object $args An object containing wp_nav_menu() arguments.
 * @return string The modified list of menu items.
 */
function aspa_add_search_to_menu( $items, $args ) {
  // Only add the search form to the 'primary' menu location.
  if ( 'primary' === $args->theme_location ) {
    $search_form = get_search_form( array( 'echo' => false ) );
    $items .= '<li class="menu-item menu-item-search">' . $search_form . '</li>';
  }

  return $items;
}
add_filter( 'wp_nav_menu_items', 'aspa_add_search_to_menu', 10, 2 );

/**
 * Allow SVG uploads for admins only.
 */
function aspa_allow_svg_uploads( $mimes ) {
  if ( ! current_user_can( 'administrator' ) ) {
    return $mimes;
  }

  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter( 'upload_mimes', 'aspa_allow_svg_uploads' );

/**
 * Sanitize SVG files upon upload to remove malicious code.
 */
function aspa_sanitize_svg( $file ) {
  if ( $file['type'] === 'image/svg+xml' ) {
    $svg_content = file_get_contents( $file['tmp_name'] );
    $sanitized_content = aspa_sanitize_svg_string( $svg_content );
    file_put_contents( $file['tmp_name'], $sanitized_content );
  }

  return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'aspa_sanitize_svg' );

/**
 * Replace the default site icon control with a simpler media uploader for SVG support.
 */
function aspa_replace_site_icon_control( $wp_customize ) {
  // Remove the original control
  $wp_customize->remove_control('site_icon');

  // Add a simpler media control in its place
  $wp_customize->add_control(
    new WP_Customize_Media_Control(
      $wp_customize,
      'site_icon',
      array(
        'label'    => __( 'Site Icon', 'aspa' ),
        'section'  => 'title_tagline',
        'mime_type' => 'image',
        'priority' => 60,
      )
    )
  );
}
add_action( 'customize_register', 'aspa_replace_site_icon_control', 99 );

/**
 * Output the SVG site icon link in the site header.
 */
function aspa_output_svg_site_icon() {
  $site_icon_id = get_option('site_icon');

  if ( $site_icon_id ) {
    $site_icon_url = wp_get_attachment_url($site_icon_id);
    
    if ( pathinfo( $site_icon_url, PATHINFO_EXTENSION ) === 'svg' ) {
      echo '<link rel="icon" href="' . esc_url( $site_icon_url ) . '" type="image/svg+xml">';
    }
  }
}
add_action( 'wp_head', 'aspa_output_svg_site_icon' );
add_action( 'admin_head', 'aspa_output_svg_site_icon' );

/**
 * Hide the site icon section on the Settings > General page with CSS.
 */
function aspa_hide_site_icon_general_settings_css() {
  echo '<style>
    .site-icon-section {
      display: none !important;
    }
  </style>';
}
add_action('admin_head-options-general.php', 'aspa_hide_site_icon_general_settings_css');

/**
 * Get the logo URL, using the site icon if available, with fallback to a default.
 *
 * @return string The URL of the logo.
 */
function aspa_get_logo() {
  $site_icon_id = get_option('site_icon');
  
  if ( $site_icon_id ) {
    return wp_get_attachment_url($site_icon_id);
  }
  
  return get_template_directory_uri() . '/assets/images/aspa-logo.svg';
}

/**
 * Register a custom post type for events.
 */
function aspa_register_event_cpt() {
  $labels = array(
    'name'                  => _x( 'Events', 'Post type general name', 'aspa' ),
    'singular_name'         => _x( 'Event', 'Post type singular name', 'aspa' ),
    'menu_name'             => _x( 'Events', 'Admin Menu text', 'aspa' ),
    'add_new'               => __( 'Add new', 'aspa' ),
    'add_new_item'          => __( 'Add new event', 'aspa' ),
    'edit_item'             => __( 'Edit event', 'aspa' ),
    'new_item'              => __( 'New event', 'aspa' ),
    'view_item'             => __( 'View event', 'aspa' ),
    'search_items'          => __( 'Search events', 'aspa' ),
    'not_found'             => __( 'No events found', 'aspa' ),
    'not_found_in_trash'    => __( 'No events found in trash', 'aspa' ),
  );

  $args = array(
    'labels'              => $labels,
    'public'              => true,
    'has_archive'         => true,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'show_in_rest'        => true,
    'menu_position'       => 6,
    'supports'            => array( 'title', 'editor', 'thumbnail' ),
    'rewrite'             => array( 'slug' => 'events' ),
    'menu_icon'           => 'dashicons-calendar-alt',
    'taxonomies'          => array( 'category' ),
  );

  register_post_type( 'event', $args );
}
add_action( 'init', 'aspa_register_event_cpt' );

/**
 * Add a meta box for event details.
 */
function aspa_add_event_meta_box() {
  add_meta_box(
    'aspa_event_details_meta_box',
    'Event details',
    'aspa_render_event_meta_box_html',
    'event',
    'normal',
    'high'
  );
}
add_action( 'add_meta_boxes', 'aspa_add_event_meta_box' );

/**
 * Render the HTML for the event details meta box.
 */
function aspa_render_event_meta_box_html( $post ) {
  wp_nonce_field( 'aspa_update_event_details', 'aspa_event_details_nonce' );

  $date = get_post_meta( $post->ID, '_event_date', true );
  $time = get_post_meta( $post->ID, '_event_time', true );
  $end_time = get_post_meta( $post->ID, '_event_end_time', true );
  $place = get_post_meta( $post->ID, '_event_place', true );
?>

  <p>
    <label for="event-date"><strong>Date (YYYY-MM-DD):</strong></label><br>
    <input type="date" id="event-date" name="event_date" value="<?php echo esc_attr( $date ); ?>" required>
  </p>
  <div style="display: flex; gap: 1em;">
    <div>
      <label for="event-time"><strong>Time (24 hour):</strong></label><br>
      <input type="time" id="event-time" name="event_time" value="<?php echo esc_attr( $time ); ?>" required>
    </div>
    <div>
      <label for="event-end-time"><strong>End time (optional):</strong></label><br>
      <input type="time" id="event-end-time" name="event_end_time" value="<?php echo esc_attr( $end_time ); ?>">
    </div>
  </div>
  <p>
    <label for="event-place"><strong>Place:</strong></label><br>
    <input type="text" id="event-place" name="event_place" value="<?php echo esc_attr( $place ); ?>" required>
  </p>

<?php
}

/**
 * Save the event details from the meta box.
 */
function aspa_save_event_meta( $post_id ) {
  if ( ! isset( $_POST['aspa_event_details_nonce'] ) || ! wp_verify_nonce( $_POST['aspa_event_details_nonce'], 'aspa_update_event_details' ) ) {
    return;
  }
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }
  if ( ! current_user_can( 'edit_post', $post_id ) ) {
    return;
  }
  if ( isset( $_POST['event_date'] ) ) {
    update_post_meta( $post_id, '_event_date', sanitize_text_field( $_POST['event_date'] ) );
  }
  if ( isset( $_POST['event_time'] ) ) {
    update_post_meta( $post_id, '_event_time', sanitize_text_field( $_POST['event_time'] ) );
  }
  if ( isset( $_POST['event_end_time'] ) ) {
    update_post_meta( $post_id, '_event_end_time', sanitize_text_field( $_POST['event_end_time'] ) );
  }
  if ( isset( $_POST['event_place'] ) ) {
    update_post_meta( $post_id, '_event_place', sanitize_text_field( $_POST['event_place'] ) );
  }
}
add_action( 'save_post', 'aspa_save_event_meta' );

/**
 * Sanitize a raw SVG string to remove malicious code, comments, and newlines.
 *
 * @param string $svg_content The raw SVG content.
 * @return string Sanitized, single-line SVG content.
 */
function aspa_sanitize_svg_string( $svg_content ) {
  $svg_content = preg_replace( '/<!--(.*?)-->/s', '', $svg_content );
  $svg_content = preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $svg_content );
  $svg_content = preg_replace( '/on\w+\s*=\s*".*?"/i', '', $svg_content );
  $svg_content = preg_replace( "/on\w+\s*=\s*'.*?'/i", '', $svg_content );
  $svg_content = preg_replace( '/\s+/', ' ', $svg_content );

  return trim( $svg_content );
}

/**
 * Get a sanitized, inline SVG of a Lucide icon from the local theme folder.
 *
 * @param string $name The name of the icon (e.g., 'menu', 'calendar-days').
 * @param array  $args Optional. Array of arguments ('class', 'size').
 * @return string The SVG markup wrapped in a span.
 */
function aspa_get_lucide_icon( $name, $args = [] ) {
  $defaults = [
    'class' => '',
    'size'  => null,
  ];

  $args = wp_parse_args( $args, $defaults );
  $icon_path = get_template_directory() . '/assets/icons/' . sanitize_key( $name ) . '.svg';

  if ( ! file_exists( $icon_path ) ) {
    return '';
  }

  $svg_content = file_get_contents( $icon_path );
  
  if ( empty( $svg_content ) ) {
    return '';
  }

  $sanitized_svg = aspa_sanitize_svg_string( $svg_content );
  preg_match( '/<svg.*<\/svg>/s', $sanitized_svg, $matches );

  if ( ! isset( $matches[0] ) ) {
    return '';
  }

  $svg = $matches[0];
  $wrapper_classes = ['lucide-icon-wrapper'];

  if ( ! empty( $args['class'] ) ) {
    $wrapper_classes[] = esc_attr( $args['class'] );
  }

  $wrapper_style = '';

  if ( $args['size'] ) {
    $wrapper_style = 'style="width: ' . esc_attr( $args['size'] ) . '; height: ' . esc_attr( $args['size'] ) . ';"';
  }

  return sprintf(
    '<span class="%s" %s>%s</span>',
    implode( ' ', $wrapper_classes ),
    $wrapper_style,
    $svg
  );
}

/**
 * Shortcode handler for displaying a Lucide icon.
 *
 * Usage: [icon calendar-days]
 *
 * @param array $atts Shortcode attributes.
 * @return string The icon HTML.
 */
function aspa_icon_shortcode_handler( $atts ) {
  // Check if the first unnamed attribute exists (e.g., [icon my-icon-name])
  if ( ! empty( $atts[0] ) ) {
    $icon_name = sanitize_key( $atts[0] );
    return aspa_get_lucide_icon( $icon_name );
  }

  // Fallback for the named attribute version [icon name="my-icon-name"]
  if ( ! empty( $atts['name'] ) ) {
    $icon_name = sanitize_key( $atts['name'] );
    return aspa_get_lucide_icon( $icon_name );
  }

  return '';
}
add_shortcode('icon', 'aspa_icon_shortcode_handler');

/**
 * Generate a consistent, rich text excerpt with an inline "Continue reading" link.
 *
 * @param int $word_limit The maximum number of words for a generated excerpt.
 * @return string The formatted excerpt.
 */
function aspa_get_richtext_excerpt( $word_limit ) {
  global $post;

  if ( ! $post ) {
    return '';
  }

  $continue_reading_text = ' <a href="' . esc_url( get_permalink( $post->ID ) ) . '#more-' . $post->ID . '" class="continue-reading-link">Continue reading</a>';

  // Prioritize manual excerpts
  if ( has_excerpt( $post->ID ) ) {
    $manual_excerpt_html = wpautop( get_the_excerpt( $post->ID ) );
    return preg_replace( '/<\/p>\s*$/', $continue_reading_text . '</p>', $manual_excerpt_html, 1 );
  }

  $content_to_parse = $post->post_content;
  $is_more_block_present = false;

  // Check for a "more" block and split content if it exists
  if ( preg_match( '/<!--more(.*?)?-->/', $content_to_parse, $matches ) ) {
    list( $content_to_parse, $extended_content ) = explode( $matches[0], $content_to_parse, 2 );
    $is_more_block_present = true;
  }

  // Parse the content source to extract paragraphs, including from media-text blocks
  $blocks = parse_blocks( $content_to_parse );
  $source_content = '';

  if ( ! empty( $blocks ) ) {
    $find_paragraphs = function( $inner_blocks ) use ( &$find_paragraphs, &$source_content ) {
      foreach ( $inner_blocks as $block ) {
        if ( 'core/paragraph' === $block['blockName'] && ! empty( trim( $block['innerHTML'] ) ) ) {
          $paragraph_html = render_block( $block );
          $paragraph_html = preg_replace( '/<img[^>]+>/i', '', $paragraph_html );
          $source_content .= $paragraph_html;
        }

        if ( 'core/media-text' === $block['blockName'] && ! empty( $block['innerBlocks'] ) ) {
          $find_paragraphs( $block['innerBlocks'] );
        }
      }
    };

    $find_paragraphs( $blocks );
  }

  // If we have no paragraphs, create a plain-text fallback
  if ( empty( trim( $source_content ) ) ) {
    $fallback_text = wp_trim_words( strip_tags( strip_shortcodes( $post->post_content ) ), $word_limit, '' );

    if ( ! empty( $fallback_text ) ) {
      return '<p>' . esc_html( $fallback_text ) . '&hellip;' . $continue_reading_text . '</p>';
    }
    else {
      return '';
    }
  }

  // If a "more" block was used, use the excerpt as is
  if ( $is_more_block_present ) {
    return preg_replace( '/<\/p>\s*$/', $continue_reading_text . '</p>', $source_content, 1 );
  }

  // For auto-generated excerpts, check if truncation is needed
  $word_count_in_source = str_word_count( strip_tags( $source_content ) );

  if ( $word_count_in_source <= $word_limit ) {
    return preg_replace( '/<\/p>\s*$/', $continue_reading_text . '</p>', $source_content, 1 );
  }

  // If the content is long, trim it to the word limit
  $tokens = preg_split( '/(<[^>]+>|\s+)/', $source_content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
  $word_count = 0;
  $excerpt = '';

  foreach ( $tokens as $token ) {
    if ( $word_count >= $word_limit ) {
      break;
    }

    if ( strpos( $token, '<' ) === false && ! ctype_space( $token ) && ! empty( $token ) ) {
      $word_count++;
    }

    $excerpt .= $token;
  }

  return force_balance_tags( $excerpt . '&hellip;' . $continue_reading_text );
}

/**
 * Modify the main query for specific archives.
 *
 * @param WP_Query $query The main WP_Query object.
 */
function aspa_modify_main_query( $query ) {
  if ( is_admin() || ! $query->is_main_query() ) {
    return;
  }

  // Set 10 posts per page on the blog index (news page).
  if ( $query->is_home() ) {
    $query->set( 'posts_per_page', 10 );
  }
}
add_action( 'pre_get_posts', 'aspa_modify_main_query' );

