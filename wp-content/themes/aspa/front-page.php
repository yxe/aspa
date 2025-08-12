<?php get_header(); ?>

<main id="front-page">

  <?php get_template_part( 'template-parts/page-header' ); ?>

  <div class="page-content">
    <?php
    if ( have_posts() ) :
      while ( have_posts() ) :
        the_post();
        the_content();
      endwhile;
    endif;
    ?>
  </div>

  <?php
  $more_news_query = new WP_Query( array(
    'post_type'      => 'post',
    'posts_per_page' => 5,
    'offset'         => 1, // Skip most recent post
    'post_status'    => 'publish',
  ) );

  if ( $more_news_query->have_posts() ) :
  ?>
    <section class="more-news-section">
      <div class="page-content">
        <h2 class="subheading h3 text-uppercase">More News</h2>
        <div class="more-news-grid">

          <?php while ( $more_news_query->have_posts() ) : $more_news_query->the_post(); ?>
            
            <article class="news-item">
              <h3 class="title h4"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
              <div class="metadata"><?php echo get_the_date(); ?></div>
              <div class="excerpt">

                <?php echo aspa_get_richtext_excerpt( 30 ); ?>

              </div>
            </article>

          <?php endwhile; ?>

        </div>
        <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="button">Read all news</a>
      </div>
    </section>
  <?php
  endif;
  wp_reset_postdata();
  ?>

</main>

<?php get_footer(); ?>

