<?php if ( is_front_page() ) :

  $news_query = new WP_Query( array(
    'posts_per_page' => 1,
    'post_status'    => 'publish',
  ) );

  $upcoming_events_query = new WP_Query( array(
    'post_type'      => 'event',
    'posts_per_page' => 2,
    'meta_key'       => '_event_date',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_query'     => array(
      'relation' => 'AND',
      array(
        'key'     => '_event_date',
        'value'   => date('Y-m-d'),
        'compare' => '>=',
        'type'    => 'DATE',
      ),
      array(
        'key'     => '_event_date',
        'value'   => '',
        'compare' => '!=',
      ),
    ),
  ));

  $final_events = [];
  $found_ids = [];

  if ( $upcoming_events_query->have_posts() ) {
    while ( $upcoming_events_query->have_posts() ) {
      $upcoming_events_query->the_post();
      $final_events[] = $post;
      $found_ids[] = get_the_ID();
    }
  }

  wp_reset_postdata();

  // If we found fewer than 2 events, run a fallback query
  $events_found_count = count( $final_events );

  if ( $events_found_count < 2 ) {
    $needed = 2 - $events_found_count;

    // Get the most recently created events, excluding any we already found
    $fallback_events_query = new WP_Query( array(
      'post_type'      => 'event',
      'posts_per_page' => $needed,
      'orderby'        => 'date',
      'order'          => 'DESC',
      'post__not_in'   => $found_ids,
    ));

    // Add these fallback events to our final list
    if ( $fallback_events_query->have_posts() ) {
      while ( $fallback_events_query->have_posts() ) {
        $fallback_events_query->the_post();
        $final_events[] = $post;
      }
    }

    wp_reset_postdata();
  }

  $has_events = ! empty( $final_events );
  $container_class = $has_events ? 'front-header-grid has-events' : 'front-header-grid';
  $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'large' ) : '';
  $background_style = ! empty( $image_url ) ? 'style="background-image: url(\'' . esc_url( $image_url ) . '\');"' : '';

?>

  <header class="page-header with-background-image" <?php echo $background_style; ?>>
    <div class="header-content <?php echo $container_class; ?>">
      
      <div class="header-news-column">
        
        <?php if ( $news_query->have_posts() ) : while ( $news_query->have_posts() ) : $news_query->the_post(); ?>
          
          <section class="header-news">
            <h1 class="subheading h3 text-uppercase">Recent news</h1>
            <h2 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <div class="metadata"><?php echo get_the_date(); ?></div>
            <div class="excerpt">
              <?php echo aspa_get_richtext_excerpt( 100 ); ?>
            </div>
            <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="button">Read more news</a>
          </section>

        <?php endwhile; wp_reset_postdata(); endif; ?>

      </div>

      <?php if ( $has_events ) : ?>

        <div class="header-events-column">

          <section class="header-events">
            <h1 class="subheading h3 text-uppercase">Upcoming events</h1>
              <div class="event-list">

              <?php foreach ( $final_events as $post ) : setup_postdata( $post ); ?>

                <div class="event-item">
                  <h2 class="h3 title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                  <div class="excerpt"><?php echo aspa_get_richtext_excerpt( 20 ); ?></div>
                  <div class="metadata">
                    <?php
                    $event_date = get_post_meta( get_the_ID(), '_event_date', true );
                    $start_time = get_post_meta( get_the_ID(), '_event_time', true );
                    $event_place = get_post_meta( get_the_ID(), '_event_place', true );
                
                    // If all key details are missing, show a link to the event page
                    if ( empty( $event_date ) && empty( $start_time ) && empty( $event_place ) ) {
                      ?>
                      <p><?php echo aspa_get_lucide_icon( 'calendar-clock' ); ?> <a href="<?php the_permalink(); ?>">View event details</a></p>
                      <?php
                    }
                    // Display date if it exists
                    else {

                      if ( ! empty( $event_date ) ) {
                        ?>
                        <p class="event-date"><?php echo aspa_get_lucide_icon( 'calendar-days' ); ?> <?php echo esc_html( date( 'F j, Y', strtotime( $event_date ) ) ); ?></p>
                        <?php
                      }
                
                      // Display time/range if start time exists
                      if ( ! empty( $start_time ) ) {
                        $end_time = get_post_meta( get_the_ID(), '_event_end_time', true );
                        $time_string = date( 'g:i a', strtotime( $start_time ) );

                        if ( ! empty( $end_time ) ) {
                          $time_string .= ' - ' . date( 'g:i a', strtotime( $end_time ) );
                        }

                        ?>
                        <p class="event-time"><?php echo aspa_get_lucide_icon( 'clock' ); ?> <?php echo esc_html( $time_string ); ?></p>
                        <?php
                      }
                
                      // Display place if it exists
                      if ( ! empty( $event_place ) ) {
                        ?>
                        <p class="event-place"><?php echo aspa_get_lucide_icon( 'map-pin' ); ?> <?php echo esc_html( $event_place ); ?></p>
                        <?php
                      }
                    }
                    ?>
                  </div>
                </div>

              <?php endforeach; wp_reset_postdata(); ?>

              </div>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'event' ) ); ?>" class="button">See all events</a>
          </section>

        </div>

      <?php endif; ?>

    </div>
  </header>

<?php
else :
  $header_title = '';
  $image_url = '';
  $header_classes = ['page-header'];
  
  if ( is_home() ) {
    $header_title = get_the_title( get_option('page_for_posts', true) );
    
    if ( has_post_thumbnail( get_option('page_for_posts', true) ) ) {
      $image_url = get_the_post_thumbnail_url( get_option('page_for_posts', true), 'large' );
    }
  }
  elseif ( is_singular() ) {
    $header_title = get_the_title();

    if ( has_post_thumbnail() ) {
      $image_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
    }
  }
  elseif ( is_archive() ) {
    $header_title = get_the_archive_title();
  }
  elseif ( is_search() ) {
    $header_title = 'Search results for: ' . get_search_query();
  }

  $background_style = '';

  if ( ! empty( $image_url ) ) {
    $header_classes[] = 'with-background-image';
    $background_style = 'style="--bg-image: url(\'' . esc_url( $image_url ) . '\');"';
  }
  else {
    $header_classes[] = 'no-background-image';
  }

?>

  <header class="<?php echo esc_attr( implode( ' ', $header_classes ) ); ?>" <?php echo $background_style; ?>>
    <div class="header-content">
      <h1 class="page-title"><?php echo esc_html( $header_title ); ?></h1>
    </div>
  </header>

<?php endif; ?>
