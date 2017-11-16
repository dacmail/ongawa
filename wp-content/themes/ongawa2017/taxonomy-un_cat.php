<section class="container">
  <h1 class="section-title main"><?php esc_html_e('Publicaciones', 'ungrynerd'); ?></h1>
  <nav class="pubs__filter">
    <ul>
      <li><a href="<?php echo get_post_type_archive_link('un_doc') ?>"><?php esc_html_e('Todas', 'ungrynerd'); ?></a></li>
      <?php wp_list_categories(array(
                              'taxonomy' => 'un_cat',
                              'title_li' => '')); ?>
    </ul>
  </nav>
  <section class="pubs">
    <?php if (!have_posts()) : ?>
      <div class="alert alert-warning">
        <?php _e('Sorry, no results were found.', 'sage'); ?>
      </div>
      <?php get_search_form(); ?>
    <?php endif; ?>

    <?php while (have_posts()) : the_post(); ?>
      <?php get_template_part('templates/content-pubs'); ?>
    <?php endwhile; ?>

    <?php the_posts_navigation(); ?>
  </section>
</section>
