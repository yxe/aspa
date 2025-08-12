<?php get_header(); ?>

<main>
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>">
                <h2><?php the_title(); ?></h2>
                <div>
                    <?php the_content(); ?>
                </div>
            </article>

        <?php endwhile; ?>
    <?php else : ?>
        <p>Sorry, no posts matched your criteria.</p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
