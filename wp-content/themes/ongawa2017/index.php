<section class="blog-wrapper container">
  <section class="blog-list">
    <?php while (have_posts()) : the_post(); ?>
      <?php get_template_part('templates/content', get_post_type() != 'post' ? get_post_type() : get_post_format()); ?>
    <?php endwhile; ?>
    <?php the_posts_navigation(); ?>
  </section>
  <aside class="sidebar">
    <?php get_template_part('templates/sidebar'); ?>
  </aside>
</section>
