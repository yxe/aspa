<?php get_header(); ?>

<main>

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

</main>

<?php get_footer(); ?>
