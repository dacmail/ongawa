<?php
/**
 * Template Name: Página Simple
 */
?>

<section class="page-wrapper page-wrapper--simple">
  <?php while (have_posts()) : the_post(); ?>
    <article <?php post_class() ?> id="page-<?php the_ID(); ?>">
      <header class="container">
        <h1 class="section-title"><?php the_title(); ?></h1>
      </header>
      <div class="page__content">
        <div class="page__content__block">
          <?php the_content(); ?>
        </div>
      </div>
    </article>
  <?php endwhile; ?>
</section>
