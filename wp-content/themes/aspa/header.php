<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<header class="main-header">

  <div class="aspa-branding">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
      <?php
      $logo_id = get_option( 'site_icon' );

      if ( $logo_id ) {
        $logo_path = get_attached_file( $logo_id );
      }
      else {
        $logo_path = get_template_directory() . '/assets/images/aspa-logo.svg';
      }

      // If SVG, print its contents directly into the HTML
      if ( $logo_path && pathinfo( $logo_path, PATHINFO_EXTENSION ) === 'svg' && file_exists( $logo_path ) ) {
        echo file_get_contents( esc_url( $logo_path ) );
      }
      // If it's not an SVG (e.g., a PNG), display it as a normal image tag
      elseif ( $logo_id ) {
        echo '<img src="' . esc_url( wp_get_attachment_url( $logo_id ) ) . '" alt="' . get_bloginfo( 'name' ) . ' logo" class="aspa-logo">';
      }
      // Fallback for the fallback: if no file is found, show the site title
      else {
        echo '<h1>' . get_bloginfo('name') . '</h1>';
      }
      ?>
    </a>
  </div>

  <button class="mobile-menu-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Menu', 'aspa' ); ?>">
    <?php 
      echo aspa_get_lucide_icon( 'menu', ['class' => 'icon-menu'] ); 
      echo aspa_get_lucide_icon( 'x', ['class' => 'icon-close'] ); 
    ?>
  </button>

  <nav class="main-navigation">
    <?php
    wp_nav_menu( array(
      'theme_location' => 'primary',
      'menu_id'        => 'primary-menu',
      'menu_class'     => 'primary-menu',
      'container'      => false,
      'depth'          => 2,
    ) );
    ?>
  </nav>

</header>
